<?php

require_once 'log_file.php';
require_once 'confidential_vars.php';

class user_log_file
{
  private static $log_f;

  public static function init()
  {
    self::$log_f = new log_file(confidential_vars::$user_log_file);
  }

  public static function append($message)
  {
    self::$log_f->log_append($message);
  }
}

user_log_file::init();

?>