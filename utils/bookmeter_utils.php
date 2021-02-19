<?php

require_once 'wykop_api.php';
require_once 'counter_file.php';

class bookmeter_utils
{
  public function get_counter($tag_name)
  {
    $cf = new counter_file;
    $counter_in_file = $cf->get_counter_value();
    
    $wapi = new wykop_api();
    $jdata = $wapi->tag_entries($tag_name);

    $wdata = $jdata['data'] ?? null;
    
    // TODO: make this better
    //$api_counter = -1;
    // $curr_date = new DateTime();
    // $new_edition_min_date = new DateTime('2021-01-01');
    // $new_edition_max_date = new DateTime('2021-01-06');
    // if($curr_date >= $new_edition_min_date && $curr_date <= $new_edition_max_date)
    // {
    //   $max_counter = 300;
    //   if($counter_in_file > $max_counter)
    //     $counter_in_file = 0;

    //   $api_counter = $this->get_counter_from_entries($wdata, $counter_in_file);
    //   if($api_counter > $max_counter)
    //     $api_counter = 0;
    // }
    // else
    //{
      //$api_counter = $this->get_counter_from_entries($wdata, $counter_in_file);
    //}

    $api_counter = $this->get_counter_from_entries($wdata, $counter_in_file);

    $counter = $counter_in_file > $api_counter ? $counter_in_file : $api_counter;

    return $counter;
  }

  public function get_counter_from_entries($entries, $counter_in_file)
  {
    $counter = -1;
    $numberless_books = 0;
    if(isset($entries) && is_array($entries))
    {
      foreach ($entries as $entry)
      {
        $body = $entry['body'] ?? '';
        $counter = $this->find_counter_in_body($body);
        if($counter != -1 && $counter >= $counter_in_file)
        {
          break;
        }
        else
        {
          if($this->body_contains_book_entry($body))
            $numberless_books++;
        }
      }
    }

    $counter += $numberless_books;

    return $counter;
  }

  public function set_counter($counter)
  {
    $cf = new counter_file;
    $cf->set_counter_value($counter);
  }

  private function find_counter_in_body($body)
  {
    $matches = array();
    if(preg_match('/^[ ]*\d+[ ]*[\+\-][ ]*\d+[ ]*=[ ]*(\d+)[ ]*$/m', $body, $matches))
    {
      $counter = $matches[1] ?? -1;
      return $counter;
    }

    return -1;
  }

  private function body_contains_book_entry($body)
  {
    $matches = array();
    if(preg_match('/^Tytuł[ ]*:/mi', $body, $matches) && preg_match('/^Gatunek[ ]*:/mi', $body, $matches))
      return true;

    return false;
  }
}

?>