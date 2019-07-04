<?php

namespace Dashifen\Router\Route\Factory;

use Dashifen\Repository\RepositoryException;
use Dashifen\Router\Route\RouteInterface;

/**
 * Interface RouteFactoryInterface
 *
 * @package Dashifen\Router\Route\Factory
 */
interface RouteFactoryInterface {
	/**
   * produceRoute
   *
   * Given an array of data, that should provide some or all of our Route
   * properties, returns a Route object created from that array.
   *
	 * @param array $data
	 *
	 * @return RouteInterface
   * @throws RouterFactoryException
   * @throws RepositoryException
	 */
	public function produceRoute(array $data): RouteInterface;

  /**
   * produceBlankRoute
   *
   * Returns a blank route with no data.  Properties are set in the calling
   * scope.  This is most likely used during the auto-routing functionality
   * of our Router object.
   *
   * @return RouteInterface
   * @throws RepositoryException
   */
	public function produceBlankRoute(): RouteInterface;
}
