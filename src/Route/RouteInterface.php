<?php

namespace Dashifen\Router\Route;

/**
 * Interface RouteInterface
 *
 * @package Dashifen\Router\Route
 */
interface RouteInterface {
  /**
   * setMethod
   *
   * Sets the method property.  Note:  setters aren't usually a part of
   * an interface, but because the auto-routing functionality of our Router
   * utilizes this one and the ones that follow, we'll specify them here so
   * that implementations of this interface are guaranteed to have them.
   *
   * @param string $method
   *
   * @return void
   */
  public function setMethod(string $method): void;

  /**
   * setPath
   *
   * Sets the path property.
   *
   * @param string $path
   *
   * @return void
   */
  public function setPath(string $path): void;

  /**
   * setAction
   *
   * Sets the action property.
   *
   * @param string $action
   *
   * @return void
   */
  public function setAction(string $action): void;

  /**
   * setPrivate
   *
   * Sets the private property.
   *
   * @param bool $private
   *
   * @return void
   */
  public function setPrivate(bool $private): void;

  /**
   * setActionParameter
   *
   * Sets the action parameter property.
   *
   * @param array $actionParameter
   *
   * @return void
   */
  public function setActionParameter(array $actionParameter): void;

  /**
	 * @param array $order
	 *
	 * @throws RouteException
	 * @return array;
	 */
	public function getRouteData(array $order = ["method","path","action","private"]): array;
	
	/**
	 * @param RouteInterface $route
	 *
	 * @return bool
	 */
	public function matchRoute(RouteInterface $route): bool;
}
