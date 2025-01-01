<?php

namespace Dashifen\Router\Route\Collection;

use Dashifen\Collection\CollectionInterface;

interface RouteCollectionInterface extends CollectionInterface
{
  /**
   * Given the name of an HTTP method, returns the routes in our collection
   * which match that method.
   *
   * @param string $method
   *
   * @return array
   */
  public function getRoutes(string $method): array;
}
