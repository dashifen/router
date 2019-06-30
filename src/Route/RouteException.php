<?php

namespace Dashifen\Router\Route;

use Dashifen\Exception\Exception;

class RouteException extends Exception {
  public const UNEXPECTED_METHOD = 1;
  public const INVALID_PATH = 2;
  public const INVALID_ACTION = 3;
  public const UNKNOWN_PROPERTY = 4;
}
