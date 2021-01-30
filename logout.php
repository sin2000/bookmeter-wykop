<?php

require_once 'utils/app_auth.php';
require_once 'utils/csrf.php';
require_once 'utils/site_globals.php';
use steveclifton\phpcsrftokens\Csrf;

session_start();

$app = new app_auth;
$app->remove_auth_data();
Csrf::removeToken('index');
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

    <div id="timeout_alert" class="alert alert-success" role="alert">
      Wylogowano poprawnie. Zaloguj ponownie: <a href="<?php echo $base_url; ?>">logowanie</a>
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