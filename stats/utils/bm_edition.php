<?php

class bm_edition
{
  public $id;
  public $start_date;
  public $end_date;

  public function __construct($edition_id, $edition_start_dt, $edition_end_dt)
  {
    $this->id = $edition_id;
    $this->start_date = $edition_start_dt;
    $this->end_date = $edition_end_dt;
  }
}

?>