<?php

namespace Dashifen\Router\Route\Factory;

use Dashifen\Router\Route\RouteInterface;

/**
 * Interface RouteFactoryInterface
 *
 * @package Dashifen\Router\Route\Factory
 */
interface RouteFactoryInterface {
	/**
	 * @param array $data
	 *
	 * @throws RouterFactoryException
	 * @return RouteInterface
	 */
	public function produceRoute(array $data): RouteInterface;
}
