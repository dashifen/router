<?php

namespace Dashifen\Router\Route\Factory;

use Throwable;

class RouterFactoryException extends \Exception {
	public const INVALID_ORDER = 1;
	public const UNKNOWN_ERROR = 2;
	
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		$reflection = new \ReflectionClass($this);
		if (!in_array($code, $reflection->getConstants())) {
			$code = self::UNKNOWN_ERROR;
		}
		
		parent::__construct($message, $code, $previous);
	}
}
