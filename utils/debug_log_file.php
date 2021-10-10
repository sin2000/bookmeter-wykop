<?php

require_once 'log_file.php';
require_once 'confidential_vars.php';

class debug_log_file
{
  private static $log_f;

  public static function init()
  {
    self::$log_f = new log_file(confidential_vars::debug_log_file);
  }

  public static function append($funcion_name, $message)
  {
    self::$log_f->log_append($funcion_name . ' ' . $message);
  }
}

debug_log_file::init();

?>