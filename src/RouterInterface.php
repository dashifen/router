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
   * Returns true if this is an "auto-router."  An auto-router determines the
   * route all on its own based on the current request; you don't have to
   * hand-enter routes for it.
   *
   * @return bool
   */
  public function isAutoRouter(): bool;

  /**
   * Returns the route for the current request; will construct that route
   * if this is an auto-router.
   *
   * @return RouteInterface
   */
  public function getRoute (): RouteInterface;

  /**
   * Returns the full set of routes in the router's collection.  Throws an
   * exception if this is an auto-router.
   *
   * @return array
   * @throws RouterException
   */
  public function getRoutes (): array;

  /**
   * Adds a route to this router's collection based on the indices of the
   * parameter array, but throws an exception fi this is an auto-router.
   *
   * @param array $route
   *
   * @return void
   */
  public function addRoute (array $route): void;

  /**
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
