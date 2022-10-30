<?php

namespace Multiback;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /**
     * Default config file name (without extension)
     * 
     * @var string
     */
    protected const CONF_FILE_NAME = 'mb_config';

    /**
     * Config string delimiter
     * 
     * @var string
     */
    protected const CONF_DELIMITER = '.';

    /**
     * Get the config file path
     * 
     * gets all files named with the {CONF_FILE_NAME}.y*ml pattern and returns the first one
     * 
     * @return string
     */
    protected static function getConfigFile(): string
    {
        $file = getcwd().DIRECTORY_SEPARATOR.self::CONF_FILE_NAME;
        $files = glob("$file.y*ml");

        return $files[0] ?? '';
    }

    /**
     * Parse the config file content into an array
     * 
     * @return array
     */
    static function parseConfigFile(): array
    {
        try {
            return Yaml::parse(file_get_contents(self::getConfigFile()));
        } catch (ParseException $e) {
            echo "Unable to parse the YAML string: {$e->getMessage()}";
            exit(E_ERROR);
        }
    }

    /**
     * Get a config
     * 
     * returns an array of all configs if no config param is passed
     * 
     * @param string|null $config
     * @return string|array|null
     */
    static function get(?string $config = null)
    {
        $configs = self::parseConfigFile();
        $config = trim($config);
        if (! $config) return $configs;

        $keys = explode(self::CONF_DELIMITER, $config);
        $value = $configs;
        foreach ($keys as $key) {
            if (! is_array($value) || ! array_key_exists($key, $value)) return null;
            $value = $value[$key];
        }

        return $value;
    }
}
