<?php

namespace Dashifen\Router;

use Dashifen\Router\Route\Route;
use Dashifen\Request\RequestInterface;
use Dashifen\Router\Route\RouteInterface;
use Dashifen\Router\Route\RouteException;
use Dashifen\CaseChangingTrait\CaseChangingTrait;
use Dashifen\Router\Route\Collection\RouteCollectionInterface;

class Router implements RouterInterface
{
  use CaseChangingTrait;
  
  protected string $path;
  protected string $method;
  
  /**
   * Router constructor.
   *
   * @param RequestInterface              $request
   * @param bool                          $autoRouter
   * @param RouteCollectionInterface|null $collection
   */
  public function __construct(
    RequestInterface $request,
    protected readonly bool $autoRouter = false,
    protected ?RouteCollectionInterface $collection = null
  ) {
    $this->path = $request->getServerVar("REQUEST_URI");
    $this->method = $request->getServerVar("REQUEST_METHOD");
  }
  
  /**
   * Returns the route for the current request.  If this isn't an auto-router,
   * we use our collection to do so.  Otherwise, we construct the route based
   * on the request.
   *
   * @return RouteInterface
   * @throws RouterException
   * @throws RouteException
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
    return "($this->method) $this->path";
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
      'method'  => $this->method,
      'path'    => $this->path,
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
    // for simplicity's sake, we're going to assume that a person will name
    // their action based on the final part of the URL path.  thus, a path
    // that looks like /foo/bar/login will use a Login action while one that
    // looks like /foo/bar/process-login will use ProcessLogin.  if there's
    // no hyphen in that final part of the path, we just capitalize its first
    // letter and return it.  if it is hyphenated, then we have a method in the
    // CaseChangingTrait that will convert kebab-case to PascalCase, and it'll
    // do the work for us.
    
    $debris = explode('/', $this->path);
    return str_contains(($action = array_pop($debris)), '-')
      ? $this->kebabToPascalCase($action)
      : ucfirst($action);
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
   * @throws RouterException
   */
  public function getRoutes(): array
  {
    return !$this->autoRouter
      ? $this->collection->getCollection()
      : throw new RouterException(
        'Auto-routers don\'t collect routes.',
        RouterException::UNEXPECTED_AUTOROUTER_ACTION
      );
  }
}
