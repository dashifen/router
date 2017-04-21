<?php

namespace Dashifen\Router\Route\Collection;

use Dashifen\Router\Route\RouteInterface;

/**
 * Class RouteCollection
 *
 * @package Dashifen\Router\Route\Collection
 */
class RouteCollection implements RouteCollectionInterface {
	
	/**
	 * @var array $collection ;
	 */
	protected $collection = [];
	
	/**
	 * @param RouteInterface $route
	 *
	 * @throws RouteCollectionException
	 */
	public function addRoute(RouteInterface $route): void {
		$method = $route->getMethod();
		$path = $route->getPath();
		
		if ($this->hasRoute($method, $path)) {
			throw new RouteCollectionException("Duplicate route: $method;$path.");
		}
		
		$this->collection[$this->getIndex($method, $path)] = $route;
	}
	
	/**
	 * @param string $method
	 * @param string $path
	 *
	 * @return bool
	 */
	public function hasRoute(string $method, string $path): bool {
		return isset($this->collection[$this->getIndex($method, $path)]);
	}
	
	/**
	 * @param string $method
	 * @param string $path
	 *
	 * @return RouteInterface
	 * @throws RouteCollectionException
	 */
	public function getRoute(string $method, string $path): RouteInterface {
		if (!$this->hasRoute($method, $path)) {
			throw new RouteCollectionException("Unexpected route: $method;$path.");
		}
		
		return $this->collection[$this->getIndex($method, $path)];
	}
	
	/**
	 * @return RouteInterface[]
	 */
	public function getRoutes(): array {
		return $this->collection;
	}
	
	/**
	 * @param string $method
	 * @param string $path
	 *
	 * @return string
	 */
	protected function getIndex(string $method, string $path): string {
		return "$method;$path";
	}
}
