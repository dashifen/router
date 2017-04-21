<?php

namespace Dashifen\Router\Route\Collection;

use Dashifen\Router\Route\RouteInterface;

/**
 * Interface RouteCollectionInterface
 *
 * @package Dashifen\Router\Route\Collection
 */
interface RouteCollectionInterface {
	/**
	 * @param RouteInterface $route
	 *
	 * @throws RouteCollectionException
	 * @return void
	 */
	public function addRoute(RouteInterface $route): void;
	
	/**
	 * @param string $method
	 * @param string $path
	 *
	 * @return bool
	 */
	public function hasRoute(string $method, string $path): bool;
	
	/**
	 * @param string $method
	 * @param string $path
	 *
	 * @throws RouteCollectionException
	 * @return RouteInterface
	 */
	public function getRoute(string $method, string $path): RouteInterface;
	
	/**
	 * @return RouteInterface[];
	 */
	public function getRoutes(): array;
}
