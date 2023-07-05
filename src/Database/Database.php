<?php

namespace Multiback\Database;

use Multiback\Exception\ValidationException;
use Multiback\Util;
use RuntimeException;

class Database
{
  protected array $databases;

  protected string $backupDir;

  protected array $clients = [];

  protected array $requiredDataKeys = ['connection', 'driver'];

  public function __construct(array $databases, string $backupDir)
  {
    $this->databases = $databases;
    $this->backupDir = $backupDir;

    $this->setup();
  }

  protected function setup()
  {
    foreach ($this->databases as $source_db_name => $data) {
      $this->validateData($data);
      $class = $this->getDriverClass($data['driver']);
      $conn = $data['connection'];
      $this->clients[] = new $class(
        Util::resolve($conn['name']),
        Util::resolve($conn['user']),
        Util::resolve($conn['pass']),
        Util::resolve($conn['host'] ?? $class::HOST),
        (int) Util::resolve($conn['port'] ?? $class::PORT),
        $this->getBackupDir($source_db_name, $data['timestamped'] ?? false),
        $data['compressed'] ?? true,
        $data['include'] ?? [],
        $data['exclude'] ?? [],
        $data['truncate'] ?? [],
      );
    }
  }

  protected function getBackupDir(string $source_db_name, bool $append_timestamp): string
  {
    $dir = sprintf(
      '%s/%s%s',
      $this->backupDir,
      $source_db_name,
      $append_timestamp ? '_' . date('d-m-Y_H-i-s') : '',
    );
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
      throw new RuntimeException("Error creating backup directory: $dir");
    }
    return $dir;
  }

  protected function validateData(array $data): bool
  {
    $diff = array_diff($this->requiredDataKeys, array_keys($data));
    if (! empty($diff)) {
      throw new ValidationException(
        sprintf('Missing database requirement: %s;', implode(', ', $diff))
      );
    }
    return true;
  }

  protected function getDriverClass(string $driver): string
  {
    $class = __NAMESPACE__."\\".ucfirst($driver);
    if (strtolower($driver) == 'database' || ! class_exists($class)) {
      throw new ValidationException("Invalid database driver: $driver");
    }
    return $class;
  }

  protected function export()
  {
    foreach ($this->clients as $client) {
      $client->export();
    }
  }
}
