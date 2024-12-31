<?php

namespace Dashifen\Router;

use Dashifen\Request\RequestInterface;
use Dashifen\Router\Route\RouteInterface;
use Dashifen\Repository\RepositoryException;
use Dashifen\CaseChangingTrait\CaseChangingTrait;
use Dashifen\Router\Route\Factory\RouteFactoryInterface;
use Dashifen\Router\Route\Factory\RouterFactoryException;
use Dashifen\Router\Route\Collection\RouteCollectionException;
use Dashifen\Router\Route\Collection\RouteCollectionInterface;

class Router implements RouterInterface
{
  use CaseChangingTrait;
  
  protected string $path;
  protected string $method;
  protected bool $dirty = false;
  protected ?RouteInterface $route;
  
  /**
   * Router constructor.
   *
   * @param RequestInterface         $request
   * @param RouteCollectionInterface $collection
   * @param RouteFactoryInterface    $factory
   * @param array                    $routes
   * @param bool                     $autoRouter
   *
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterException
   * @throws RouterFactoryException
   */
  public function __construct(
    RequestInterface $request,
    protected array $routes = [],
    protected RouteCollectionInterface $collection,
    protected RouteFactoryInterface $factory,
    protected bool $autoRouter = false,
  ) {
    $this->method = $request->getServerVar("REQUEST_METHOD");
    $this->path = $request->getServerVar("REQUEST_URI");
    $this->addRoutes($routes);
  }
  
  /**
   * Returns true if this is an "auto-router."  An auto-router determines the
   * route all on its own based on the current request; you don't have to
   * hand-enter routes for it.
   *
   * @return bool
   */
  public function isAutoRouter(): bool
  {
    return $this->autoRouter;
  }
  
  
  /**
   * Returns the route for the current request.  If this isn't an auto-router,
   * we use our collection to do so.  Otherwise, we construct the route based
   * on the request.
   *
   * @return RouteInterface
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterException
   */
  public function getRoute(): RouteInterface
  {
    return !$this->autoRouter
      ? $this->getCollectedRoute()
      : $this->getAutoRoute();
  }
  
  /**
   * Returns a route based on our collected routes.
   *
   * @return RouteInterface
   * @throws RouteCollectionException
   * @throws RouterException
   */
  protected function getCollectedRoute(): RouteInterface
  {
    // the guts of our router is actually here.  when constructed, we
    // use the $request object to get the URI and method for this request.
    // with those, we can see if our collection has the route for this
    // page and, if so, return it.  if it does not, we return null.
    
    if ($this->dirty) {
      $this->route = null;
      
      // we search through the collection of routes looking for one that
      // response to this method and path.  if we find it, we set the route
      // property to it.
      
      if ($this->collection->hasRoute($this->method, $this->path)) {
        $this->route = $this->collection->getRoute($this->method, $this->path);
      }
      
      // regardless of whether or not we found a route, because we've done a
      // search, we can set the dirty flag to false.  only if we add more
      // routes to our collection will we have to search again.
      
      $this->dirty = false;
    }
    
    if (is_null($this->route)) {
      throw new RouterException(
        sprintf('Unexpected route: %s:%s.', $this->method, $this->path),
        RouterException::UNEXPECTED_ROUTE
      );
    }
    
    return $this->route;
  }
  
  /**
   * Returns a route that's constructed based on the current request.
   *
   * @return RouteInterface
   * @throws RepositoryException
   */
  protected function getAutoRoute(): RouteInterface
  {
    // the auto-routing capability uses information in our current request to
    // construct a RouteInterface object on the fly and returns it.  we assume
    // that whatever environment is using this object in that capacity will
    // know what to do from there.
    
    $route = $this->factory->produceBlankRoute();
    $route = $this->setRouteActionAndParameter($route);
    
    // now, we've set our route's action and action parameter, we need to
    // handle the path, method, and privacy.  the first two are easy; we can
    // simply pass our own properties over to it.  privacy is harder, so we'll
    // call the method below to allow apps using this router to override our
    // default and do something more useful.
    
    $route->setPath($this->path);
    $route->setMethod($this->method);
    $route->setPrivate($this->isRoutePrivate($route));
    
    return $route;
  }
  
