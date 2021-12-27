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
      SELECT COUNT(*) FROM bm_entry
    SQL;

    $res = $this->db->querySingle($sql);

    return $res;
  }

  public function get_login_count()
  {
    $sql = <<<SQL
      SELECT COUNT(*) FROM login
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
      SELECT l.name, COUNT(*) FROM bm_entry AS b
      LEFT JOIN login AS l ON b.login_id = l.id
      GROUP BY b.login_id
      ORDER BY COUNT(*) DESC, l.name COLLATE NOCASE
      LIMIT 20
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

  public function get_top_voted_users()
  {
    $sql = <<<SQL
      SELECT l.name, SUM(vote_count) FROM bm_entry AS b
      LEFT JOIN login AS l ON b.login_id = l.id
      GROUP BY b.login_id
      ORDER BY SUM(vote_count) DESC, l.name COLLATE NOCASE
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

  public function get_last_joined_users()
  {
    $sql = <<<SQL
      SELECT l.name, strftime('%Y-%m-%d %H:%M', MIN(date), 'unixepoch', 'localtime') FROM bm_entry AS b
      LEFT JOIN login AS l ON b.login_id = l.id
      GROUP BY b.login_id
      ORDER BY MIN(date) DESC, l.name COLLATE NOCASE
      LIMIT 7
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
      SELECT authors, title, AVG(rate), SUM(vote_count), entry_id FROM bm_entry
      GROUP BY LOWER(authors), LOWER(title)
      HAVING AVG(rate) > 5
      ORDER BY AVG(rate) DESC, SUM(vote_count) DESC, LOWER(authors), LOWER(title)
      LIMIT 20
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1], $row[2], $row[3], $row[4] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_worst_books()
  {
    $sql = <<<SQL
      SELECT authors, title, AVG(rate), SUM(vote_count), entry_id FROM bm_entry
      GROUP BY LOWER(authors), LOWER(title)
      HAVING AVG(rate) < 5
      ORDER BY AVG(rate), SUM(vote_count), LOWER(authors), LOWER(title)
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1], $row[2], $row[3], $row[4] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_top_voted_books()
  {
    $sql = <<<SQL
      SELECT authors, title, SUM(vote_count) FROM bm_entry
      GROUP BY LOWER(authors), LOWER(title)
      ORDER BY SUM(vote_count) DESC, LOWER(authors), LOWER(title)
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
      SELECT authors, COUNT(*) FROM bm_entry
      GROUP BY LOWER(authors)
      ORDER BY COUNT(*) DESC, authors
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
  
    public function get_top_popular_books()
  {
    $sql = <<<SQL
      SELECT authors, title, COUNT(*), AVG(rate), SUM(vote_count), entry_id FROM bm_entry
      GROUP BY LOWER(authors), LOWER(title)
      HAVING COUNT(*) > 1
      ORDER BY COUNT(*) DESC, AVG(rate) DESC, SUM(vote_count) DESC, LOWER(authors), LOWER(title)
      LIMIT 10
    SQL;

    $res = $this->db->query($sql);
    $arr = [];
    while($row = $res->fetchArray(SQLITE3_NUM))
    {
      array_push($arr, [ $row[0], $row[1], $row[2], $row[3], $row[4], $row[5] ]);
    }
    $res->finalize();

    return $arr;
  }

  public function get_top_genres()
  {
    $sql = <<<SQL
      SELECT * FROM (
        WITH cat AS (
          SELECT LOWER(g.name) lname, COUNT(*) cnt
          FROM bm_entry AS b
          LEFT JOIN genre AS g ON b.genre_id = g.id
          GROUP BY b.genre_id
        )
        SELECT LOWER(g.name), 
        (
          SELECT SUM(cat.cnt) FROM cat WHERE INSTR(cat.lname, LOWER(g.name))
        ) ccc
        FROM bm_entry AS b
        LEFT JOIN genre AS g ON b.genre_id = g.id
        GROUP BY b.genre_id
        ORDER BY COUNT(*) DESC, g.name
        LIMIT 10
      )
      ORDER BY ccc DESC
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
    $distinct = $use_distinct ? "GROUP BY LOWER({$field_name})" : '';

    $sql = <<<SQL
      SELECT $field_name FROM $table
      WHERE INSTR(LOWER($field_name), ?)>0
      $distinct
      ORDER BY LOWER($field_name)
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
    $this->db = new SQLite3(confidential_vars::stats_db_filepath, SQLITE3_OPEN_READONLY);
    if($this->db->loadExtension(confidential_vars::sqlite_ext_filename) == false)
    {
      $this->db->createCollation('POLISH', 'bm_database::mycollation');
      $this->db->createFunction('LOWER', 'bm_database::mylower', 1, SQLITE3_DETERMINISTIC);
    }
    $this->db->busyTimeout(15000);
  }

  public static function mycollation($val1, $val2)
  {
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
