<?php

namespace Multiback\Uploader;

use Multiback\Exception\FileException;
use Multiback\Exception\ValidationException;
use Multiback\Uploader\S3;
use Multiback\Util;

class Uploader
{
  protected string $backupDir;

  protected array $driverData;

  protected $client;

  protected array $requiredDataKeys = ['driver'];

  public function __construct(string $backupDir, array $data)
  {
    $this->backupDir = $backupDir;

    $this->validate($data);
    $this->setup($data);
  }

  public function upload(): void
  {
    $this->client->upload();
  }

  protected function setup(array $data): void
  {
    switch ($data['driver']) {
      case 's3':
        $this->client = new S3(
          $this->backupDir,
          $data['bucket'] ?? null,
          Util::resolve($data['auth']['access_key_id'] ?? null),
          Util::resolve($data['auth']['secret_access_key'] ?? null),
          $data['region'] ?? null,
          $data['version'] ?? null,
        );
        break;

      default:
        throw new ValidationException("Invalid upload driver: {$data['driver']}");
    }
  }

  protected function validate(array $data): bool
  {
    if (! is_dir($this->backupDir)) {
      throw new FileException("Directory not found: $this->backupDir");
    }

    $diff = array_diff($this->requiredDataKeys, array_keys($data));
    if (! empty($diff)) {
      throw new ValidationException(
        sprintf('Missing upload requirement: %s;', implode(', ', $diff))
      );
    }

    return true;
  }
}
