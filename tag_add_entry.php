<?php

require_once 'utils/csrf.php';
require_once 'utils/wykop_api.php';
require_once 'utils/counter_file.php';
require_once 'utils/app_auth.php';
require_once 'utils/site_globals.php';
use steveclifton\phpcsrftokens\Csrf;

session_start();
if(Csrf::verifyToken('index') == false)
{
  http_response_code(404);
  return;
}

function success_response()
{
  $response = new stdClass;
  $response->errmsg = '';
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($response);
  exit;
}

function error_response($errmsg)
{
  $response = new stdClass;
  $response->errmsg = htmlspecialchars($errmsg);
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($response);
  exit;
}

function error_if_empty($field, $errmsg)
{
  if($field == '')
  {
    error_response($errmsg);
  }
}

function error_if_nonum($field, $int_min, $int_max, $errmsg)
{
  if($field == '' || is_numeric($field) == false || $field < $int_min || $field > $int_max)
    error_response($errmsg);
}

function validate_file($file_data, $file_type)
{
  $file_up_errors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
  );

  if(isset($file_data))
  {
    if($file_data['error'] == UPLOAD_ERR_NO_FILE)
      return '';
    
    if($file_data['error'] != UPLOAD_ERR_OK)
      return $file_up_errors[$file_data['error']];

    $file_size = $file_data['size'];
    if($file_size > (3 * 1024 * 1024))
      return 'maksymalna wielkość pliku to 3 MB';

    if($file_type != 'image/jpeg' && $file_type != 'image/png')
      return 'dozwolone typy plików to JPG i PNG';
  }

  return '';
}

$title_input = trim($_POST['title_input']);
$author_input = trim($_POST['author_input']);
$genre_input = trim($_POST['genre_input']);
$isbn_input = trim($_POST['isbn_input']);
$descr_input = trim($_POST['descr_input']);
$file_input = $_FILES['file_input'];
$file_type = empty($file_input['tmp_name']) ? false : mime_content_type($file_input['tmp_name']);
$image_url_input = trim($_POST['image_url_input']);
$selected_rating_input = $_POST['selected_rating'];
$add_ad_input = $_POST['add_ad_input'] ?? false;

error_if_empty($title_input, 'tytuł jest wymagany');
error_if_empty($author_input, 'autor jest wymagany');
error_if_empty($genre_input, 'gatunek jest wymagany');
error_if_nonum($selected_rating_input, 1, 10, 'ocena jest wymagana w zakresie od 1 do 10');
$val = validate_file($file_input, $file_type);
if($val != '')
  error_response($val);

$app = new app_auth;
$wapi = new wykop_api;
$login_result = $wapi->login_index($app->get_session_login_name(), $app->get_session_token());
if($login_result->errmsg_curl != '')
  error_response($login_result->errmsg_curl);
if(isset($login_result->content['error']['message_pl']) && $login_result->content['error']['message_pl'] != '')
  error_response($login_result->content['error']['message_pl']);

$userkey = $login_result->content['data']['userkey'];
error_if_empty($userkey, 'brak userkey');

$counter = new counter_file;
$predicted_counter = $counter->get_counter_value();
$api_counter = $counter->get_api_counter(site_globals::$tag_name);
if($api_counter > $predicted_counter)
  $predicted_counter = $api_counter;

$predicted_counter_start = $predicted_counter;
$predicted_counter++;
$append_ad = $add_ad_input == 'on' ? true : false;
$ad = "Wpis dodano za pomocą strony: [" . $app->get_current_base_url() . "](" . $app->get_current_base_url() . ")";

$isbn_row = "";
if(empty($isbn_input) == false)
  $isbn_row = "**ISBN:** " . $isbn_input . "\n";

$body = $predicted_counter_start . " + 1 = " . $predicted_counter . "\n\n"
  . "**Tytuł:** " . $title_input . "\n"
  . "**Autor:** " . $author_input . "\n"
  . "**Gatunek:** " . $genre_input . "\n"
  . $isbn_row
  . "**Ocena:** " . $selected_rating_input . "/10\n\n"
  . $descr_input . "\n\n"
  . ($append_ad == true ? ($ad . "\n\n") : "")
  . "#" . site_globals::$tag_name;

$embed = empty($image_url_input) ? null : $image_url_input;
if($file_type != false)
  $embed = new CURLFile($file_input['tmp_name'], $file_type, $file_input['name']);

$add_res = $wapi->add_entry($userkey, $body, $embed);
if($add_res->errmsg_curl != '')
  error_response($add_res->errmsg_curl);
if(isset($add_res->content['error']['message_pl']) && $add_res->content['error']['message_pl'] != '')
  error_response($add_res->content['error']['message_pl']);

$counter->set_counter_value($predicted_counter);

Csrf::removeToken('index');

success_response();

?>