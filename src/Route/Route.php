<?php

namespace Dashifen\Router\Route;

/**
 * Class AbstractRoute
 *
 * @package Dashifen\Router\Route
 */
class Route implements RouteInterface {
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
	protected $actionParameter = "";
	
	/**
	 * AbstractRoute constructor.
	 *
	 * @param string $method
	 * @param string $path
	 * @param string $action
	 * @param bool   $private
	 */
	public function __construct(
		string $method,
		string $path,
		string $action,
		bool $private = false
	) {
		$this->setMethod($method);
		$this->setPath($path);
		$this->setAction($action);
		$this->setPrivate($private);
	}
	
	/**
	 * @return string
	 */
	public function getMethod(): string {
		return $this->method;
	}
	
	/**
	 * @param string $method
	 *
	 * @throws RouteException
	 * @return void
	 */
	public function setMethod(string $method): void {
		
		// since using cURL to perform PATCH and DELETE requests, while
		// possible, is a pain in the ass, we'll simply use GET and POST
		// here.
		
		$method = strtoupper($method);
		if (!in_array($method, ["GET", "POST"])) {
			throw new RouteException("Unexpected method: $method.");
		}
		
		$this->method = $method;
	}
	
	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}
	
	/**
	 * @param string $path
	 *
	 * @throws RouteException
	 * @return void
	 */
	public function setPath(string $path): void {
		
		// paths should be words separated by forward-slashes.  we
		// can check for that as follows.  first, if there's not one
		// in the front of our string, we add it.
		
		if (substr($path, 0, 1) !== '/') {
			$path = '/' . $path;
		}
		
		if (!preg_match('/(?:\/(?:\w+)?)+/', $path)) {
			throw new RouteException("Invalid path: $path.");
		}
		
		$this->path = $path;
	}
	
	/**
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}
	
	/**
	 * @param string $action
	 *
	 * @throws RouteException
	 * @return void
	 */
	public function setAction(string $action): void {
		
		// action should be a fully namespaced class name.  so, it's
		// words separated by back-slashes.  we can check for that here.
		// why do we use three back slashes in the regular expression?
		// see http://stackoverflow.com/a/15369828/360838 (2017-04-30)
		
		if (!preg_match('/(?:\w+\\\)+\w+/', $action)) {
			throw new RouteException("Invalid action: $action");
		}
		
		$this->action = $action;
	}
	
	/**
	 * @return bool
	 */
	public function getPrivate(): bool {
		return $this->private;
	}
	
	/**
	 * @param bool $private
	 */
	public function setPrivate(bool $private): void {
		$this->private = $private;
	}
	
	/**
	 * @param array $parameter
	 *
	 * @return void
	 */
	public function setActionParameter(array $parameter): void {
		$this->actionParameter = $parameter;
	}
	
	/**
	 * @return array
	 */
	public function getActionParameter(): array {
		return $this->actionParameter;
	}
	
	
	/**
	 * @param RouteInterface $route
	 *
	 * @return bool
	 */
	public function matchRoute(RouteInterface $route): bool {
		return $this->getAll() === $route->getAll();
	}
	
	/**
	 * @param array $order
	 *
	 * @throws RouteException
	 * @return array
	 */
	public function getAll(array $order = ["method", "path", "action", "private"]): array {
		
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
	 * @return string
	 */
	public function __toString(): string {
		return vsprintf("%s (%s, %s, %s)", $this->getAll([
			"path", "action", "method", "private",
		]));
	}
	
}
