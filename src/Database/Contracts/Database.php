<?php

namespace Multiback\Database\Contracts;

interface Database
{
    /**
     * Attempt to connect to database using connection details
     */
    public function connect(): void;

    /**
     * Get data from database
     * 
     * @param string $queryString
     * @return array
     */
    public function query(string $queryString): array;
}