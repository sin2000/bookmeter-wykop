<?php

require_once 'log_file.php';
require_once 'confidential_vars.php';

class error_log_file
{
  private static $log_f;

  public static function init()
  {
    self::$log_f = new log_file(confidential_vars::$error_log_file);
  }

  public static function append($message)
  {
    self::$log_f->log_append($message);
  }
}

error_log_file::init();

?>