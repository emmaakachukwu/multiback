<?php

namespace Multiback\Database;

use Multiback\Exception\ValidationException;

class Database
{
  protected string $driver;

  protected array $data;

  protected $client;

  protected $requiredDataKeys = ['connection'];

  public function __construct(array $data)
  {
    $this->driver = $data['driver'] ?? null;
    $this->data = $data;

    $this->validate();
    $this->setup();
  }

  protected function setup()
  {
    $class = __NAMESPACE__."\\".ucfirst($this->driver);
    $conn = $this->data['connection'];
    $this->client = new $class(
      $conn['name'],
      $conn['user'],
      $conn['pass'],
      $conn['host'],
      (int) $conn['port'],
    );
  }

  protected function validate(): bool
  {
    $this->validateDriver();
    $this->validateData();
    return true;
  }

  protected function validateData(): bool
  {
    $diff = array_diff($this->requiredDataKeys, array_keys($this->data));
    if (! empty($diff)) {
      throw new ValidationException(
        sprintf('Missing database requirement: %s;', implode(', ', $diff))
      );
    }
    return true;
  }

  protected function validateDriver(): bool
  {
    if (!$this->driver || !class_exists(ucfirst($this->driver))) {
      throw new ValidationException("Invalid database driver: $this->driver");
    }
    return true;
  }

  protected function export()
  {
    $this->client->export();
  }
}
