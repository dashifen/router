<?php

namespace Dashifen\Router\Route\Collection;

use Dashifen\Router\Route\RouteInterface;

/**
 * Class RouteCollection
 *
 * @package Dashifen\Router\Route\Collection
 */
class RouteCollection implements RouteCollectionInterface {
	protected const ACTION_PARAMETER_PATTERN = "|%s/([^/]*)|";
	
	/**
	 * @var array $collection ;
	 */
	protected $collection = [];
	
	/**
	 * RouteCollection constructor.
	 */
	public function __construct() {
		$this->collection = ["GET" => [], "POST" => []];
	}
	
	/**
	 * @param RouteInterface $route
	 *
	 * @throws RouteCollectionException
	 */
	public function addRoute(RouteInterface $route): void {
		$method = $route->getMethod();
		$path = $route->getPath();
		
		if ($this->hasRoute($method, $path)) {
			throw new RouteCollectionException("Duplicate route: $method;$path.", RouteCollectionException::DUPLICATE_ROUTE);
		}
		
		$this->collection[$method][$path] = $route;
	}
	
	/**
	 * @param string $method
	 * @param string $path
	 *
	 * @return bool
	 */
	public function hasRoute(string $method, string $path): bool {
		
		// there are two ways that this collection might contain the
		// specified route:  an exact match or a partial match.  exact
		// matches are easier and, hopefully, more common; we'll do
		// those first.
		
		$paths = array_keys($this->collection[$method]);
		
		if (in_array($path, $paths)) {
			return true;
		}
		
		// if we haven't left yet, then we don't have an exact match for
		// this method/path combination.  so, it's time to look for a
		// partial one.  we do so by assuming each path is a regular
		// expression by using each partial path as the substitution in
		// the constant above.
		
		foreach ($paths as $partial) {
			$pattern = sprintf(self::ACTION_PARAMETER_PATTERN, $partial);
			
			if (preg_match($pattern, $path)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @param string $method
	 * @param string $path
	 *
	 * @return RouteInterface
	 * @throws RouteCollectionException
	 */
	public function getRoute(string $method, string $path): RouteInterface {
		
		// just like our lookup, we want to handle both exact matches
		// and prefix matches.  an exact match is easy; if we have a
		// route at [$method][$path] we're done.
		
		if (isset($this->collection[$method][$path])) {
			return $this->collection[$method][$path];
		}
		
		// now comes the harder part.  we have to re-do our regular
		// expression style search, but this time we want to identify
		// our action parameter.  using the constant above, when we
		// find our path, we'll be able to extract that parameter as
		// the first parenthetical match within it.
		
		$paths = array_keys($this->collection[$method]);
		
		foreach ($paths as $partial) {
			$pattern = sprintf(self::ACTION_PARAMETER_PATTERN, $partial);
			
			if (preg_match($pattern, $path, $matches)) {
				/** @var RouteInterface $route */
				
				// now that we've found our route, we'll extract it from
				// the array, set its action parameter to the matched part
				// of our $path, and then return it.
				
				$route = $this->collection[$method][$partial];
				$route->setActionParameter($matches[1]);
				return $route;
			}
		}
		
		// and, if we haven't return a route by this point, we ain't
		// gonna be able to.  so, instead we'll throw a tantrum ... err,
		// an exception.
		
		throw new RouteCollectionException("Unexpected route: $method;$path",
			RouteCollectionException::UNEXPECTED_ROUTE);
	}
	
	/**
	 * @return RouteInterface[]
	 */
	public function getRoutes(): array {
		return $this->collection;
	}
}
