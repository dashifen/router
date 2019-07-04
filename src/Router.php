<?php

/** @noinspection PhpUnusedParameterInspection */

namespace Dashifen\Router;

use Dashifen\Request\RequestInterface;
use Dashifen\Router\Route\RouteInterface;
use Dashifen\Repository\RepositoryException;
use Dashifen\Router\Route\Factory\RouteFactoryInterface;
use Dashifen\Router\Route\Factory\RouterFactoryException;
use Dashifen\Router\Route\Collection\RouteCollectionException;
use Dashifen\Router\Route\Collection\RouteCollectionInterface;

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

  /**
   * @var RouteFactoryInterface $factory
   */
  protected $factory;

  /**
   * @var @var RouteInterface
   */
  protected $route;

  /**
   * @var bool
   */
  protected $dirty = false;

  /**
   * @var bool
   */
  protected $autoRouter = false;

  /**
   * Router constructor.
   *
   * @param RequestInterface         $request
   * @param RouteCollectionInterface $collection
   * @param RouteFactoryInterface    $factory
   * @param array                    $routes
   *
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   * @throws RouterException
   */
  public function __construct (
    RequestInterface $request,
    RouteCollectionInterface $collection,
    RouteFactoryInterface $factory,
    array $routes = []
  ) {
    $this->method = $request->getServerVar("REQUEST_METHOD");
    $this->path = $request->getServerVar("REQUEST_URI");
    $this->collection = $collection;
    $this->factory = $factory;

    $this->addRoutes($routes);
  }

  /**
   * isAutoRouter
   *
   * An "auto-router" automatically constructs the RouteInterface based
   * on the current request.  There's no need to add routes to its collection,
   * it'll figure it all out on its own!
   *
   * @param bool|null $autoRouterState
   *
   * @return bool
   */
  public function isAutoRouter (?bool $autoRouterState = null): bool {
    if (!is_null($autoRouterState)) {

      // if our state isn't null, then we set our property.  thus, passing
      // a true here makes this an auto-router.

      $this->autoRouter = $autoRouterState;
    }

    // if we received a null, we still want to return the current, unchanged
    // state of our auto-router property.

    return $this->autoRouter;
  }


  /**
   * getRoute
   *
   * Returns the route for the current request.  If this isn't an auto-router,
   * we use the collection to do so.  Otherwise, we construct the route based
   * on the request.
   *
   * @return RouteInterface
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterException
   */
  public function getRoute (): RouteInterface {
    return !$this->autoRouter
      ? $this->getCollectedRoute()
      : $this->getAutoRoute();
  }

  /**
   * getCollectedRoute
   *
   * Returns a route based on our collected routes.
   *
   * @return RouteInterface
   * @throws RouteCollectionException
   * @throws RouterException
   */
  protected function getCollectedRoute (): RouteInterface {

    // the guts of our router is actually here.  when constructed, we
    // use the $request object to get the URI and method for this request.
    // with those, we can see if our collection has the route for this
    // page and, if so, return it.  if it does not, we return null.

    if ($this->dirty) {
      $this->route = null;

      // we search through the collection of routes looking for one that
      // response to this method and path.  if we find it, we set the route
      // property to it.

      if ($this->collection->hasRoute($this->method, $this->path)) {
        $this->route = $this->collection->getRoute($this->method, $this->path);
      }

      // regardless of whether or not we found a route, because we've done a
      // search, we can set the dirty flag to false.  only if we add more
      // routes to our collection will we have to search again.

      $this->dirty = false;
    }

    if (is_null($this->route)) {
      throw new RouterException("Unexpected route: $this->method;$this->path.",
        RouterException::UNEXPECTED_ROUTE
      );
    }

    return $this->route;
  }

  /**
   * getAutoRoute
   *
   * Returns a route that's constructed based on the current request.
   *
   * @return RouteInterface
   * @throws RepositoryException
   */
  protected function getAutoRoute (): RouteInterface {

    // the auto-routing capability uses information in our current
    // request to construct a RouteInterface object on the fly and
    // returns it.  we assume that whatever environment is using this
    // object in that capacity will define the right Actions, etc. so
    // that everything works out in the end.

    $route = $this->factory->produceBlankRoute();
    $route = $this->setRouteActionAndParameter($route);


    // now, we've set our route's action and action parameter, we need to
    // handle the path, method, and privacy.  the first two are easy; we can
    // simply pass our own properties over to it.  privacy is harder, so
    // we'll call the method below to allow apps using this router to over-
    // ride our default and do something more useful.

    $route->setPath($this->path);
    $route->setMethod($this->method);
    $route->setPrivate($this->isRoutePrivate($route));

    return $route;
  }

  /**
   * setRouteActionAndParameter
   *
   * Sets the action and, optionally, the action parameter of our route
   * parameter and returns it back to the calling scope.
   *
   * @param RouteInterface $route
   *
   * @return RouteInterface
   */
  protected function setRouteActionAndParameter (RouteInterface $route): RouteInterface {

    // we split our path based on the forward slashes between its parts.
    // then, we add our method onto the front of it.  then, we need to filter
    // it into two different sets of parts:  the numeric ones and the non-
    // numeric ones.  the former become our action parameter, the latter we
    // transform so that they become our action.

    $actionParts = $this->getActionParts();
    $actionParameters = array_filter($actionParts, function (string $chunk): bool {
      return is_numeric($chunk);
    });

    // now we have the set of action parts that are numeric.  we could do
    // another filter to get the non-numeric ones, but we can also just use
    // array_diff().  we'll hope that avoiding the callback and filtering
    // loop saves us some time and we can always benchmark it later if we
    // want to.

    $action = array_diff($actionParts, $actionParameters);
    $route->setAction($this->transformAction($action));
    $route->setActionParameter($actionParameters);
    return $route;
  }

  /**
   * getActionParts
   *
   * Returns an array of the method followed by the parts of our path so
   * that we can use them to identify our Action.
   *
   * @return array
   */
  protected function getActionParts (): array {

    // we'll split our path using the forward slash that separates the URL
    // of our request.  then, we filter out the blanks.  we add the method
    // onto the front of that array and that gives us the parts that we
    // need to work with.

    $parts = array_filter(explode("/", $this->path));
    array_unshift($parts, $this->method);

    // before we return, we want to see if there's only one value in the
    // array.  if so, that means we're at the root of this domain.  in that
    // case, we add "index" to our array so that our action will be either
    // GetIndex or, perhaps, PostIndex.

    if (sizeof($parts) === 1) {
      $parts[] = "index";
    }

    return $parts;
  }

  /**
   * transformAction
   *
   * Given an array of actions parts (e.g. [foo, bar, baz]), returns
   * the name of our action in StudlyCaps (e.g. FooBarBaz).
   *
   * @param array $actionParts
   *
   * @return string
   */
  protected function transformAction (array $actionParts): string {
    // we walk our array using the method below.  once we're done with that,
    // to return a string instead of array, we join the transformed array by
    // empty strings.


    array_walk($actionParts, function (string &$part): void {

      // we want to capitalize $part so that it's ready to be joined into a
      // StudlyCaps action name.  but, we also want to allow a hyphenated
      // string, like foo-bar.  in this case, we transform it into FooBar.
      // this, unfortunately, calls for preg_replace_callback() which means
      // we're putting a callback inside this callback so we can callback
      // while we're calling back.

      $part = ucfirst(preg_replace_callback("/-([a-z])/", function ($matches) {

        // this matches any character preceded by a hyphen.  we want to return
        // the capitalized version of the that character which is then used to
        // replace the lower case version that we matched.

        return strtoupper($matches[1]);
      }, strtolower($part)));
    });

    return join("", $actionParts);
  }

  /**
   * isRoutePrivate
   *
   * By default, we just return false.  But, we assume that apps that use
   * this router will override this and to produce a more useful version that
   * might distinguish between public and private routes based on the
   * parameter.
   *
   * @param RouteInterface $route
   *
   * @return bool
   */
  protected function isRoutePrivate (RouteInterface $route): bool {
    return false;
  }

  /**
   * getRoutes
   *
   * Returns the routes collected by this Router.
   *
   * @return array
   * @throws RouterException
   */
  public function getRoutes (): array {
    if ($this->autoRouter) {
      throw new RouterException("Auto-routers don't collect routes.",
        RouterException::UNEXPECTED_AUTOROUTER_ACTION);
    }

    return $this->collection->getRoutes();
  }

  /**
   * addRoute
   *
   * Adds a route to this object's collection and sets the dirty flag so we
   * know we have to search through it (again) to find the current route.
   *
   * @param array $route
   *
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   * @throws RouterException
   */
  public function addRoute (array $route): void {
    if ($this->autoRouter) {
      throw new RouterException("Auto-routers don't collect routes.",
        RouterException::UNEXPECTED_AUTOROUTER_ACTION);
    }

    $this->collection->addRoute($this->factory->produceRoute($route));
    $this->dirty = true;
  }

  /**
   * addRoutes
   *
   * Adds an array of routes to this object's collection
   *
   * @param array $routes
   *
   * @throws RepositoryException
   * @throws RouteCollectionException
   * @throws RouterFactoryException
   * @throws RouterException
   */
  public function addRoutes (array $routes): void {
    foreach ($routes as $route) {
      $this->addRoute($route);
    }
  }
}
