<?php

namespace Multiback;

use Multiback\Database\Database;
use Multiback\Exception\ValidationException;

class Source
{
  protected string $type;

  protected array $data;

  protected $client;

  protected $backupDir;

  public function __construct(string $type, array $data, string $backupDir)
  {
    $this->type = $type;
    $this->data = $data;
    $this->backupDir = $backupDir;

    $this->setup($type);
  }

  public function export()
  {
    $this->client->export();
  }

  protected function setup()
  {
    switch ($this->type) {
      case 'databases':
        $this->client = new Database($this->data, $this->backupDir);
        break;
      
      default:
        throw new ValidationException("Invalid source type: $this->type");
    }
  }

}
