<?php

require_once 'utils/csrf.php';
require_once 'utils/wykop_api.php';
require_once 'utils/bookmeter_utils.php';
require_once 'utils/app_auth.php';
require_once 'utils/site_globals.php';
require_once 'utils/bookmeter_entry.php';
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
    error_response($errmsg);
}

$bm_entry = new bookmeter_entry;
$bm_entry->set_title($_POST['title_input']);
$bm_entry->set_author($_POST['author_input']);
$bm_entry->set_genre($_POST['genre_input']);
$bm_entry->set_isbn($_POST['isbn_input']);
$bm_entry->set_description($_POST['descr_input']);
$bm_entry->set_img_file($_FILES['file_input']);
$bm_entry->set_img_url($_POST['image_url_input']);
$bm_entry->set_rate($_POST['selected_rating']);
$bm_entry->set_use_star_rating($_POST['use_star_rating_input'] ?? null);
$bm_entry->set_add_ad($_POST['add_ad_input'] ?? null);

$val_error = $bm_entry->validate();
if($val_error != '')
  error_response($val_error);

$app = new app_auth;
$wapi = new wykop_api;
$login_result = $wapi->login_index($app->get_session_login_name(), $app->get_session_token());
if($login_result->errmsg_curl != '')
  error_response($login_result->errmsg_curl);
if(isset($login_result->content['error']['message_pl']) && $login_result->content['error']['message_pl'] != '')
  error_response($login_result->content['error']['message_pl']);

$userkey = $login_result->content['data']['userkey'];
error_if_empty($userkey, 'brak userkey');

$bmu = new bookmeter_utils;
$predicted_counter = $bmu->get_counter(site_globals::$tag_name) + 1;
$body = $bm_entry->compose_msg($predicted_counter);
$embed = $bm_entry->get_img();

$add_res = $wapi->add_entry($userkey, $body, $embed);
if($add_res->errmsg_curl != '')
  error_response($add_res->errmsg_curl);
if(isset($add_res->content['error']['message_pl']) && $add_res->content['error']['message_pl'] != '')
  error_response($add_res->content['error']['message_pl']);

$bmu->set_counter($predicted_counter);

Csrf::removeToken('index');

success_response();

?>