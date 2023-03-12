<?php

namespace Multiback;

use Multiback\Database\Database;
use Multiback\Uploader\Uploader;

class Multiback
{
    const DEFAULT_ROOT_BACKUP_DIR = '/tmp/multiback/';

    /**
     * Should backup database
     * 
     * @var bool
     */
    protected bool $backupDb;

    /**
     * Should backup files and directories specified in all .gitignore files
     * 
     * @var bool
     */
    protected bool $backupGitIgnoredList;

    /**
     * List of files and directories to backup
     * 
     * @var array
     */
    protected array $backupList;

    /**
     * List of files and directories to ignore during backup
     * 
     * @var array
     */
    protected array $ignoredList;

    public function __construct()
    {
        $this->init();
    }
    
    protected function init()
    {
        mbkLog('starting multi backup');

        if ($dbDrivers = mbkConfig('database.drivers')) {
            mbkLog('databases...');
            new Database($dbDrivers);

            if (($file_storage = mbkConfig('file_storage')) && $file_storage['upload_backups'] ?? false) {
                mbkLog('uploading backups...');
                new Uploader($file_storage);
            }
        }
    }
}
