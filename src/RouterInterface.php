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
	 * @return string
	 */
	public function route(): string;

	/**
	 * @return array
	 */
	public function getRoutes(): array;
	
	/**
	 * @return RouteInterface|null
	 */
	public function getRoute(): ?RouteInterface;
	
	/**
	 * @param array $routes
	 *
	 * @throws RouterException
	 * @return void
	 */
	public function addRoutes(array $routes): void;
	
	/**
	 * @param array $route
	 *
	 * @return void
	 */
	public function addRoute(array $route): void;
}
