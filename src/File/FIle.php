<?php

namespace Multiback\File;

use Multiback\Exception\ValidationException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ValueError;

class File
{
  protected string $backupDir;

  protected string $parentDir;

  protected array $include;

  protected array $exclude;

  protected array $files = [];

  protected array $requiredDataKeys = ['parent_dir', 'include'];

  public function __construct(array $fileData, string $backupDir)
  {
    $this->validate($fileData);

    $this->backupDir = "$backupDir/files";
    $this->parentDir = rtrim($fileData['parent_dir'], '/');
    $this->include = $fileData['include'];
    $this->exclude = $fileData['exclude'] ?? [];

    $this->setup();
  }

  public function export(): void
  {
    foreach ($this->files as $file) {
      $destination = sprintf('%s/%s', $this->backupDir, substr($file, strlen("$this->parentDir/")));
      $dir = dirname($destination);
      if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        throw new RuntimeException("Error creating directory: $dir");
      }
      if (! @copy($file, $destination)) {
        throw new RuntimeException("Error copying file $file to $destination");
      }
    }
  }

  protected function setup(): void
  {
    $this->processInclude();
    $this->processExclude();
  }

  protected function validate(array $data): bool
  {
    $diff = array_diff($this->requiredDataKeys, array_keys($data));
    if ($diff) {
      throw new ValidationException(
        sprintf('Missing files requirement: %s', implode(', ', $diff))
      );
    }

    return true;
  }

  protected function validatePath(string $path, string $type): bool
  {
    if (! file_exists($path)) {
      throw new ValueError("Invalid $type path: $path");
    }
    if (strpos($path, $this->parentDir) !== 0) {
      throw new ValueError("{ucfirst($type)} path does not match with parent dir: $path; parent dir is $this->parentDir");
    }
    return true;
  }

  protected function processInclude()
  {
    foreach ($this->include as $path) {
      $this->validatePath($path, 'include');
      $this->files = array_unique(array_merge($this->files, $this->getPathStructure($path)));
    }
  }

  protected function processExclude()
  {
    foreach ($this->exclude as $path) {
      $this->validatePath($path, 'exclude');
      $this->files = array_diff($this->files, $this->getPathStructure($path));
    }
  }

  protected function getPathStructure(string $path): array
  {
    if (! file_exists($path)) {
      throw new ValueError("Invalid path: $path");
    }

    if (! is_dir($path)) {
      return [$path];
    }

    $paths = iterator_to_array(
      new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
      ),
      false
    );
    return array_map(function($path) {
      return $path->getPathname();
    }, $paths);
  }

}
