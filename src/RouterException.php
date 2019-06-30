<?php

namespace Dashifen\Router;

use Dashifen\Exception\Exception;

class RouterException extends Exception {
  public const UNEXPECTED_ROUTE = 1;
  public const UNKNOWN_ERROR = 2;
}
