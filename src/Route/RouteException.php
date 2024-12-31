<?php

namespace Dashifen\Router\Route;

use Dashifen\Exception\Exception;

class RouteException extends Exception {
  public const int UNKNOWN_METHOD       = 1;
  public const int UNKNOWN_PATH         = 2;
  public const int UNKNOWN_ACTION       = 3;
  public const int UNKNOWN_PROPERTY     = 4;
}
