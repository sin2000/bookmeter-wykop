<?php

if(!isset($_COOKIE[session_name()]))
{
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