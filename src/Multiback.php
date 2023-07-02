<?php

namespace Multiback;

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Multiback\Exception\ParseException;
use Multiback\Exception\FileException;
use Multiback\Exception\ValidationException;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;

class Multiback
{
  /**
   * @var array $validActions list of valid actions to execute
   * @todo Replace with enums in PHP8
   */
  protected array $validActions = ['export', 'upload'];

  protected array $actions;

  protected array $sources;

  /**
   * @param string $configFile path to yaml file with defined configs
   * @param string $backupDir path to backup directory; default is /tmp/multiback
   * @param array $actions list of actions to execute; default is [export, upload]
   * @param bool $postCleanup cleanup backup directory contents after process completes; default is false
   * @param bool $verbose toggle verbose mode
   */
  public function __construct(
    string $configFile,
    array $actions = [],
    string $backupDir = '/tmp/multiback',
    bool $postCleanup = false,
    bool $verbose = false,
  )
  {
    $this->actions = $actions;

    $this->validateActions($actions);
    $config = $this->getConfig($configFile);
    $this->sourceConfigs($config);
  }

  /**
   * Get config data from config file
   */
  protected function getConfig(string $configFile): array
  {
    $config = $this->parseConfigFile($configFile);
    $this->validateConfig($config);
    return $config;
  }

  /**
   * @return bool true if all values of $action are valid
   * @throws ValidationException if not valid
   * @todo Replace with enums in PHP8
   */
  protected function validateActions(array $actions): bool
  {
    $action_diff = array_diff($actions, $this->validActions);
    if (! empty($action_diff)) {
      throw new ValidationException(
        sprintf(
          'Invalid action: %s; allowed actions: %s',
          implode(', ', $action_diff),
          implode(', ', $this->validActions),
        )
      );
    }
    return true;
  }

  /**
   * @throws FileException if $configFile not a file or is not found
   * @throws ParseException if $configFile content is not valid
   */
  protected function parseConfigFile(string $configFile): array
  {
    if (! is_file($configFile)) {
      throw new FileException("$configFile not found or not a regular file");
    }

    try {
      return Yaml::parse(file_get_contents($configFile));
    } catch (YamlParseException $e) {
      throw new ParseException("Unable to parse the YAML string: {$e->getMessage()}");
    }
  }

  /**
   * @return bool true if config is valid
   * @throws ValidationException if not valid
   */
  protected function validateConfig(array $config): bool
  {
    $action_diff = array_diff(array_keys($config), $this->actions);
    if (! empty($action_diff)) {
      throw new ValidationException(
        sprintf(
          'Invalid config key: %s; config keys should match with actions: %s',
          implode(', ', $action_diff),
          implode(', ', $this->actions),
        )
      );
    }
    return true;
  }

  protected function sourceConfigs(array $config): void
  {
    if (! in_array('export', $this->actions)) {
      return;
    }
    foreach ($config['export'] as $type => $data) {
      $this->sources[] = new Source($type, $data);
    }
  }
}
