<?php

namespace Dashifen\Router;

use Dashifen\Exception\Exception;

class RouterException extends Exception
{
  public const int UNEXPECTED_ROUTE = 1;
}
