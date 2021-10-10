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
  error_log_file::append('book_details.php 404. Missing auth in session');
  http_response_code(404);
  return;
}
$term = $_GET['title'] ?? null;

$bookdb = new book_info_database;
$ret_vals = $bookdb->get_book_details($term);

response($ret_vals);

?>