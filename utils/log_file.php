<?php

class log_file
{
  private $log_filepath;
  private $backup_log_filepath;
  private $max_log_file_size = 1 * 1024 * 1024;

  public function __construct($filepath_wo_ext)
  {
    $ext = ".log";
    $this->log_filepath = $filepath_wo_ext . $ext;
    $this->backup_log_filepath = $filepath_wo_ext . '_1' . $ext;
  }

  public function log_append($line)
  {
    if(filesize($this->log_filepath) >= $this->max_log_file_size)
    {
      rename($this->log_filepath, $this->backup_log_filepath);
    }

    $aline = "[" . date('Ymd H:i:s') . '] ' . $line . "\n";
    file_put_contents($this->log_filepath, $aline, FILE_APPEND | LOCK_EX);
    chmod($this->log_filepath, 0600);
  }
}
