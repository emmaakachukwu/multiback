<?php

namespace Multiback;

use Multiback\Database\Database;
use Multiback\Exception\ValidationException;

class Source
{
  protected string $type;

  protected array $data;

  protected $client;

  public function __construct(string $type, array $data)
  {
    $this->type = $type;
    $this->data = $data;

    $this->setup($type);
  }

  protected function setup()
  {
    switch ($this->type) {
      case 'databases':
        $this->client = new Database($this->data);
        break;
      
      default:
        throw new ValidationException("Invalid source type: $this->type");
    }
  }

  protected function export()
  {
    $this->client->export();
  }
}
