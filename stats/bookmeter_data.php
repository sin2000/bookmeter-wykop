<?php

require_once 'utils/stats_utils.php';

$edition = $_GET["edition"] ?? null;
$statu = new stats_utils($edition);
$filepath = $statu->get_bookmeter_csv_filepath();

if($filepath == '')
  exit;

$data_arr = [];
if(($handle = fopen($filepath, 'r')) !== FALSE)
{
  $first_row = true;
  while(($data = fgetcsv($handle)) !== FALSE)
  {
    if($first_row === false)
    {
      $data[7] = str_replace(',', '.', $data[7]);
      array_push($data_arr, $data);
    }
    else
      $first_row = false;
  }
  fclose($handle);
}

$data = json_encode($data_arr, JSON_NUMERIC_CHECK);

header('Content-Type: application/json; charset=UTF-8');
echo $data

?>