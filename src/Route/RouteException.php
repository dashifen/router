<?php

namespace Dashifen\Router\Route;

use Throwable;

class RouteException extends \Exception {
	public const UNEXPECTED_METHOD = 1;
	public const INVALID_PATH = 2;
	public const INVALID_ACTION = 3;
	public const UNKNOWN_PROPERTY = 4;
	public const UNKNOWN_ERROR = 5;
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		$reflection = new \ReflectionClass($this);
		if (!in_array($code, $reflection->getConstants())) {
			$code = self::UNKNOWN_ERROR;
		}
		
		parent::__construct($message, $code, $previous);
	}
}
