<?php

namespace Dashifen\Router;

use Dashifen\Request\Request;
use Dashifen\Router\Route\Route;
use Dashifen\Request\RequestInterface;
use Dashifen\Router\Route\RouteInterface;
use Dashifen\Router\Route\RouteException;
use Dashifen\CaseChangingTrait\CaseChangingTrait;
use Dashifen\Router\Route\Collection\RouteCollectionInterface;

class Router implements RouterInterface
{
  use CaseChangingTrait;
  
  protected(set) RouteInterface $route {
    get {
      return $this->collection !== null
        ? $this->getCollectedRoute()
        : $this->getAutoRoute();
    }
    
    set {
      throw new RouterException(
        'Do not set the route property; just let the get hook handle things.',
        RouterException::UNEXPECTED_ROUTE
      );
    }
  }
  
  /**
   * Router constructor.
   *
   * @param RequestInterface|null         $request
   * @param RouteCollectionInterface|null $collection
   */
  public function __construct(
    protected ?RouteCollectionInterface $collection = null,
    protected(set) ?RequestInterface $request = null,
  ) {
    // the null state of the collection property determines if this is an
    // auto-router or not.  so, if it's null, we leave that alone and assume
    // that the developer knows what they're doing.  but, for the request, if
    // it's null, we'll set it to our reasonable default which will read
    // information based on the current HTTP request being processed which is
    // very likely what we want anyway.
    
    $this->request ??= new Request();
  }
  
  /**
   * Returns a route based on our collected routes.
   *
   * @return RouteInterface
   * @throws RouterException
   */
  protected function getCollectedRoute(): RouteInterface
  {
    $index = $this->getRouteIndex();
    if (!isset($this->collection[$index])) {
      throw new RouterException('Unexpected route: $index.',
        RouterException::UNEXPECTED_ROUTE);
    }
    
    return $this->collection[$index];
  }
  
  /**
   * Returns a string used as the index for a Route within our RouteCollection.
   * Override this based on the needs of your application if "(METHOD) path"
   * won't work for you.
   *
   * @return string
   */
  protected function getRouteIndex(): string
  {
    return sprintf(
      '(%s) %s',
      $this->request->getServerVar('REQUEST_METHOD'),
      $this->request->getServerVar('REQUEST_URI')
    );
  }
  
  /**
   * Returns a route that's constructed based on the current request.
   *
   * @return RouteInterface
   * @throws RouteException
   */
  protected function getAutoRoute(): RouteInterface
  {
    // the auto-routing capability uses information in our current request to
    // construct a RouteInterface object on the fly and returns it.  we assume
    // that whatever environment is using this object in that capacity will
    // know what to do from there.
    
    return new Route([
      'method'  => $this->request->getServerVar('REQUEST_METHOD'),
      'path'    => $this->request->getServerVar('REQUEST_URI'),
      'action'  => $this->getAction(),
      'private' => $this->isRoutePrivate(),
    ]);
  }
  
  /**
   * Returns the name of the object we expect to handle a Route when using an
   * auto-router.
   *
   * @return string
   */
  protected function getAction(): string
  {
    $debris = explode('/', $this->request->getServerVar('REQUEST_URI'));
    
    // for simplicity's sake, we're going to assume that a person will name
    // their action based on the final part of the URL path.  thus, a path
    // that looks like /foo/bar/login will use LoginAction while one that looks
    // like /foo/bar/process-login will use ProcessLoginAction.  the use of
    // strtok when assigning our $action variable makes sure that we remove the
    // query string from our request before proceeding.
    
    $action = str_contains($action = strtok(array_pop($debris), '?'), '-')
      ? $this->kebabToPascalCase($action)
      : ucfirst($action);
      
    return empty($action) ? 'IndexAction' : $action . 'Action';
  }
  
  /**
   * Returns true if the Route produced by an auto-router should be private.
   * By default, we just return false; extensions can override this as needed.
   *
   * @return bool
   */
  protected function isRoutePrivate(): bool
  {
    return false;
  }
  
  /**
   * Returns the routes collected by this Router.
   *
   * @return array
   */
  public function getRoutes(): array
  {
    return $this->collection !== null
      ? $this->collection->getCollection()
      : [];
  }
}
