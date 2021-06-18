<?php

require_once 'wykop_api.php';
require_once 'counter_file.php';

class bookmeter_utils
{
  private $last_entry = null;
  private $counter_source = '';

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

    if($counter_in_file >= $api_counter)
    {
      $counter = $counter_in_file;
      $this->counter_source = 'plik';
    }
    else
    {
      $counter = $api_counter;
      $this->counter_source = 'wpis z api';
    }

    return $counter;
  }

  public function get_counter_from_entries($entries, $counter_in_file)
  {
    $counter = 0;
    $numberless_books = 0;
    $last_counter = $counter_in_file;
    if(isset($entries) && is_array($entries))
    {
      foreach ($entries as $entry)
      {
        $this->last_entry = $entry;

        $body = $entry['body'] ?? '';
        $counter_obj = $this->find_counter_in_body($body);
        $counter = $counter_obj['counter'];

        if($counter != 0 && ($counter != $last_counter || $counter_obj['sign'] == '-'))
          break;

        $last_counter = $counter;

        if($this->body_contains_book_entry($body))
          $numberless_books++;
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

  public function get_counter_source()
  {
    $id = $this->last_entry['id'] ?? '';
    $date = $this->last_entry['date'] ?? '';
    $login = $this->last_entry['author']['login'] ?? '';

    $tmp = 'źródło licznika: ' . $this->counter_source;
    $tmp .= '; ostatni spr. wpis - ' . $id . ' ' . $date . ' ' . $login;

    return $tmp;
  }

  private function find_counter_in_body($body)
  {
    $obj = ['sign' => '', 'counter' => 0];

    $matches = array();
    if(preg_match('/^[ ]*\d+[ ]*([\+\-])[ ]*\d+[ ]*=[ ]*(\d+)[ ]*$/m', $body, $matches))
    {
      $obj['sign'] = $matches[1] ?? '';
      $obj['counter'] = $matches[2] ?? -1;
      
      return $obj;
    }

    return $obj;
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