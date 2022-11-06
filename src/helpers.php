<?php

if (! function_exists('mbkLog'))
{
    /**
     * Log to the console with trailing new line char
     * 
     * @param array $values
     * @return void
     */
    function mbkLog(...$values): void
    {
        echo date("d-m-Y H-i-s\t");
        foreach($values as $value) {
            print_r($value);
            print_r(PHP_EOL);
        }
    }
}

if (! function_exists('mbkConfig'))
{
    /**
     * Get a multiback config
     * 
     * returns an array of all configs if no config param is passed
     * 
     * @param string|null $config
     * @param mixed $default
     * @return string|array|null
     */
    function mbkConfig(?string $config = null, $default = null)
    {
        return Multiback\Config::get(...func_get_args());
    }
}

if (! function_exists('dd'))
{
    /**
     * Dump and die
     * 
     * @param mixed $vars
     * @return void
     */
    function dd(...$vars): void
    {
        foreach($vars as $var) {
            print_r($var);
            print_r(PHP_EOL);
        }
        die;
    }
}
