<?php

class counter_file
{
  private $counter_filepath = 'counter.txt';

  public function get_counter_value()
  {
    $file_counter = -1;
    $handle = fopen($this->counter_filepath, 'r');
    if(flock($handle, LOCK_SH))
    {
      $read_res = fread($handle, filesize($this->counter_filepath));
      $read_res = trim($read_res);
      if(is_numeric($read_res))
        $file_counter = $read_res;
      flock($handle, LOCK_UN);
    }
    fclose($handle);

    return $file_counter;
  }

  public function set_counter_value($val)
  {
    $handle = fopen($this->counter_filepath, 'w');
    if(flock($handle, LOCK_EX))
    {
      fwrite($handle, $val);
      flock($handle, LOCK_UN);
    }
    fclose($handle);
  }

  public function increase_counter_value()
  {
    $curr_counter = $this->get_counter_value();
    $curr_counter++;

    $this->set_counter_value($curr_counter);
  }
}
