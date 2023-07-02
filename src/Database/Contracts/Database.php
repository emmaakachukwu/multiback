<?php

namespace Multiback\Database\Contracts;

interface Database
{
    /**
     * Attempt to connect to database using connection details
     */
    public function connect(): void;
}
