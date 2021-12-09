<?php

require_once 'utils/app_auth.php';

session_start();

$app = new app_auth;
$app->redirect_to_index_if_logged();
$app->redirect_to_login();

?>
