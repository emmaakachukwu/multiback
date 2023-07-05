<?php

namespace Multiback;

use ValueError;

class Util
{
  static function resolve($val) {
    $matches = [];
    preg_match_all('/\$(\w+)/', $val, $matches);

    foreach ($matches[1] as $match_string) {
      $env_value = getenv($match_string);
      if ($env_value !== false) {
        $val = str_replace("$$match_string", $env_value, $val);
      } else {
        throw new ValueError("No environment value given for $match_string");
      }
    }
    return $val;
  }
}
