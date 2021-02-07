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

$field = $_GET['field'] ?? null;
$term = $_GET['term'] ?? null;

$min_len_config = [ 'genre' => 2, 'authors' => 3, 'title' => 3 ];
$min_len = $min_len_config[$field];
if($min_len == null)
  response([]);

$term = trim($term);
$term_len = mb_strlen($term);
if($term_len < $min_len || $term_len > 3000)
  response([]);

$bm_db = new bm_database();
$ret_vals = [];
switch($field)
{
  case 'genre':
    $ret_vals = $bm_db->get_first_genres($term);
  break;
  case 'authors':
    $ret_vals = $bm_db->get_first_authors($term);
  break;
  case 'title':
    $ret_vals = $bm_db->get_first_titles($term);
  break;
}

response($ret_vals);

?>