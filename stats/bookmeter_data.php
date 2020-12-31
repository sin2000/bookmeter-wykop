<?php

$data_arr = [];
if(($handle = fopen('../data/bookmeter.csv', 'r')) !== FALSE)
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