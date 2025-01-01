<?php

namespace Dashifen\Router;

use Dashifen\Exception\Exception;

class RouterException extends Exception
{
  public const int UNEXPECTED_ROUTE             = 1;
  public const int UNEXPECTED_ROUTES            = 2;
  public const int UNEXPECTED_AUTOROUTER_ACTION = 3;
}
