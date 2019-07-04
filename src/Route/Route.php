<?php

namespace Dashifen\Router\Route;

use Dashifen\Repository\Repository;

/**
 * Class AbstractRoute
 *
 * @package Dashifen\Router\Route
 *
 * @property string $method
 * @property string $path
 * @property string $action
 * @property bool   $private
 * @property array  $actionParameter
 */
class Route extends Repository implements RouteInterface {
  /**
   * @var string $method
   */
  protected $method;

  /**
   * @var string $path
   */
  protected $path;

  /**
   * @var string $action
   */
  protected $action;

  /**
   * @var bool $private
   */
  protected $private = false;

  /**
   * @var array $actionParameter
   */
  protected $actionParameter = [];

  /**
   * setMethod
   *
   * Sets the method property, which by default must be either GET or
   * POST.
   *
   * @param string $method
   *
   * @return void
   * @throws RouteException
   */
  public function setMethod (string $method): void {
    $method = strtoupper($method);
    if (!in_array($method, $this->getViableMethods())) {
      throw new RouteException("Unexpected method: $method.");
    }

    $this->method = $method;
  }

  /**
   * getViableMethods
   *
   * By default, returns an array containing GET and POST for use in the
   * prior method.  If you want to allow PUT and/or DELETE (or disallow GET
   * and/or POST) override this method and return some other array of viable
   * methods.
   *
   * @return array
   */
  protected function getViableMethods (): array {
    return ["GET", "POST"];
  }

  /**
   * setPath
   *
   * Sets the path property which, by default, is expected to be a series
   * of "words" separated by forward slashes.  A "word" in this context is
   * made up of one or more characters that match \w in a regular
   * expression.
   *
   * @param string $path
   *
   * @return void
   * @throws RouteException
   */
  public function setPath (string $path): void {

    // paths should be words separated by forward-slashes.  we
    // can check for that as follows.  first, if there's not one
    // in the front of our string, we add it.

    if (substr($path, 0, 1) !== '/') {
      $path = '/' . $path;
    }

    if (!preg_match($this->getPathPattern(), $path)) {
      throw new RouteException("Invalid path: $path.");
    }

    $this->path = $path;
  }

  /**
   * getPathPattern
   *
   * Returns the pattern used when setting the path property to identify
   * viable paths within this app.  Override to change from the default:
   * a series of "words" separated by forward slashes where "words" are
   * made up of one or more characters that match \w in regular
   * expressions.
   *
   * @return string
   */
  protected function getPathPattern (): string {
    return '/(?:\/(?:\w+)?)+/';
  }

  /**
   * setAction
   *
   * Sets the Action property, an optionally namespaced object name in
   * StudlyCaps by default.
   *
   * @param string $action
   *
   * @return void
   * @throws RouteException
   */
  public function setAction (string $action): void {

    // action should be a fully namespaced class name.  so, it's
    // words separated by back-slashes.  we can check for that here.
    // why do we use three back slashes in the regular expression?
    // see http://stackoverflow.com/a/15369828/360838 (2017-04-30)

    if (!preg_match($this->getActionPattern(), $action)) {
      throw new RouteException("Invalid action: $action");
    }

    $this->action = $action;
  }

  /**
   * getActionPattern
   *
   * Returns a regular expression pattern used to identify valid Action
   * object names.  By default, it should be an object name in StudlyCaps
   * (as per PSR-1) and, optionally, the namespace in which it resides.
   *
   * @return string
   */
  protected function getActionPattern (): string {
    return '/^(?:(?:[A-Z][a-z]*)+\\\\)*(?:[A-Z][a-z]*)+$/';
  }

  /**
   * setPrivate
   *
   * Sets the private property.
   *
   * @param bool $private
   *
   * @return void
   */
  public function setPrivate (bool $private): void {
    $this->private = $private;
  }

  /**
   * setActionParameter
   *
   * Sets the action parameter property.
   *
   * @param array $parameter
   *
   * @return void
   */
  public function setActionParameter (array $parameter): void {
    $this->actionParameter = $parameter;
  }

  /**
   * matchRoute
   *
   * Returns true if this Route matches the one passed here as the
   * parameter to this method.
   *
   * @param RouteInterface $route
   *
   * @return bool
   * @throws RouteException
   */
  public function matchRoute (RouteInterface $route): bool {
    return $this->getRouteData() === $route->getRouteData();
  }

  /**
   * getRouteData
   *
   * Returns the full set of route information based on the values from
   * our properties.
   *
   * @param array $order
   *
   * @return array
   * @throws RouteException
   */
  public function getRouteData (array $order = ["method", "path", "action", "private"]): array {

    // the only viable values in our $order argument are the names of
    // our properties.  if we get anything else, we'll throw an exception.
    // we intentionally leave off the actionParameter because it's not
    // required and it's different from request to request.  if someone
    // wants to use it, they can change the default $order array.

    $valid = get_object_vars($this);
    $difference = array_diff($order, $valid);
    if (($count = sizeof($difference)) > 0) {
      $noun = $count === 1 ? "property" : "properties";
      $difference = join(", ", $difference);
      throw new RouteException("Request for unknown $noun: $difference.");
    }

    $properties = [];
    foreach ($order as $property) {

      // since this method is also used in the __toString method below,
      // we want everything to be printable.  any property that's a bool
      // wouldn't always get printed, so we'll explicitly shift them to
      // strings here.

      $properties[] = is_bool($this->{$property})
        ? ($this->{$property} ? '1' : '0')
        : $this->{$property};
    }

    return $properties;
  }

  /**
   * __toString
   *
   * Returns a string build from our route properties for display
   * purposes.
   *
   * @return string
   * @throws RouteException
   */
  public function __toString (): string {
    return vsprintf("%s (%s, %s, %s)", $this->getRouteData([
      "path", "action", "method", "private",
    ]));
  }

}
