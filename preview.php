<?php

require_once 'utils/csrf.php';
require_once 'utils/bookmeter_utils.php';
require_once 'utils/site_globals.php';
require_once 'utils/bookmeter_entry.php';
use steveclifton\phpcsrftokens\Csrf;

session_start();
if(Csrf::verifyToken('index') == false)
{
  http_response_code(404);
  return;
}

function success_response($body)
{
  $response = new stdClass;
  $response->errmsg = '';
  $response->body = $body;
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

$bm_entry = new bookmeter_entry;
$bm_entry->set_title($_POST['title_input']);
$bm_entry->set_author($_POST['author_input']);
$bm_entry->set_genre($_POST['genre_select_input'], $_POST['genre_input']);
$bm_entry->set_isbn($_POST['isbn_input']);
$bm_entry->set_description($_POST['descr_input']);
$bm_entry->set_additional_tags($_POST['tags_input']);
$bm_entry->set_rate($_POST['selected_rating']);
$bm_entry->set_save_additional_tags($_POST['save_tags_input'] ?? null);
$bm_entry->set_bold_labels($_POST['bold_labels_input'] ?? null);
$bm_entry->set_use_star_rating($_POST['use_star_rating_input'] ?? null);
$bm_entry->set_add_ad($_POST['add_ad_input'] ?? null);

$val_error = $bm_entry->validate();
if($val_error != '')
  error_response($val_error);

$bmu = new bookmeter_utils;
$predicted_counter = $bmu->get_counter(site_globals::$tag_name) + 1;
$body = $bm_entry->compose_msg($predicted_counter);
$body = htmlspecialchars($body);
$body = nl2br($body);

$bm_entry->save_settings();

success_response($body);

?>