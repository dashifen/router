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
   * Given an array of data, that should provide some or all of our Route
   * properties, returns a Route object created from that array.
   *
	 * @param array $data
	 *
	 * @return RouteInterface
   * @throws RouterFactoryException
	 */
	public static function produceRoute(array $data): RouteInterface;

  /**
   * Returns a blank route with no data.  Properties are set in the calling
   * scope.  This is most likely used during the auto-routing functionality
   * of our Router object.
   *
   * @return RouteInterface
   */
	public static function produceBlankRoute(): RouteInterface;
}
