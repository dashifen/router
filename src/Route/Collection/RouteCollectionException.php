<?php

namespace Dashifen\Router\Route\Collection;

use Throwable;

class  RouteCollectionException extends \Exception {
	public const UNEXPECTED_ROUTE = 1;
	public const DUPLICATE_ROUTE = 2;
	public const INVALID_PATTERN = 3;
	public const UNKNOWN_ERROR = 4;
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		$reflection = new \ReflectionClass($this);
		if (!in_array($code, $reflection->getConstants())) {
			$code = self::UNKNOWN_ERROR;
		}
		
		parent::__construct($message, $code, $previous);
	}
	
}