  /**
   * Sets the action and, optionally, the action parameter of our route and
   * returns it back to the calling scope.
   *
   * @param RouteInterface $route
   *
   * @return RouteInterface
   */
  protected function setRouteActionAndParameter(RouteInterface $route): RouteInterface
  {
    // we split our path based on the forward slashes between its parts.  then,
    // we add our method onto the front of it.  then, we need to filter it into
    // two different sets of parts:  the numeric ones and the nonnumerical
    // ones.  the former become our action parameter, the latter we transform
    // so that they become our action.
    
    $parts = $this->getActionParts();
    $parameters = array_filter($parts, fn($p) => is_numeric($p));
    
    // now we have the set of action parts that are numeric.  we could do
    // another filter to get the non-numeric ones, but we can also just use
    // array_diff().  we'll hope that avoiding the callback and filtering loop
    // saves us some time; we can always benchmark it later if we want to.
    
    $action = array_diff($parts, $parameters);
    $route->setAction($this->transformAction($action));
    $route->setActionParameter($parameters);
    return $route;
  }
  
  /**
   * Returns an array of the method followed by the parts of our path, so that
   * we can use them to identify our Action.
   *
   * @return array
   */
  protected function getActionParts(): array
  {
    // we'll split our path using the forward slash that separates the URL
    // of our request.  then, we filter out the blanks.  we add the method
    // onto the front of that array and that gives us the parts that we
    // need to work with.
    
    $parts = array_filter(explode("/", $this->path));
    array_unshift($parts, $this->method);
    
    // before we return, we want to see if there's only one value in the
    // array.  if so, that means we're at the root of this domain.  in that
    // case, we add "index" to our array so that our action will be either
    // GetIndex or, perhaps, PostIndex.
    
    if (sizeof($parts) === 1) {
      $parts[] = "index";
    }
    
    return $parts;
  }
  
  /**
   * Given an array of actions parts (e.g. [foo, bar, baz]), returns
   * the name of our action in StudlyCaps (e.g. FooBarBaz).
   *
   * @param array $actionParts
   *
   * @return string
   */
  protected function transformAction(array $actionParts): string
  {
    // there are two options:  simple words and hyphened compound words.  the
    // former are easy:  we just capitalize them.  the latter are words in
    // kebab-case, so we switch that to PascalCase.
    
    $mapper = fn($part) => str_contains($part, '-')
      ? $this->kebabToPascalCase($part)
      : ucfirst($part);
    
    return join('', array_map($mapper, $actionParts));
  }
  
  /**
   * By default, we just return false.  Extensions of this object can provide
   * the means to determine between public and private routes, and to do so we
   * assume they'll need the $route parameter.  But, we don't at the moment, so
   * we suppress the IDE warning that leaving it alone might cause.
   *
   * @param RouteInterface $route
   *
   * @return bool
   * @noinspection PhpUnusedParameterInspection
   */
  protected function isRoutePrivate(RouteInterface $route): bool
  {
    return false;
  }
  
  /**
   * Returns the routes collected by this Router.
   *
   * @return array
   * @throws RouterException
   */
  public function getRoutes(): array
  {
    return $this->autoRouter
      ? $this->collection->getRoutes()
      : throw new RouterException(
        'Auto-routers don\'t collect routes.',
        RouterException::UNEXPECTED_AUTOROUTER_ACTION
      );
  }
  
  /**
   * Adds a route to this object's collection and sets the dirty flag so we
   * know we have to search through it (again) to find the current route.
   *
   * @param array $route
   *
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   * @throws RouterException
   */
  public function addRoute(array $route): void
  {
    if ($this->autoRouter) {
      throw new RouterException(
        'Auto-routers don\'t collect routes.',
        RouterException::UNEXPECTED_AUTOROUTER_ACTION
      );
    }
    
    $this->collection->addRoute($this->factory->produceRoute($route));
    $this->dirty = true;
  }
  
  /**
   * Adds an array of routes to this object's collection
   *
   * @param array $routes
   *
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   * @throws RouterException
   */
  public function addRoutes(array $routes): void
  {
    foreach ($routes as $route) {
      $this->addRoute($route);
    }
  }
}
