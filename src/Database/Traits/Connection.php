<?php

namespace Multiback\Database\Traits;

trait Connection
{
    /**
     * Db driver
     * 
     * @var string
     */
    protected string $driver;

    /**
     * DB host
     * 
     * @var string
     */
    protected string $host;

    /**
     * DB port
     * 
     * @var int
     */
    protected int $port;

    /**
     * DB name
     * 
     * @var string
     */
    protected string $name;

    /**
     * DB user
     * 
     * @var string
     */
    protected string $user;

    /**
     * DB password
     * 
     * @var string
     */
    protected string $pass;

    /**
     * List of connections
     * 
     * @var array
     */
    protected array $connections;

    /**
     * Current PDO connection
     * 
     * @var PDO
     */
    protected $connection;

    public function __construct(array $connections)
    {
        $this->connections = $connections;
        $this->init();
    }

    protected function init(): void
    {
        $this->driver = $this->driver();

        foreach ($this->connections as $connection) {
            $this->parseConnection($connection);
            $this->connect();
            $this->fetch();
        }
    }

    /**
     * Get the driver's name
     * 
     * by default, uses the class name
     */
    protected function driver(): string
    {
        $explode = explode("\\", __CLASS__);
        return $explode[count($explode) - 1];
    }

    /**
     * Parse connection params
     * 
     * @param array $connection
     * @return void
     */
    protected function parseConnection(array $connection)
    {
        $this->host = getenv($connection['host'] ?? '127.0.0.1');
        $this->port = getenv($connection['port']);
        $this->name = getenv($connection['name']);
        $this->user = getenv($connection['user']);
        $this->pass = getenv($connection['pass']);
    }
}
