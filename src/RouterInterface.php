<?php

namespace Dashifen\Router;

use Dashifen\Router\Route\RouteInterface;

/**
 * Interface RouterInterface
 *
 * @package Dashifen\Router
 */
interface RouterInterface
{
  /**
   * Returns the full set of routes in the router's collection.  Throws an
   * exception if this is an auto-router.
   *
   * @return array
   * @throws RouterException
   */
  public function getRoutes(): array;
}
