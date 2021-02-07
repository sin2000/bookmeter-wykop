<?php

require_once 'confidential_vars.php';

class bm_database
{
  private $db;
  private $tmpres = null;

  public function __construct()
  {
    $this->open();
  }

  function __destruct()
  {
    if($this->tmpres)
    {
      $this->tmpres->finalize();
      unset($this->tmpres);
    }

    $this->db->close();
  }

  public function get_first_genres($filter, $n = 10)
  {
    return $this->get_first_n('genre', 'name', $filter, false, $n);
  }

  public function get_first_authors($filter, $n = 10)
  {
    return $this->get_first_n('bm_entry', 'authors', $filter, true, $n);
  }

  public function get_first_titles($filter, $n = 10)
  {
    return $this->get_first_n('bm_entry', 'title', $filter, true, $n);
  }

  // returns two datetime in array
  public function fetch_last_update_times()
  {
    $sql = <<<SQL
      SELECT last_update, last_full_update FROM last_operations
    SQL;

    $res = $this->db->querySingle($sql, true);
    $fmtdt = ['', ''];
    $dt = new Datetime();
    if($res['last_update'] != null)
    {
      $dt->setTimestamp($res['last_update']);
      $fmtdt[0] = $dt->format('Y-m-d H:i');
    }
    if($res['last_full_update'] != null)
    {
      $dt->setTimestamp($res['last_full_update']);
      $fmtdt[1] = $dt->format('Y-m-d H:i');
    }

    return $fmtdt;
  }

  public function start_get_bm_view()
  {
    $sql = <<<SQL
      SELECT entry_id, datestr, login_name, sex, authors, title, genre_name, rate, vote_count
      FROM entry_view
      ORDER BY [date] DESC
    SQL;

    $this->tmpres = $this->db->query($sql);
  }

  public function get_next_bm_view_row()
  {
    return $this->tmpres->fetchArray(SQLITE3_NUM);
  }

  private function get_first_n($table, $field_name, $filter, $use_distinct = false, $n = 10)
  {
    if(mb_strlen($filter) > 3000)
      return [];

    $filter = mb_strtolower($filter);
    $distinct = $use_distinct ? 'DISTINCT' : '';

    $sql = <<<SQL
      SELECT $distinct $field_name FROM $table
      WHERE INSTR(LOWER($field_name), ?)>0
      ORDER BY $field_name
      LIMIT ?
    SQL;

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(1, $filter);
    $stmt->bindValue(2, $n);

    $result = $stmt->execute();
    $arr = [];
    while($row = $result->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, $row[0]);
    }

    return $arr;
  }

  private function open()
  {
    setlocale(LC_COLLATE, 'pl_PL.UTF-8');
    $this->db = new SQLite3(confidential_vars::stats_db_filepath, SQLITE3_OPEN_READONLY);
    $this->db->busyTimeout(25000);
    $this->db->createCollation('NOCASE', 'bm_database::mycollation');
    $this->db->createFunction('LOWER', 'bm_database::mylower', 1, SQLITE3_DETERMINISTIC);
  }

  public static function mycollation($val1, $val2)
  {
    $val1 = mb_strtolower($val1);
    $val2 = mb_strtolower($val2);

    return strcoll($val1, $val2);
  }

  public static function mylower($val)
  {
    if ($val !== null)
      return mb_strtolower($val);

    return null;
  }
}

?>