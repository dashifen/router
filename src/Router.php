<?php

namespace Dashifen\Router;

use Dashifen\Request\RequestInterface;
use Dashifen\Router\Route\RouteInterface;
use Dashifen\Router\Route\Factory\RouteFactoryInterface;
use Dashifen\Router\Route\Factory\RouterFactoryException;
use Dashifen\Router\Route\Collection\RouteCollectionException;
use Dashifen\Router\Route\Collection\RouteCollectionInterface;

class Router implements RouterInterface {
  /**
   * @var RouteCollectionInterface $collection
   */
  protected $collection;

  /**
   * @var string $method
   */
  protected $method;

  /**
   * @var string $path
   */
  protected $path;

  /**
   * @var RouteFactoryInterface $factory
   */
  protected $factory;

  /**
   * @var @var RouteInterface
   */
  protected $route;

  /**
   * @var bool
   */
  protected $dirty = false;

  /**
   * Router constructor.
   *
   * @param RequestInterface         $request
   * @param RouteCollectionInterface $collection
   * @param RouteFactoryInterface    $factory
   * @param array                    $routes
   *
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   */
  public function __construct (
    RequestInterface $request,
    RouteCollectionInterface $collection,
    RouteFactoryInterface $factory,
    array $routes = []
  ) {
    $this->method = $request->getServerVar("REQUEST_METHOD");
    $this->path = $request->getServerVar("REQUEST_URI");
    $this->collection = $collection;
    $this->factory = $factory;

    $this->addRoutes($routes);
  }

  /**
   * getRoutes
   *
   * Returns the routes collected by this Router.
   *
   * @return array
   */
  public function getRoutes (): array {
    return $this->collection->getRoutes();
  }

  /**
   * addRoutes
   *
   * Adds an array of routes to this object's collection
   *
   * @param array $routes
   *
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   */
  public function addRoutes (array $routes): void {
    foreach ($routes as $route) {
      $this->addRoute($route);
    }
  }

  /**
   * getRoute
   *
   * Sets and returns the route property the first time it's called; thereafter
   * re-returns the previously identified route.  Assumption:  list of routes
   * doesn't change between calls.
   *
   * @return RouteInterface
   * @throws RouteCollectionException
   * @throws RouterException
   */
  public function getRoute (): RouteInterface {

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
      throw new RouterException("Unexpected route: $this->method;$this->path.",
        RouterException::UNEXPECTED_ROUTE
      );
    }

    return $this->route;
  }

  /**
   * addRoute
   *
   * Adds a route to this object's collection and sets the dirty flag so we
   * know we have to search through it (again) to find the current route.
   *
   * @param array $route
   *
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   */
  public function addRoute (array $route): void {
    $this->collection->addRoute($this->factory->produceRoute($route));
    $this->dirty = true;
  }
}
