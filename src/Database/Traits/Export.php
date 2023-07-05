<?php

namespace Multiback\Database\Traits;

trait Export
{
  protected string $backupFile;

  protected bool $compressed;

  protected array $include;

  protected array $exclude;

  protected array $truncate;
}
