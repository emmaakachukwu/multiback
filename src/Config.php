<?php

namespace Emmaakachukwu\Multiback;

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
}
