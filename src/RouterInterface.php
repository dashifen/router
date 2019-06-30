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
   * @return array
   */
  public function getRoutes (): array;

  /**
   * @return RouteInterface
   */
  public function getRoute (): RouteInterface;

  /**
   * @param array $routes
   *
   * @return void
   * @throws RouterException
   */
  public function addRoutes (array $routes): void;

  /**
   * @param array $route
   *
   * @return void
   */
  public function addRoute (array $route): void;
}
