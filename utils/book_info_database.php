<?php

require_once 'confidential_vars.php';
require_once 'debug_log_file.php';

class book_info_database
{
  private const min_filter_length = 2;
  private const max_filter_length = 300;
  private const book_cache_key = 'last_book_cache';
  private const title_cache_key = 'last_title_cache';

  /** @var SQLite3 **/
  private $db;
  /** @var SQLite3Result **/
  private $tmpres = null;

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
      SELECT authors, title, genre, isbn, translator, publisher, image_url FROM book_info
      WHERE ltitle = LOWER(?)
      ORDER BY isbn DESC, authors
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
    $filter = $this->sanitize_title($filter);

    $filter = str_replace('"', '""', $filter);
    $arr = explode(' ', $filter);
    $filter = implode('" "', $arr);
    $filter = '"' . $filter . '"*';

    return $filter;
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
        return $sess_val['item'];
    }

    return false;
  }

  private function set_cached_item($cache_key, $filter, $item)
  {
    $_SESSION[$cache_key] = [ 'filter' => $filter, 'item' => $item ];
  }
}

?>
