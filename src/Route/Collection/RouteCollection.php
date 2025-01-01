<?php

namespace Dashifen\Router\Route\Collection;

use Dashifen\Router\Route\RouteInterface;
use Dashifen\Collection\AbstractCollection;

/**
 * Class RouteCollection
 */
class RouteCollection extends AbstractCollection implements RouteCollectionInterface
{
  /**
   * @var RouteInterface[]
   */
  protected array $collection = [];
  
  /**
   * Given the name of an HTTP method, returns the routes in our collection
   * which match that method.
   *
   * @param string $method
   *
   * @return array
   */
  public function getRoutes(string $method): array
  {
    // we don't care what $method is used, but we do capitalize it because we
    // make sure RouteInterface objects have capitalized methods in their
    // definitions.  we could capitalize it within the arrow function, but then
    // we'd call that function over and over again.  instead, we call it once
    // and then access it via closure within the arrow function.
    
    $method = strtoupper($method);
    $filter = fn($route) => $route->method === $method;
    return array_filter($this->collection, $filter);
  }
  
  /**
   * Returns the entire collection.
   *
   * @return RouteInterface[]
   */
  public function getCollection(): array
  {
    return parent::getCollection();
  }
  
  /**
   * Returns the value at the current index in our collection.
   *
   * @return RouteInterface
   */
  public function current(): RouteInterface
  {
    return parent::current();
  }
  
  /**
   * Returns the value at the specified index within the collection.  Note:  we
   * know that the $offset parameters will be strings because of the way we
   * wrote ::valid above, but we can't typehint things here because that would
   * create a mismatch between this method and the ArrayAccess interface.
   *
   * @param mixed $offset
   *
   * @return RouteInterface|null
   */
  public function offsetGet(mixed $offset): ?RouteInterface
  {
    return parent::offsetGet($offset);
  }
  
  /**
   * Adds the value to the collection at the specified index.
   *
   * @param mixed          $offset
   * @param RouteInterface $value
   *
   * @return void
   * @throws RouteCollectionException
   */
  public function offsetSet(mixed $offset, mixed $value): void
  {
    if (!is_a($value, RouteInterface::class)) {
      throw new RouteCollectionException(
        'RouteCollection can only contain RouteInterface objects.',
        RouteCollectionException::INVALID_COLLECTABLE
      );
    }
    
    parent::offsetSet($offset, $value);
  }
}
