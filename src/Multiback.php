<?php

namespace Emmaakachukwu\Multiback;

class Multiback
{
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
    {}
}
