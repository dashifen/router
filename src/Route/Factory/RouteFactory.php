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
		if (!is_array($data)) {
			throw new RouterFactoryException("Invalid route information: $data.");
		}
		
		$keys = array_keys($data);
		if ($keys !== ["method", "path", "action", "private"]) {
			$keys = join(", ", $keys);
			throw new RouterFactoryException("Invalid route information order: $keys.");
		}
		
		return new Route(...$data);
	}
}
