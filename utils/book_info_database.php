<?php

require_once 'confidential_vars.php';
require_once 'debug_log_file.php';

class book_info_database
{
  private const min_filter_length = 2;
  private const max_filter_length = 300;
  private const max_cache_time_secs = 3 * 60 * 60; // 3h
  private const book_cache_key = 'last_book_cache';
  private const title_cache_key = 'last_title_cache';

  /** @var SQLite3 **/
  private $db;

  public function __construct()
  {
    $this->open();
  }

  function __destruct()
  {
    $this->db->close();
  }

  public function get_matched_titles($filter, $n = 12)
  {
    $flen = mb_strlen($filter);
    if($flen > book_info_database::max_filter_length || $flen < book_info_database::min_filter_length)
      return [];

    $orgfilter = $filter;
    $item = $this->get_cached_item(book_info_database::title_cache_key, $orgfilter);
    if($item !== false)
      return $item;

    $filter = $this->sanitize_title($filter);
    $filter = $this->trim_nonalfanum($filter);
    $flen = mb_strlen($filter);
    if($flen < book_info_database::min_filter_length)
      return [];

    $filter = $this->sanitize_filter($filter);

    $sql = <<<SQL
      SELECT DISTINCT title FROM fts WHERE title MATCH ? ORDER BY rank LIMIT ?;
    SQL;

    $start = microtime(true);
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(1, $filter);
    $stmt->bindValue(2, $n);
    $result = $stmt->execute();
    $arr = [];
    while($row = $result->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, $row[0]);
    }
    $result->finalize();
    $time_elapsed_secs = round(microtime(true) - $start, 4);
    debug_log_file::append(__METHOD__, 'db elapsed: ' . $time_elapsed_secs . 's query: '. $filter);

    $this->set_cached_item(book_info_database::title_cache_key, $orgfilter, $arr);

    return $arr;
  }

  public function get_book_details($title, $n = 100)
  {
    $tlen = mb_strlen($title);
    if($tlen > book_info_database::max_filter_length || $tlen < 1)
      return [];

    $orgfilter = $title;
    $item = $this->get_cached_item(book_info_database::book_cache_key, $orgfilter);
    if($item !== false)
      return $item;

    $title = $this->sanitize_title($title);

    $sql = <<<SQL
      SELECT authors, title, genre, isbn, translator, publisher, image_url, pages, form FROM book_info
      WHERE ltitle = LOWER(?)
      ORDER BY authors NULLS LAST, isbn DESC
      LIMIT ?
    SQL;

    $start = microtime(true);
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(1, $title);
    $stmt->bindValue(2, $n);
    $result = $stmt->execute();
    $arr = [];
    while($row = $result->fetchArray(SQLITE3_ASSOC))
    {
      array_push($arr, $row);
    }
    $result->finalize();
    $time_elapsed_secs = round(microtime(true) - $start, 4);
    debug_log_file::append(__METHOD__, 'db elapsed: ' . $time_elapsed_secs . 's query: '. $title);

    $this->set_cached_item(book_info_database::book_cache_key, $orgfilter, $arr);

    return $arr;
  }

  private function open()
  {
    $this->db = new SQLite3(confidential_vars::book_info_db_filepath, SQLITE3_OPEN_READONLY);
    $this->db->loadExtension(confidential_vars::sqlite_ext_filename); 
    $this->db->busyTimeout(15000);
  }

  private function sanitize_title($title)
  {
    $title = $this->strip_unsafe_chars($title);
    $title = $this->replace_tabs($title);
    $title = trim($title);

    return $title;
  }

  private function sanitize_filter($filter)
  {
    $filter = str_replace('"', '""', $filter);
    $arr = explode(' ', $filter);
    $filter = implode('" "', $arr);
    $filter = '"' . $filter . '"*';

    return $filter;
  }

  private function trim_nonalfanum($text)
  {
    $arr = mb_str_split($text, 1, 'UTF-8');
    $arr_len = count($arr);
    if($arr_len <= 1)
      return $text;

    $start = 0;
    for($i = 0; $i < $arr_len; ++$i)
    {
      if(IntlChar::isalnum($arr[$i]))
      {
        $start = $i;
        break;
      }
    }

    $end = 0;
    for($i = $arr_len - 1; $i >= 0; --$i)
    {
      if(IntlChar::isalnum($arr[$i]))
      {
        $end = $i + 1;
        break;
      }
    }

    $text = mb_substr($text, $start, $end - $start, "UTF-8");

    return $text;
  }

  private function strip_unsafe_chars($str)
  {
    return preg_replace('/[\x00-\x08\x0A-\x1F\x7F-\x9F]/u', '', $str);
  }

  private function replace_tabs($source, $replacement = ' ')
  {
    return str_replace("\t", $replacement, $source);
  }

  private function get_cached_item($cache_key, $filter)
  {
    if(empty($_SESSION[$cache_key]) == false)
    {
      $sess_val = $_SESSION[$cache_key];
      if($sess_val['filter'] === $filter)
      {
        $time_diff = time() - ($sess_val['create_time'] ?? 0);
        if($time_diff > book_info_database::max_cache_time_secs)
        {
          debug_log_file::append(__METHOD__, 'invalidate: ' . $filter);
          unset($_SESSION[$cache_key]);
          return false;
        }

        debug_log_file::append(__METHOD__, 'hit: ' . $filter);
        return $sess_val['item'];
      }
    }

    return false;
  }

  private function set_cached_item($cache_key, $filter, $item)
  {
    $_SESSION[$cache_key] = [ 'filter' => $filter, 'item' => $item, 'create_time' => time() ];
  }
}

?>
