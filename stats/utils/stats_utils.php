<?php

require_once 'bm_edition.php';

class stats_utils
{
  public const bm_edition_4 = 4;
  public const bm_edition_5 = 5;
  public const bm_actual_edition = self::bm_edition_5;
  private const data_dir = '../data';
  private $editions = null;
  private $bm_current_edition = null;

  public function __construct($edition)
  {    
    $this->bm_current_edition = $edition;
    $this->editions = array(
      self::bm_edition_4 => new bm_edition(self::bm_edition_4, new DateTime('2020-07-29'), new DateTime('2020-12-31 23:59:59')),
      self::bm_edition_5 => new bm_edition(self::bm_edition_5, new DateTime('2021-01-01'), new DateTime('2021-12-31 23:59:59')),
    );
  } 

  public function get_current_edition()
  {
    return $this->bm_current_edition;
  }

  public function get_bookmeter_csv_filepath()
  {
    switch($this->bm_current_edition)
    {
      case self::bm_actual_edition:
        return self::data_dir . '/bookmeter.csv';
      break;
      case self::bm_edition_4:
        return self::data_dir . '/bookmeter_4th_edition.csv';
      break;
    }

    return '';
  }

  public function get_undetected_filepath()
  {
    switch($this->bm_current_edition)
    {
      case self::bm_actual_edition:
        return self::data_dir . '/undetected.txt';
      break;
      case self::bm_edition_4:
        return self::data_dir . '/undetected_4th_edition.txt';
      break;
    }

    return '';
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
}

?>