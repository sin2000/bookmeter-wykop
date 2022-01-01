<?php

class bm_edition
{
  public $id;
  public $start_date;
  public $end_date;
  public $undetected_fn;
  public $db_file;
  public $csv_file; //unused in recent editions

  public function __construct($edition_id, $edition_start_dt, $edition_end_dt,
    $undetected_filepath, $db_filepath, $csv_filename)
  {
    $this->id = $edition_id;
    $this->start_date = $edition_start_dt;
    $this->end_date = $edition_end_dt;
    $this->undetected_fn = $undetected_filepath;
    $this->db_file = $db_filepath;
    $this->csv_file = $csv_filename;
  }
}

?>