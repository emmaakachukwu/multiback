<?php

namespace Multiback\Database\Traits;

trait Connection
{
  protected string $name;

  protected string $user;

  protected string $pass;

  protected string $host;

  protected int $port;

  /**
   * PDO connection object
   * @var PDO
   */
  protected $connection;
}
