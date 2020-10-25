<?php 

require_once 'utils/wykop_api.php';

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

if(strlen($term) < 3 || preg_match('/^[a-zA-Z0-9]*$/', $term) == false)
{
  response([]);
}

$ret_vals = [];
$api = new wykop_api;
$suggests = $api->tag_suggest($term);
$suggests = $suggests['data'] ?? null;

if(is_array($suggests))
{
  $ret_vals = array_map(function($obj) {
    $tagname = $obj['tag'] ?? null;
    return ltrim($tagname, '#');
  }, $suggests);
}

response($ret_vals);

?>