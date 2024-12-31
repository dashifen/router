<?php

namespace Dashifen\Router\Route;

/**
 * Interface RouteInterface
 *
 * @package Dashifen\Router\Route
 */
interface RouteInterface {
  /**
	 * @param RouteInterface $route
	 *
	 * @return bool
	 */
	public function matchRoute(RouteInterface $route): bool;
  
  /**
   * @param array $order
   *
   * @throws RouteException
   * @return array;
   */
  public function getRouteData(array $order): array;
}
