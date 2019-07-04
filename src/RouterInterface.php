<?php

namespace Dashifen\Router;

use Dashifen\Router\Route\RouteInterface;

/**
 * Interface RouterInterface
 *
 * @package Dashifen\Router
 */
interface RouterInterface {
  /**
   * isAutoRouter
   *
   * An "auto-router" automatically constructs the RouteInterface based
   * on the current request.  There's no need to add routes to its collection,
   * it'll figure it all out on its own!
   *
   * @param bool|null $autoRouterState
   *
   * @return bool
   */
  public function isAutoRouter(?bool $autoRouterState = null): bool;

  /**
   * getRoute
   *
   * Returns the route for the current request; will construct that route
   * if this is an auto-router.
   *
   * @return RouteInterface
   */
  public function getRoute (): RouteInterface;

  /**
   * getRouters
   *
   * Returns the full set of routes in the router's collection.  Throws an
   * exception if this is an auto-router.
   *
   * @return array
   * @throws RouterException
   */
  public function getRoutes (): array;

  /**
   * addRoute
   *
   * Adds a route to this router's collection based on the indices of the
   * parameter array, but throws an exception fi this is an auto-router.
   *
   * @param array $route
   *
   * @return void
   */
  public function addRoute (array $route): void;

  /**
   * addRoutes
   *
   * Adds the specified routes to this router's collection, but throws an
   * exception if this is an auto-router.
   *
   * @param array $routes
   *
   * @return void
   * @throws RouterException
   */
  public function addRoutes (array $routes): void;
}
