<?php

namespace Multiback\Database\Contracts;

interface Database
{
    /**
     * Attempt to connect to database using connection details
     */
    public function connect(): void;

    /**
     * Run database query
     * 
     * @param string $queryString
     * @return mixed
     */
    public function query(string $queryString);

    /**
     * Run and fetch data from database query
     * 
     * @param string $queryString
     * @return array
     */
    public function fetch(string $queryString): array;

    /**
     * Generate list of tables to backup
     * 
     * @return array
     */
    public function tables(): array;

    /**
     * Run backup
     * 
     * @return void
     */
    public function backup(): void;

}
