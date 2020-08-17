<?php

require_once 'utils/app_auth.php';
require_once 'utils/csrf.php';
require_once 'utils/site_globals.php';
use steveclifton\phpcsrftokens\Csrf;

session_start();

$app = new app_auth;
$app->redirect_to_login();
$login_name = htmlspecialchars($app->get_session_login_name());

?>

<!doctype html>
<html lang="pl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="./css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
  <link rel="stylesheet" href="./css/main.css" />
  <title>bookmeter</title>
</head>

<body>
  <div id="spinner_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog spinner_dialog">
      <div id="full_spinner" class="myspinner" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
  </div>

  <div id="success_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Sukces</h5>
        </div>
        <div class="modal-body">
          <p>Pomyślnie dodano wpis na mikroblogu.</p>
        </div>
        <div class="modal-footer">
          <button id="success_modal_ok_btn" type="button" class="btn btn-primary">OK</button>
        </div>
      </div>
    </div>
  </div>

  <div id="main_content" class="container mt-4">

    <div class="form-row mb-2">
      <div class="col-sm">
        <h2><a href="https://www.wykop.pl/tag/bookmeter" target="_blank">Wykop / #Bookmeter</a></h2>
      </div>
      <div class="col-auto align-self-center pr-3">
        Login: <b><?php echo $login_name ?></b>
      </div>
      <div class="col-auto align-self-center">
        <a href="logout.php">Wyloguj</a>
      </div>
    </div>

    <div id="timeout_alert" class="alert alert-danger hide" role="alert">
      Wysłanie danych nie powiodło się - sesja wygasła. <b>Odśwież stronę</b> i spróbuj ponownie.
    </div>

    <div id="srv_err_alert" class="alert alert-warning hide" role="alert">
      Błąd serwera: <span id="srv_err_text"></span>
    </div>

    <form id="add_entry_form" action="tag_add_entry.php" method="POST" class="needs-validation" novalidate>

      <?php echo Csrf::getInputToken('index', 9999) ?>

      <div class="form-row">
        <div class="col-md mb-3">
          <span class="align-middle">Przewidywany licznik:</span>
          <div id="counter_spinner" class="spinner-border spinner-border-sm align-middle" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          <span class="font-weight-bold align-middle" id="book_counter_input"></span>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md mb-3">
          <label for="title_input">Tytuł</label>
          <input id="title_input" type="text" name="title_input" class="form-control" value="" required>
          <div class="invalid-tooltip">
            Tytuł jest wymagany
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md mb-3">
          <label for="author_input">Autor</label>
          <input id="author_input" type="text" name="author_input" class="form-control" required>
          <div class="invalid-tooltip">
            Autor jest wymagany
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md mb-3">
          <label for="genre_input">Gatunek</label>
          <input id="genre_input" type="text" name="genre_input" class="form-control" required>
          <div class="invalid-tooltip">
            Gatunek jest wymagany
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="col-md mb-3">
          <label for="descr_input">Opis</label>
          <textarea id="descr_input" class="form-control" name="descr_input" rows="3"></textarea>
        </div>
      </div>

      <div class="form-row">
        <div class="col-md mb-3">
          <div class="custom-file">
            <input id="file_input" type="file" name="file_input" class="form-control custom-file-input" lang="pl">
            <label class="custom-file-label" for="file_input">Dołącz obrazek z dysku</label>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="col-md mb-3">
          <label for="image_url_input">Url obrazka:</label>
          <input id="image_url_input" type="text" name="image_url_input" class="form-control">
        </div>
      </div>

      <div class="form-row mb-3 align-items-center">
        <div class="col-md">
          <input id="selected_rating" type="text" name="selected_rating" class="form-control input-hidden" value="" required>

          <button type="button" class="btnrating btn btn-default btn-md" data-attr="1" id="rating-star-1">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="2" id="rating-star-2">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="3" id="rating-star-3">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="4" id="rating-star-4">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="5" id="rating-star-5">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="6" id="rating-star-6">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="7" id="rating-star-7">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="8" id="rating-star-8">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="9" id="rating-star-9">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>
          <button type="button" class="btnrating btn btn-default btn-md" data-attr="10" id="rating-star-10">
            <i class="fa fa-star" aria-hidden="true"></i>
          </button>

          <div class="invalid-tooltip">
            Wybierz ocenę
          </div>
        </div>
        <div class="col-md font-weight-bold">
          Ocena: <span class="selected-rating">0</span><small>/10</small>
        </div>
      </div>

      <div class="custom-control custom-checkbox mb-3">
        <input id="add_ad_input" type="checkbox" name="add_ad_input" class="form-control custom-control-input">
        <label class="custom-control-label" for="add_ad_input">Dołącz informację o tej stronie</label>
      </div>

      <button class="btn btn-primary pl-5 pr-5" type="submit">Wyślij</button>
    </form>

    <footer class="mt-4 mb-4">
      <div class="container text-center">
        <span class="text-muted"><?php echo site_globals::$footer_info ?></span>
      </div>
    </footer>

  </div>

  <!-- jQuery first, then Popper.js, then Bootstrap JS -->
  <script src="./js/jquery-3.5.1.min.js"></script>
  <script src="./js/bootstrap.bundle.min.js"></script>
  <script src="./js/star-rating.min.js" type="text/javascript"></script>
  <script src="./js/main.js" type="text/javascript"></script>
</body>

</html>