<?php

require_once 'wykop_api.php';

class bookmeter_utils
{
  public function get_api_counter($tag_name)
  {
    $wapi = new wykop_api();
    $jdata = $wapi->tag_entries($tag_name);

    $wdata = $jdata['data'] ?? null;
    $api_counter = $this->get_counter_from_entries($wdata);

    return $api_counter;
  }

  public function get_counter_from_entries($entries)
  {
    $counter = -1;
    $matches = array();
    if(isset($entries) && is_array($entries))
    {
      foreach ($entries as $entry)
      {
        $body = $entry['body'] ?? '';
        if(preg_match('/^[ ]*\d+[ ]*\+[ ]*\d+[ ]*=[ ]*(\d+)[ ]*$/m', $body, $matches))
        {
          $counter = $matches[1] ?? -1;
          break;
        }
      }
    }

    return $counter;
  }
}

?>