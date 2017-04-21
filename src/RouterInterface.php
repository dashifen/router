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
	public function getRoutes(): array;
	
	/**
	 * @param array $routes
	 *
	 * @throws RouterException
	 * @return void
	 */
	public function setRoutes(array $routes): void;
	
	/**
	 * @return RouteInterface|null
	 */
	public function getRoute(): ?RouteInterface;
	
	/**
	 * @param array $route
	 *
	 * @return void
	 */
	public function setRoute(array $route): void;
}
