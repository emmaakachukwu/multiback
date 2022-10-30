<?php

namespace Multiback\Database;

use Multiback\Database\Contracts\Database;
use Multiback\Database\Traits\Connection;
use PDO;
use PDOException;

class Mysql implements Database
{
    use Connection;

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

    public function query(string $queryString): array
    {
        $rows = [];
        try {
            $result = $this->connection->query($queryString, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            mbkLog("$this->driver DB: $this->name query error; error: {$e->getMessage()}");
            return [];
        }
        foreach ($result as $row)
            $rows[] = $row;

        return $rows;
    }

    public function fetch()
    {}

    public function tables()
    {}

}
