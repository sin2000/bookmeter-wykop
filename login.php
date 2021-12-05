<?php

require_once 'utils/app_auth.php';
require_once 'utils/site_globals.php';
require_once 'utils/error_log_file.php';

session_start();

$app = new app_auth;
$app->redirect_to_index_if_logged();

$auth_id = $_GET["id"] ?? null;
if(strlen($auth_id) > 100)
  $auth_id = null;

$connect_data = $_GET["connectData"] ?? null;
if(strlen($connect_data) > 512)
  $connect_data = null;

$connect_data = json_decode(base64_decode($connect_data));
$has_connect_data = !empty($connect_data->appkey) && !empty($connect_data->login)
  && !empty($connect_data->token) && !empty($connect_data->sign) && !empty($auth_id);

$wapi = new wykop_api;
if($has_connect_data && $connect_data->appkey == $wapi->get_appkey() && $app->has_generated_auth_id($auth_id))
{
  $app->remove_auth_id_from_session();
  $sign_calc = md5($wapi->get_appsecret() . $connect_data->appkey . $connect_data->login . $connect_data->token);
  if(hash_equals($sign_calc, $connect_data->sign))
  {
    $app->set_auth_cookies($connect_data->login, $connect_data->token);
    $app->set_auth_to_session($connect_data->login, $connect_data->token);
    $app->redirect($app->get_current_base_url());
  }
}

error_log_file::append('login.php error');
$app->remove_auth_id_from_session();
$base_url = $app->get_current_base_url();

?>

<!doctype html>
<html lang="pl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="./css/bootstrap.min.css?v=2" />
  <link rel="stylesheet" href="./css/main.css" />
  <title>bookmeter</title>
</head>

<body>
  <div id="main_content" class="container mt-4">

    <h2 class="mb-4"><a href="https://www.wykop.pl/tag/bookmeter" target="_blank">Wykop / #Bookmeter</a></h2>

    <div class="alert alert-danger" role="alert">
      Wystąpił błąd logowania. Spróbuj ponownie: <a href="<?php echo $base_url; ?>">logowanie</a>
    </div>

    <footer class="mt-4 mb-4">
      <div class="container text-center">
        <span class="text-muted"><?php echo site_globals::$footer_info ?></span>
      </div>
    </footer>

  </div>

  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="./js/jquery-3.5.1.min.js"></script>
  <script src="./js/bootstrap.bundle.min.js?v=2"></script>
</body>

</html>