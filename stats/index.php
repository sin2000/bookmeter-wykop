<?php

require_once '../utils/app_auth.php';;

$app = new app_auth();
$app->redirect($app->get_current_base_url() . 'tabela/edycja5/index.php');

?>