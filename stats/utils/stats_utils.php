<?php

require_once 'bm_edition.php';
require_once __DIR__ . '/../../utils/app_auth.php';

class stats_utils
{
  public const bm_edition_4 = 4;
  public const bm_edition_5 = 5;
  public const bm_edition_6 = 6;
  public const bm_actual_edition = self::bm_edition_6;
  private const data_dir_name = 'data';
  private const data_dir = __DIR__ . '/../../' . self::data_dir_name;
  private $data_url;
  private $editions = null;
  private $bm_current_edition = null;
  private $setting_ingored_logins_key = 'setting_ignored_logins';

  public function __construct($edition)
  {
    $this->data_url = (new app_auth())->get_current_base_url() . self::data_dir_name;

    $this->bm_current_edition = $edition;
    $this->editions = array(
      self::bm_edition_4 => new bm_edition(self::bm_edition_4, new DateTime('2020-07-29'), new DateTime('2020-12-31 23:59:59'),
        'undetected_4th_edition.txt', '', 'bookmeter_4th_edition.csv'),

      self::bm_edition_5 => new bm_edition(self::bm_edition_5, new DateTime('2021-01-01'), new DateTime('2021-12-31 23:59:59'),
        'niewykryte_edycja5.txt', confidential_vars::bm_ed5_db_filepath, ''),

      self::bm_edition_6 => new bm_edition(self::bm_edition_6, new DateTime('2022-01-01'), new DateTime('2022-12-31 23:59:59'),
        'edycja6/undetected.txt', confidential_vars::bm_ed6_db_filepath, ''),
    );
  } 

  public function get_current_edition()
  {
    return $this->bm_current_edition;
  }

  public function get_bookmeter_csv_filepath()
  {    
    return self::data_dir . '/' . $this->editions[$this->bm_current_edition]->csv_file;
  }

  public function get_bookmeter_csv_url()
  {    
    return $this->data_url . '/' . $this->editions[$this->bm_current_edition]->csv_file;
  }

  public function get_undetected_file_url()
  {
    return $this->data_url . '/' . $this->editions[$this->bm_current_edition]->undetected_fn;
  }

  public function get_bm_db_filepath()
  {
    return $this->editions[$this->bm_current_edition]->db_file;
  }

  public function get_edition_start_date($as_string = false)
  {
    $edition_dt = $this->editions[$this->bm_current_edition]->start_date;
    
    return ($as_string ?  $edition_dt->format('Y-m-d') : $edition_dt);
  }

  public function get_edition_end_date($as_string = false)
  {
    $edition_dt = $this->editions[$this->bm_current_edition]->end_date;
    
    return ($as_string ?  $edition_dt->format('Y-m-d') : $edition_dt);
  }

  public function get_bm_data_update_time()
  {
    $end_time = '';
    $datafile_time = filemtime($this->get_bookmeter_csv_filepath($this->bm_current_edition));
    if($datafile_time !== false)
    {
        $dt = new DateTime();
        $dt->setTimestamp($datafile_time);
        $dt->sub(new DateInterval('P1D'));

        if($dt < $this->get_edition_start_date())
        {
          $dt->setTimestamp($datafile_time);
          $end_time = $dt->format('Y-m-d H:i:s');
        }
        else
        {
          $end_time = $dt->format('Y-m-d') . ' 23:59:59';
        }
    }

    return $end_time;
  }

  public function get_ignored_logins()
  {
    if(empty($_COOKIE[$this->setting_ingored_logins_key]) == false)
    {
      $ign_logins = trim($_COOKIE[$this->setting_ingored_logins_key]);
      if($this->validate_ignored_logins($ign_logins) != '')
        $ign_logins = '';

      return $ign_logins;
    }

    return '';
  }

  private function validate_ignored_logins($ignored_logins)
  {
    if(empty($ignored_logins))
      return '';

    if(strlen($ignored_logins) > 2000)
      return 'ignorowane loginy mogą zawierać maksymalnie 2000 znaków';

    if(preg_match('/^[a-zA-Z0-9 _-]*$/', $ignored_logins) == false)
      return 'nieprawidłowe loginy. Dozwolone są tylko znaki alfanumeryczne, odstęp/spacja oraz znaki _ i -';

    return '';
  }
}

?>