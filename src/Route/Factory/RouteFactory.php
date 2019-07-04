<?php

namespace Dashifen\Router\Route\Factory;

use Dashifen\Repository\RepositoryException;
use Dashifen\Router\Route\Route;
use Dashifen\Router\Route\RouteException;
use Dashifen\Router\Route\RouteInterface;

/**
 * Class RouteFactory
 *
 * @package Dashifen\Router\Route\Factory
 */
class RouteFactory implements RouteFactoryInterface {
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
  public function produceRoute (array $data): RouteInterface {
    $diff = array_diff(array_keys($data), ["method", "path", "action", "private"]);

    // array_diff() returns the set of values in the first array that are
    // not found in the latter.  since our $data should only be the set of
    // keys listed (or a subset thereof), if $diff is not empty, then we've
    // got a problem.

    if (sizeof($diff) > 0) {
      throw new RouterFactoryException(
        "Invalid route information or order: " . join(", ", $keys) . ".",
        RouterFactoryException::INVALID_ORDER
      );
    }

    // PHP doesn't let you unpack associative arrays.  so, now that we
    // know things are in the right order (or we'd have thrown an exception
    // above), we can use array_values() to numerically index our array
    // and then unpack it!

    return new Route($data);
  }

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
  public function produceBlankRoute (): RouteInterface {
    return new Route([]);
  }
}
