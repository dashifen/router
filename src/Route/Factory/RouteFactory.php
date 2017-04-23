<?php

namespace Dashifen\Router\Route\Factory;

use Dashifen\Router\Route\Route;
use Dashifen\Router\Route\RouteInterface;

/**
 * Class RouteFactory
 *
 * @package Dashifen\Router\Route\Factory
 */
class RouteFactory implements RouteFactoryInterface {
	/**
	 * @param array $data
	 *
	 * @return RouteInterface
	 * @throws RouterFactoryException
	 */
	public function produceRoute(array $data): RouteInterface {
		$keys = array_keys($data);
		if ($keys !== ["method", "path", "action", "private"]) {
			throw new RouterFactoryException("Invalid route information or order: " . join(", ", $keys) . ".");
		}
		
		return new Route(...$data);
	}
}
