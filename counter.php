<?php

require_once 'utils/error_log_file.php';

if(!isset($_COOKIE[session_name()]))
{
  error_log_file::append('counter.php returned -1. Missing cookie with session name.');

  echo '-1';
  return;
}

require_once 'utils/bookmeter_utils.php';
require_once 'utils/site_globals.php';

$bmu = new bookmeter_utils;
$predicted_counter = $bmu->get_counter(site_globals::$tag_name);
$predicted_counter++;

echo $predicted_counter;

?>