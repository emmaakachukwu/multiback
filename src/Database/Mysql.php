<?php

namespace Multiback\Database;

use Multiback\Database\Traits\Connection;
use Multiback\Exception\DatabaseException;
use Multiback\Database\Contracts\Database;
use PDO;
use PDOException;

class Mysql implements Database
{
  use Connection;

  public function __construct(
    string $name,
    string $user,
    string $pass = '',
    string $host = 'localhost',
    int $port = 3306,
  )
  {
    $this->name = $name;
    $this->user = $user;
    $this->pass = $pass;
    $this->host = $host;
    $this->port = $port;

    $this->connect();
  }

  public function connect(): void
  {
    try {
      $this->connection = new PDO(
        "mysql:host=$this->host;dbname=$this->name;port=$this->port",
        $this->user,
        $this->pass
      );
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      throw new DatabaseException("Connection to $this->name DB failed: {$e->getMessage()}");
    }
  }

  protected function export()
  {}
}
