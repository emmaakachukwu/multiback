<?php

namespace Multiback\Database;

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

    public function __construct(array $drivers)
    {
        if (! $this->validateDrivers($drivers)) return;
        $this->init();
    }

    protected function init()
    {
        foreach ($this->drivers as $db => $driver) {
            mbkLog("$db databases backup starting");
            $driverClass = __NAMESPACE__."\\".ucfirst($db);
            new $driverClass($driver['connections']);
        }
    }

    /**
     * Get list of supported DB drivers
     * 
     * @return array
     */
    protected static function supportedDrivers()
    {
        $reflectionClass = new \ReflectionClass(__CLASS__);
        $consts = $reflectionClass->getConstants();
        return array_filter($consts, function($const) {
            return strpos($const, 'DRIVER') === 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Validate drivers
     * 
     * @return bool
     */
    protected function validateDrivers(array $drivers): bool
    {
        foreach (self::supportedDrivers() as $driver)
            if (array_key_exists($driver, $drivers))
                $this->drivers[$driver] = $drivers[$driver];

        if (empty($this->drivers)) {
            mbkLog('Database drivers is empty; skipping..');
            return false;
        }
        return true;
    }
}
