<?php 

require_once 'utils/book_info_database.php';
require_once 'utils/app_auth.php';
require_once 'utils/error_log_file.php';

session_start();

function response($arr)
{
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($arr);
  exit;
}

$app = new app_auth;
if($app->has_auth_in_session() == false)
{
  error_log_file::append('book_search.php empty result. Missing auth in session');
  response([]);
}

$term = $_GET['term'] ?? null;

$bookdb = new book_info_database;
$ret_vals = $bookdb->get_matched_titles($term);

response($ret_vals);

?>