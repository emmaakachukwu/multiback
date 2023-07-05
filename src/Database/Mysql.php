<?php

namespace Multiback\Database;

use Multiback\Database\Traits\Connection;
use Multiback\Database\Traits\Export;
use Multiback\Database\Contracts\Database;
use Multiback\Exception\DatabaseException;
use PDO;
use PDOException;
use Phar;
use PharData;
use RuntimeException;

class Mysql implements Database
{
  use Connection, Export;

  public const HOST = 'localhost';

  public const COMPRESSION = 'gz';

  protected const FILE_EXT = 'sql';

  protected const PORT = 3306;

  public function __construct(
    string $name,
    string $user,
    string $pass = '',
    string $host = null,
    int $port = null,
    string $backupDir = null,
    bool $compressed = true,
    array $include = [],
    array $exclude = [],
    array $truncate = [],
  )
  {
    $this->name = $name;
    $this->user = $user;
    $this->pass = $pass;
    $this->host = $host ?? self::HOST;
    $this->port = $port ?? self::PORT;
    $this->compressed = $compressed;
    $this->include = array_unique(array_merge($include, $truncate));
    $this->exclude = $exclude;
    $this->truncate = $truncate;
    $this->backupFile = $this->getBackupFile($backupDir);

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

  public function export(): void
  {
    $file = fopen($this->backupFile, 'a');
    fwrite($file, $this->createDbQuery());
    foreach ($this->tables() as $table) {
      fwrite($file, $this->createTableQuery($table));
      if (! in_array($table, $this->truncate)) {
        fwrite($file, $this->insertQuery($table));
      }
    }
    fclose($file);
    if ($this->compressed) {
      $this->compress();
    }
  }

  public function query(string $queryString, int $queryMode = PDO::FETCH_ASSOC)
  {
    try {
      return $this->connection->query($queryString, $queryMode);
    } catch (PDOException $e) {
      throw new DatabaseException("Database query error; query: $queryString, error: {$e->getMessage()}");
    }
  }

  protected function fetch(string $queryString): array
  {
    $result = $this->query($queryString);
    $rows = [];
    foreach ($result as $row)
      $rows[] = $row;
    return $rows;
  }

  protected function getBackupFile(string $backupDir): string
  {
    $file = rtrim($backupDir ?? "./$this->name", '/').self::FILE_EXT;
    if (file_exists($file) && !unlink($file)) {
      throw new RuntimeException("Unable to delete file: $file");
    }
    return $file;
  }

  protected function tables(): array
  {
    $tables = $this->getTablesFromInfoSchema();
    if (! empty($this->include)) {
      $tables = array_intersect($this->include, $tables);
    }
    $tables = array_diff($tables, $this->exclude);
    return $tables;
  }

  protected function getTablesFromInfoSchema(): array
  {
    $alias = '_tables';
    $tables = $this->fetch(
      "SELECT TABLE_NAME $alias
      FROM INFORMATION_SCHEMA.TABLES 
      WHERE TABLE_SCHEMA = '$this->name'"
    );
    return array_map(function ($table) use ($alias) {
      return $table[$alias];
    }, $tables);
  }

  protected function createDbQuery(): string
  {
    return <<<EOF
    CREATE DATABASE IF NOT EXISTS `$this->name`;
    USE `$this->name`;\n
    EOF;
  }

  protected function createTableQuery(string $table): string
  {
    $result = $this->fetch("SHOW CREATE TABLE `$table`");
    if (count($result) != 1 || !array_key_exists('Create Table', $result[0])) {
      throw new DatabaseException("Error getting table schema: $table");
    }
    $sql = sprintf("%s;\n", $result[0]['Create Table']);
    return str_ireplace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $sql);
  }

  protected function insertQuery(string $table): string
  {
    $sql = "INSERT INTO `$table` VALUES\n";
    $rows = $this->query("SELECT * FROM `$table`");
    $tab_space = str_repeat(' ', 2);
    foreach ($rows as $row) {
      $sql .= sprintf('%s(%s)', $tab_space,
        implode(', ',
          array_map(function ($r) {
            return "\"$r\"";
          },
          array_values($row))
        )
      );
    }
    return $sql;
  }

  protected function compress(): void
  {
    $tar_archive = "$this->backupFile.tar";
    $gz_archive = sprintf('%s.%s', $tar_archive, self::COMPRESSION);
    $archive = new PharData($tar_archive);
    $archive->addFile($this->backupFile, basename($this->backupFile));
    @unlink($gz_archive);
    $archive->compress(Phar::GZ);
    foreach ([$tar_archive, $this->backupFile] as $file) {
      @unlink($file);
    }
  }

}
