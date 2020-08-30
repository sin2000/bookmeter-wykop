<?php

if(!isset($_COOKIE[session_name()]))
{
  echo '-1';
  return;
}

require_once 'utils/counter_file.php';
require_once 'utils/bookmeter_utils.php';
require_once 'utils/site_globals.php';

$counter = new counter_file;
$bm = new bookmeter_utils;

$api_counter = $bm->get_api_counter(site_globals::$tag_name);

$file_counter = $counter->get_counter_value();

$predicted_counter = ($api_counter > $file_counter) ? $api_counter : $file_counter;
$predicted_counter++;

echo $predicted_counter;

?>