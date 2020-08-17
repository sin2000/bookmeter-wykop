<?php

require_once 'wykop_api.php';

class counter_file
{
  private $counter_filepath = 'counter.txt';

  function get_counter_value()
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

  function set_counter_value($val)
  {
    $handle = fopen($this->counter_filepath, 'w');
    if(flock($handle, LOCK_EX))
    {
      fwrite($handle, $val);
      flock($handle, LOCK_UN);
    }
    fclose($handle);
  }

  function increase_counter_value()
  {
    $curr_counter = $this->get_counter_value();
    $curr_counter++;

    $this->set_counter_value($curr_counter);
  }

  function get_api_counter($tag_name)
  {
    $wapi = new wykop_api();

    $jdata = $wapi->tag_entries($tag_name);

    $api_counter = -1;
    $matches = array();
    $wdata = $jdata['data'] ?? null;
    if(isset($wdata) && is_array($wdata))
    {
      foreach ($wdata as $entry)
      {
        $body = $entry['body'] ?? '';
        if(preg_match('/=\s*(\d+)/', $body, $matches))
        {
          $api_counter = $matches[1] ?? -1;
          break;
        }
      }
    }

    return $api_counter;
  }
}
