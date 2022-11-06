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
     * Root backup directory
     * 
     * @var string
     */
    protected string $rootBackupDir;

    /**
     * Backup directory
     * 
     * @var string
     */
    protected string $backupDir;

    /**
     * Create DB query
     * 
     * @var bool
     */
    protected bool $createDbQuery;

    /**
     * List of tables to backup
     * 
     * @var array
     */
    protected array $tablesToBackup;

    /**
     * List of tables to ignore
     * 
     * @var array
     */
    protected array $tablesToIgnore;

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
        $this->rootBackupDir = $this->getRootBackupDir();

        foreach ($this->connections as $connection) {
            $this->parseConnection($connection);
            mbkLog("\t$this->driver/$this->name database backup starting..");
            $this->createAndSetBackupDir();
            $this->connect();
            $this->backup();
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
        return strtolower($explode[count($explode) - 1]);
    }

    /**
     * Parse connection params
     * 
     * @param array $connection
     * @return void
     */
    protected function parseConnection(array $connection)
    {
        $auth = $connection['auth'];
        $this->host = getenv($auth['host'] ?? '127.0.0.1');
        $this->port = getenv($auth['port']);
        $this->name = getenv($auth['name']);
        $this->user = getenv($auth['user']);
        $this->pass = getenv($auth['pass']);

        $tables = $connection['tables'];
        $this->tablesToBackup = $tables['backup'] ?? [];
        $this->tablesToIgnore = $tables['ignore'] ?? [];

        $this->createDbQuery = $connection['create_db_query'] ?? false;
    }

    protected function getRootBackupDir(): string
    {
        return rtrim(mbkConfig('root_backup_dir', '/tmp/multiback'), '/');
    }

    protected function createAndSetBackupDir(): void
    {
        $this->backupDir = "$this->rootBackupDir/$this->driver/$this->name/";
        if (! is_dir($this->backupDir))
            mkdir($this->backupDir, 0755, true);
    }

}
