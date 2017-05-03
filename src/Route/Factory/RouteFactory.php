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
			throw new RouterFactoryException(
				"Invalid route information or order: " . join(", ", $keys) . ".",
				RouterFactoryException::INVALID_ORDER
			);
		}
		
		// PHP doesn't let you unpack associative arrays.  so, now that we
		// know things are in the right order (or we'd have thrown an exception
		// above), we can use array_values() to numerically index our array
		// and then unpack it!
		
		return new Route(...array_values($data));
	}
}
