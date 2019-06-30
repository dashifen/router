<?php

namespace Dashifen\Router\Route\Collection;


use Dashifen\Exception\Exception;

class  RouteCollectionException extends Exception {
	public const UNEXPECTED_ROUTE = 1;
	public const DUPLICATE_ROUTE = 2;
	public const INVALID_PATTERN = 3;
}
