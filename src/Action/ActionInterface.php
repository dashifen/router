<?php

namespace Dashifen\Router\Action;

/**
 * ActionInterface
 *
 * An interface that Route objects use to ensure that the objects they store as
 * Actions meet a certain minimum requirement.
 */
interface ActionInterface
{
  /**
   * Executes the behaviors necessary to follow a Route.
   *
   * @return void
   */
  public function execute(): void;
}
