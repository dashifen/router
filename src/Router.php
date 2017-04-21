<?php

namespace Dashifen\Router;

use Dashifen\Request\RequestInterface;
use Dashifen\Router\Route\Collection\RouteCollectionInterface;
use Dashifen\Router\Route\Factory\RouteFactoryInterface;
use Dashifen\Router\Route\RouteInterface;

class Router implements RouterInterface {
	/**
	 * @var RouteCollectionInterface $collection
	 */
	protected $collection;
	
	/**
	 * @var string $method
	 */
	protected $method;
	
	/**
	 * @var string $path
	 */
	protected $path;
	
	/** @var RouteFactoryInterface $factory */
	protected $factory;
	
	public function __construct(
		RequestInterface $request,
		RouteCollectionInterface $collection,
		RouteFactoryInterface $factory,
		array $routes = []
	) {
		$this->method = $request->getServerVar("REQUEST_METHOD");
		$this->path = $request->getServerVar("REQUEST_URI");
		$this->collection = $collection;
		$this->factory = $factory;
		$this->setRoutes($routes);
	}
	
	public function getRoutes(): array {
		return $this->collection->getRoutes();
	}
	
	public function setRoutes(array $routes): void {
		foreach ($routes as $route) {
			$this->setRoute($route);
		}
	}
	
	public function getRoute(): ?RouteInterface {
		
		// the guts of our router is actually here.  when constructed, we
		// use the $request object to get the URI and method for this request.
		// with those, we can see if our collection has the route for this
		// page and, if so, return it.  if it does not, we return null.
		
		$route = null;
		
		if ($this->collection->hasRoute($this->method, $this->path)) {
			$route = $this->collection->getRoute($this->method, $this->path);
		}
		
		return $route;
	}
	
	public function setRoute(array $route): void {
		$this->collection->addRoute($this->factory->produceRoute($route));
	}
}
