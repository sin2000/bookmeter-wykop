<?php 

require_once 'utils/bm_database.php';

function response($arr)
{
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($arr);
  exit;
}

if(!isset($_COOKIE[session_name()]))
{
  response([]);
}

$term = $_GET['term'] ?? null;

if(mb_strlen($term) < 3)
{
  response([]);
}

$bm_db = new bm_database();
$ret_vals = $bm_db->get_first_authors($term);

response($ret_vals);

?>