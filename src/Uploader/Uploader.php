<?php

namespace Multiback\Uploader;

class Uploader
{
    protected const DRIVER_S3 = 's3';

    /**
     * Storage driver
     * 
     * E.g. S3
     * 
     * @var string
     */
    protected string $driver;

    public function __construct(string $storage)
    {
        if (! $this->validateDriver($storage['driver'])) return;
        $this->init();
    }

    protected function init(): void
    {
        //
    }

    /**
     * Get list of supported storage drivers
     * 
     * @return array
     */
    protected static function supportedDrivers(): array
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
    protected function validateDriver(string $driver): bool
    {
        if (in_array($driver, self::supportedDrivers())) {
            $this->driver = $driver;
        } else {
            mbkLog('Invalid storage driver; skipping..');
            return false;
        }
        return true;
    }
}
