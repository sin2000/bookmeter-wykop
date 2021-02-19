<?php

require_once 'confidential_vars.php';

class bm_database
{
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

    $data = $this->db->querySingle($sql, true);
    $fmtdt = ['', ''];
    $dt = new Datetime();
    if($data['last_update'] != null)
    {
      $dt->setTimestamp($data['last_update']);
      $fmtdt[0] = $dt->format('Y-m-d H:i');
    }
    if($data['last_full_update'] != null)
    {
      $dt->setTimestamp($data['last_full_update']);
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

  public function get_book_count()
  {
    $sql = <<<SQL
      SELECT COUNT(id) FROM bm_entry
    SQL;

    $res = $this->db->querySingle($sql);

    return $res;
  }

  public function get_login_count()
  {
    $sql = <<<SQL
      SELECT COUNT(id) FROM login
    SQL;

    $res = $this->db->querySingle($sql, false);

    return $res;
  }

  public function get_count_by_sex()
  {
    $sql = <<<SQL
      SELECT
        IFNULL(SUM(CASE WHEN sex = 0 THEN 1 ELSE 0 END), 0) AS unk,
        IFNULL(SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END), 0) AS fem,
        IFNULL(SUM(CASE WHEN sex = 2 THEN 1 ELSE 0 END), 0) AS mal
      FROM login
    SQL;

    $arr = $this->db->querySingle($sql, true);
    
    // $arr['unk'] - unknown, $arr['fem'] - female $arr['mal'] - male
    return $arr;
  }

  public function get_book_count_by_sex()
  {
    $sql = <<<SQL
      SELECT
        IFNULL(SUM(CASE WHEN sex = 0 THEN 1 ELSE 0 END), 0) AS unk,
        IFNULL(SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END), 0) AS fem,
        IFNULL(SUM(CASE WHEN sex = 2 THEN 1 ELSE 0 END), 0) AS mal
      FROM bm_entry AS b
      LEFT JOIN login AS l ON b.login_id = l.id
     SQL;

     $arr = $this->db->querySingle($sql, true);

     // $arr['unk'] - unknown, $arr['fem'] - female $arr['mal'] - male
    return $arr;
  }

  public function get_top_users()
  {
    $sql = <<<SQL
      SELECT l.name, COUNT(b.login_id) FROM bm_entry AS b
      LEFT JOIN login AS l ON b.login_id = l.id
      GROUP BY b.login_id
      ORDER BY COUNT(b.login_id) DESC, l.name COLLATE NOCASE
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_top_books()
  {
    $sql = <<<SQL
      SELECT authors, title, SUM(rate)/COUNT(id), SUM(vote_count) FROM bm_entry
      GROUP BY authors, title
      HAVING (SUM(rate)/COUNT(id)) > 5
      ORDER BY (SUM(rate)/COUNT(id)) DESC, SUM(vote_count) DESC, authors, title
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1], $row[2], $row[3] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_worst_books()
  {
    $sql = <<<SQL
      SELECT authors, title, SUM(rate)/COUNT(id), SUM(vote_count) FROM bm_entry
      GROUP BY authors, title
      HAVING (SUM(rate)/COUNT(id)) < 5
      ORDER BY (SUM(rate)/COUNT(id)), SUM(vote_count), authors, title
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1], $row[2], $row[3] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_top_voted_books()
  {
    $sql = <<<SQL
      SELECT authors, title, SUM(vote_count) FROM bm_entry
      GROUP BY authors, title
      ORDER BY SUM(vote_count) DESC, authors, title
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1], $row[2] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_top_authors()
  {
    $sql = <<<SQL
      SELECT authors, COUNT(id) FROM bm_entry
      GROUP BY authors
      ORDER BY COUNT(id) DESC, authors
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_top_genres()
  {
    $sql = <<<SQL
      SELECT g.name, COUNT(b.genre_id) FROM bm_entry AS b
      LEFT JOIN genre AS g ON b.genre_id = g.id
      GROUP BY b.genre_id
      ORDER BY COUNT(b.genre_id) DESC, g.name
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1] ]);
    }
    $res->finalize();

    return $arr;
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
    $result->finalize();

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