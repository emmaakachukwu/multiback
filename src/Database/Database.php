<?php

namespace Emmaakachukwu\Multiback\Database;

class Database
{
    protected const DRIVER_MYSQL = 'mysql';

    /**
     * Specify database driver
     * 
     * E.g. mysql
     * 
     * @var string
     */
    protected string $driver;

    /**
     * Validated database drivers list
     * 
     * @var array
     */
    protected array $drivers = [];

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {}

    /**
     * Get list of supported DB drivers
     * 
     * @return array
     */
    protected function supportedDrivers()
    {
        $reflectionClass = new \ReflectionClass(__CLASS__);
        $consts = $reflectionClass->getConstants();
        return array_filter($consts, function($const) {
            return strpos($const, 'DRIVER') === 0;
        }, ARRAY_FILTER_USE_KEY);
    }
}
