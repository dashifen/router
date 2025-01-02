<?php

namespace Dashifen\Router\Route;

use ReflectionClass;
use ReflectionProperty;
use Dashifen\Router\Action\ActionInterface;

/**
 * Class Route
 */
class Route implements RouteInterface
{
  protected(set) string $method {
    set {
      
      // if the uppercase version of our value is not considered a viable
      // method, then we throw a RouteException.  this pattern is repeated
      // below:  tenable values are set, but untenable ones cause an
      // exception.
      
      $this->method = !in_array(($method = strtoupper($value)), ['GET', 'POST'])
        ? throw new RouteException("Unexpected method: $value.", RouteException::UNKNOWN_METHOD)
        : $method;
    }
  }
  
  protected(set) string $path {
    get {
      if (!isset($this->path)) {
        return '';
      }
      
      // for convenience, we want our paths to begin with slashes and not end
      // with them.  this is simply our opinion as a default, if you don't like
      // it, feel free to extend and override this getter.
      
      $value = !str_starts_with($this->path, '/')
        ? '/' . $this->path
        : $this->path;
      
      return str_ends_with($value, '/')
        ? substr($value, 0, strlen($value) - 1)
        : $value;
    }
    
    set {
      $this->path = ($path = parse_url($value, PHP_URL_PATH)) === false
        ? throw new RouteException("Invalid path: $value.", RouteException::UNKNOWN_PATH)
        : $path;
    }
  }
  
  protected(set) string $action {
    set {
      
      // actions are meant to be objects that perform whatever we want to
      // happen when a given route is requested.  therefore, our test here is
      // to see if $value is an existing class or not, and if it does exist,
      // it must also implement the ActionInterface.
      
      $this->action = !class_exists($value) && is_a($value, ActionInterface::class)
        ? throw new RouteException("Unknown action: $value", RouteException::UNKNOWN_ACTION)
        : $value;
    }
  }
  
  protected(set) bool $private = false {
    set => $value;
  }
  
  protected(set) array $actionParameter = [] {
    set => $value;
  }
  
  protected array $properties;
  
  /**
   * Constructs a Route using $data which must map the names of the above
   * properties to the values we want them to have.
   *
   * @param array $data
   *
   * @throws RouteException
   */
  public function __construct(array $data = [])
  {
    foreach ($data as $property => $value) {
      
      // by default, our isProperty method throws an exception if an index
      // within the $data parameter does not match one of the above properties.
      // if you would prefer that it returns false instead, override it.  or,
      // override this constructor and catch the exception as needed.
      
      if ($this->isProperty($property)) {
        $this->$property = $value;
      }
    }
  }
  
  /**
   * Returns true if $property matches the name of one of our properties, and
   * otherwise throws an exception.
   *
   * @param string $property
   *
   * @return bool
   * @throws RouteException
   */
  protected function isProperty(string $property): true
  {
    if (!isset($this->properties)) {
      
      // the first time we get here, the properties property will be unset.  to
      // set it we create a reflection of this class and then extract its
      // properties.  however, these will be ReflectionProperty objects; all we
      // want are their names.  therefore, we use array_map to convert from the
      // objects down to simple strings.
      
      $reflection = new ReflectionClass(static::class);
      $reflectionProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
      $mapper = fn(ReflectionProperty $property) => $property->getName();
      $this->properties = array_map($mapper, $reflectionProperties);
    }
    
    if (!in_array($property, $this->properties)) {
      throw new RouteException(
        "Unknown property: $property.",
        RouteException::UNKNOWN_PROPERTY
      );
    }
    
    return true;
  }
  
  /**
   * Returns true if this Route matches the one passed here as the
   * parameter to this method.
   *
   * @param RouteInterface $route
   *
   * @return bool
   * @throws RouteException
   */
  public function matchRoute(RouteInterface $route): bool
  {
    return $this->getRouteData() === $route->getRouteData();
  }
  
  /**
   * Returns the full set of route information based on the values from
   * our properties.
   *
   * @param array $order
   *
   * @return array
   * @throws RouteException
   */
  public function getRouteData(
    array $order = ['path', 'action', 'method', 'private']
  ): array {
    $routeData = [];
    foreach ($order as $property) {
      if ($this->isProperty($property) && $property !== 'actionParameter') {
        
        // since this method is also used in the __toString method below, we
        // want everything to be printable.  false values get printed as empty
        // strings, so we'll explicitly convert those to numeric values here
        // so that false values become zeros instead of blanks.  note that we
        // skip the actionParameter property so that we don't have to worry
        // about the array here.
        
        $routeData[] = is_bool($this->$property)
          ? (int) $this->$property
          : $this->$property;
      }
    }
    
    return $routeData;
  }
  
  /**
   * Returns a string build from our route properties for display purposes.
   *
   * @return string
   * @throws RouteException
   */
  public function __toString(): string
  {
    $routeData = $this->getRouteData();
    return vsprintf('%s (%s, %s, %s)', $routeData);
  }
}
