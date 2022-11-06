<?php

namespace Multiback\Database;

use Multiback\Database\Contracts\Database;
use Multiback\Database\Traits\Connection;
use PDO;
use PDOException;

class Mysql implements Database
{
    use Connection;

    public const FILE_EXT = 'sql';

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
            mbkLog("Connection to $this->driver DB: $this->name failed; error: {$e->getMessage()}");
        }
    }

    public function query(string $queryString, int $queryMode = PDO::FETCH_ASSOC)
    {
        try {
            return $this->connection->query($queryString, $queryMode);
        } catch (PDOException $e) {
            mbkLog("$this->driver DB: $this->name query error; error: {$e->getMessage()}");
            return false;
        }
    }

    public function fetch(string $queryString): array
    {
        $result = $this->query($queryString);
        $rows = [];

        foreach ($result as $row)
            $rows[] = $row;

        return $rows;
    }

    public function tables(): array
    {
        $alias = '_' . __FUNCTION__;
        $tables = $this->fetch(
            "SELECT TABLE_NAME $alias
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = '$this->name'"
        );
        $tables = array_map(function ($table) use ($alias) {
            return $table[$alias];
        }, $tables);

        if (!empty($this->tablesToBackup))
            $tables = array_intersect($this->tablesToBackup, $tables);
        if (!empty($this->tablesToIgnore))
            $tables = array_diff($tables, $this->tablesToIgnore);

        return $tables;
    }

    public function backup(): void
    {
        $filePath = sprintf(
            "%s_%s-db-multiback-backup_%s.%s",
            $this->backupDir,
            $this->name,
            date('d-m-Y_H-i-s'),
            self::FILE_EXT
        );
        $file = fopen($filePath, 'a');
        $sql = $this->createDbQuery ?
            <<<EOF
            CREATE DATABASE IF NOT EXISTS `$this->name`;
              USE `$this->name`;\n
            EOF:
            '';
        fwrite($file, $sql);
        foreach ($this->tables() as $table) {
            $rows = $this->query("SELECT * FROM `$table`");
            $res = $this->fetch("SHOW CREATE TABLE `$table`");
            $sql = sprintf("\n%s;", $res[0]['Create Table']);
            $sql = str_ireplace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $sql);
            $sql .= "\nINSERT INTO $table VALUES";

            foreach ($rows as $row) {
                $sql .= sprintf("\n\t(%s)", implode(', ', array_map(function ($r) {
                    return "\"$r\"";
                }, array_values($row))));
            }
            
            fwrite($file, $sql);
        }
        fclose($file);
        mbkLog("\t$this->driver/$this->name database backup completed.\nFile: $filePath");
    }

}
