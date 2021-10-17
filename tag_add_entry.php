<?php

require_once 'utils/wykop_api.php';
require_once 'utils/bookmeter_utils.php';
require_once 'utils/app_auth.php';
require_once 'utils/site_globals.php';
require_once 'utils/bookmeter_entry.php';
require_once 'utils/user_log_file.php';
require_once 'utils/error_log_file.php';

if(!isset($_COOKIE[session_name()]))
{
  error_log_file::append('tag_add_entry.php 404');

  http_response_code(404);
  return;
}

session_start();

function success_response()
{
  $response = new stdClass;
  $response->errmsg = '';
  header('Content-Type: application/json; charset=UTF-8');
  echo json_encode($response);
  exit;
}

function error_response($errmsg, $origin = '')
{
  if($origin != '')
    $errmsg = '[' . $origin . ']' . $errmsg;

  error_log_file::append('tag_add_entry error_response: ' . $errmsg);

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
$bm_entry->set_genre($_POST['genre_select_input'], $_POST['genre_input']);
$bm_entry->set_isbn($_POST['isbn_input']);
$bm_entry->set_translator($_POST['translator_input']);
$bm_entry->set_publisher($_POST['publisher_input']);
$bm_entry->set_number_of_pages($_POST['number_of_pages_input']);
$bm_entry->set_book_form($_POST['book_form_input']);
$bm_entry->set_description($_POST['descr_input']);
$bm_entry->set_additional_tags($_POST['tags_input']);
$bm_entry->set_img_file($_FILES['file_input']);
$bm_entry->set_img_url($_POST['image_url_input']);
$bm_entry->set_rate($_POST['selected_rating']);
$bm_entry->set_save_additional_tags($_POST['save_tags_input'] ?? null);
$bm_entry->set_bold_labels($_POST['bold_labels_input'] ?? null);
$bm_entry->set_use_star_rating($_POST['use_star_rating_input'] ?? null);
$bm_entry->set_add_ad($_POST['add_ad_input'] ?? null);

$val_error = $bm_entry->validate();
if($val_error != '')
  error_response($val_error);

$app = new app_auth;
$wapi = new wykop_api;

if($app->has_auth_in_session() == false)
  error_response('nieprawidłowy login lub hasło. Wyloguj i zaloguj się ponownie.');

$login_result = $wapi->login_index($app->get_session_login_name(), $app->get_session_token());
if($login_result->errmsg_curl != '')
  error_response($login_result->errmsg_curl, 'wykopapi');
if(isset($login_result->content['error']['message_pl']) && $login_result->content['error']['message_pl'] != '')
{
  $bad_login_or_passwd_error_code = 14;
  if(isset($login_result->content['error']['code']) && $login_result->content['error']['code'] == $bad_login_or_passwd_error_code)
    error_response($login_result->content['error']['message_pl'] . ' (Wyloguj i zaloguj się ponownie)', 'wykopapi');
  else
    error_response($login_result->content['error']['message_pl'], 'wykopapi');
}

$userkey = $login_result->content['data']['userkey'];
error_if_empty($userkey, 'brak userkey');

$bmu = new bookmeter_utils;
$predicted_counter = $bmu->get_counter(site_globals::$tag_name) + 1;
$body = $bm_entry->compose_msg($predicted_counter);
$embed = $bm_entry->get_img();

$add_res = $wapi->add_entry($userkey, $body, $embed);
if($add_res->errmsg_curl != '')
  error_response($add_res->errmsg_curl, 'wykopapi');
if(isset($add_res->content['error']['message_pl']) && $add_res->content['error']['message_pl'] != '')
  error_response($add_res->content['error']['message_pl'], 'wykopapi');

$bmu->set_counter($predicted_counter);

user_log_file::append($app->get_session_login_name() . ' licznik: ' . $predicted_counter . ' ' . $bmu->get_counter_source());

$bm_entry->save_settings();

success_response();

?>