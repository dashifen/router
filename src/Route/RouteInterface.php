<?php

namespace Dashifen\Router\Route;

/**
 * Interface RouteInterface
 *
 * @package Dashifen\Router\Route
 */
interface RouteInterface {
	/**
	 * @return string
	 */
	public function getMethod(): string;
	
	/**
	 * @param string $method
	 *
	 * @throws RouteException
	 * @return void
	 */
	public function setMethod(string $method): void;
	
	/**
	 * @return string
	 */
	public function getPath(): string;
	
	/**
	 * @param string $path
	 *
	 * @throws RouteException
	 * @return void
	 */
	public function setPath(string $path): void;
	
	/**
	 * @return string
	 */
	public function getAction(): string;
	
	/**
	 * @param string $action
	 *
	 * @throws RouteException
	 * @return void
	 */
	public function setAction(string $action): void;
	
	/**
	 * @return bool
	 */
	public function getPrivate(): bool;
	
	/**
	 * @param bool $private
	 *
	 * @return void
	 */
	public function setPrivate(bool $private): void;
	
	/**
	 * @param string $parameter
	 *
	 * @return void
	 */
	public function setActionParameter(string $parameter): void;
	
	/**
	 * @return string
	 */
	public function getActionParameter(): string;
	
	/**
	 * @param array $order
	 *
	 * @throws RouteException
	 * @return array;
	 */
	public function getAll(array $order = ["method","path","action","private"]): array;
	
	/**
	 * @param RouteInterface $route
	 *
	 * @return bool
	 */
	public function matchRoute(RouteInterface $route): bool;
}
