<?php

require_once 'utils/app_auth.php';
require_once 'utils/csrf.php';
require_once 'utils/site_globals.php';
require_once 'utils/bookmeter_entry.php';

use steveclifton\phpcsrftokens\Csrf;

function create_options_tags($values)
{
  $options = '';
  foreach($values as $val)
  {
    $fval = htmlspecialchars($val);
    $options .= '<option value="' . $fval . '">' . $fval . '</option>' . "\n";
  }

  return $options;
}

session_start();

$app = new app_auth;
$app->redirect_to_wykopconnect();

$csrf_token = Csrf::getInputToken('index', 18000); // 5h=18000s; Uses setcookie so this must be before output
$login_name = htmlspecialchars($app->get_session_login_name());
$book_entry = new bookmeter_entry;
$book_entry->load_settings();
$save_tags_check_value = $book_entry->get_save_additional_tags() ? "checked" : "";
$bold_labels_check_value = $book_entry->get_bold_labels() ? "checked" : "";
$star_rating_check_value = $book_entry->get_use_star_rating() ? "checked" : "";
$add_ad_check_value = $book_entry->get_add_ad() ? "checked" : "";
$additional_tags_value = htmlspecialchars($book_entry->get_additional_tags());

$genre_list = [
  'ekonomia',
  'fantasy',
  'historyczna',
  'horror',
  'kryminał',
  'literatura piękna',
  'popularnonaukowa',
  'reportaż',
  'science fiction',
  'thriller',
];
$genre_options = create_options_tags($genre_list);

?>

<!doctype html>
<html lang="pl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="./css/bootstrap.min.css?v=2" />
  <link rel="stylesheet" href="./css/jquery-ui.min.css" />
  <link rel="stylesheet" href="./css/jquery-ui.theme.min.css" />
  <link rel="stylesheet" href="./css/font-awesome.min.css" />
  <link rel="stylesheet" href="./css/main.css?v=3" />
  <title>bookmeter</title>
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
</head>

<body>
  <div id="spinner_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog spinner_dialog">
      <div id="full_spinner" class="myspinner" role="status">
        <span class="sr-only">Ładowanie...</span>
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

  <div id="preview_modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Podgląd wpisu</h5>
        </div>
        <div class="modal-body">
          <div style="line-height: 94%;">
            <span id="preview_content" class="small"></span>
          </div>
        </div>
        <div class="modal-footer">
          <button id="preview_modal_ok_btn" type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
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
      Wysłanie danych nie powiodło się - sesja wygasła. Upewnij się, że masz włączone ciasteczka. <b>Odśwież stronę</b> i spróbuj ponownie.
    </div>

    <div id="srv_err_alert" class="alert alert-warning hide" role="alert">
      Błąd serwera: <span id="srv_err_text"></span>
    </div>

    <div class="form-row">
      <div class="col-md mb-3">
        <span class="align-middle">Przewidywany licznik:</span>
        <div id="counter_spinner" class="spinner-border spinner-border-sm align-middle" role="status">
          <span class="sr-only">Loading...</span>
        </div>
        <span class="font-weight-bold align-middle" id="book_counter_input"></span>
        <span class="align-middle small">(<a href="stats">statystyki</a>)</span>
      </div>
    </div>

    <div id="search_content">

      <form id="search_form" action="#" method="GET" class="needs-validation" novalidate>
        <div class="form-row align-items-center">
          <div class="col mt-3 mb-3 pr-0">
            <input id="search_input" type="text" name="search_input" class="form-control" value="" placeholder="podaj tytuł..." maxlength="300" autocomplete="off" required>
            <div class="invalid-tooltip">
              Tytuł jest wymagany
            </div>
          </div>
          <div class="col-auto mt-3 mb-3">
            <button id="search_button" type="submit" class="btn btn-primary" aria-label="Szukaj całego tytułu" title="Szukaj całego tytułu">
              <i class="fa fa-search" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </form>

      <div id="book_details">
        <div id="book_not_found_div" class="small alert alert-info hide" role="alert">
          <b>Nie znaleziono książki</b>. Podaj minimum 2 znaki tytułu aby uzyskać podpowiedzi.
          Wykorzystaj podpowiedzi tytułu aby móc znaleźć książki do wyboru.<br />
          Tytuł musi być podany w całości.
        </div>

        <button id="book_template" type="button" class="book_item list-group-item list-group-item-action p-2 hide">
          <div class="container pl-0 pr-0">
            <div class="row row-cols-2 align-items-end">
              <div class="col-auto pr-0">
                <img src="" class="book_img d-block border float-left" alt="." width="70" height="100" referrerpolicy="no-referrer">
              </div>
              <div class="col-auto">
                <div class="book_title font-weight-bold"></div>
                <div class="book_author mb-1 small"></div>
                <div class="small font-italic">ISBN: <span class="book_isbn"></span></div>
              </div>
              <div class="col-auto">
                <div class="mt-1 small">Gatunek: <span class="book_genre"></span></div>
                <div class="small">Wydawnictwo: <span class="book_publisher"></span></div>
              </div>
            </div>
          </div>
        </button>
        
        <div id="book_list" class="list-group">
        </div>

      </div>

      <div class="form-row">
        <div class="col-md mt-5 small">
          <button id="show_main_form" class="btn btn-light btn-sm" type="button">Przejdź do edycji bez wyboru książki</button>
        </div>
      </div>

    </div>

    <form id="add_entry_form" action="tag_add_entry.php" method="POST" class="needs-validation d-none" novalidate>

      <?php echo $csrf_token ?>

      <div class="form-row">
        <div class="col-md mb-3">
          <label for="title_input">Tytuł</label>
          <input id="title_input" type="text" name="title_input" class="form-control" value="" autocomplete="off" required>
          <div class="invalid-tooltip">
            Tytuł jest wymagany
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md mb-3">
          <label for="author_input">Autor</label>
          <input id="author_input" type="text" name="author_input" class="form-control" autocomplete="off" required>
          <div class="invalid-tooltip">
            Autor jest wymagany
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md mb-3">
          <label for="genre_select_input">Gatunek</label>
          <select id="genre_select_input" name="genre_select_input" class="custom-select form-control" autocomplete="off" required>
            <option selected disabled value="">wybierz...</option>
            <?php echo $genre_options ?>
            <option value="inny...">inny...</option>
          </select>
          <div class="invalid-tooltip">
            Gatunek jest wymagany
          </div>

          <input id="genre_input" type="text" name="genre_input" class="form-control mt-2 d-none">
          <div class="invalid-tooltip">
            Gatunek jest wymagany
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md mb-3">
          <label for="isbn_input">ISBN</label>
          <input id="isbn_input" type="text" name="isbn_input" autocomplete="off" class="form-control">
          <div class="invalid-tooltip">
            Numer ISBN jest niepoprawny
          </div>
        </div>
      </div>

      <div class="mb-1">
        <button class="btn btn-light btn-sm dropdown-toggle mb-1" type="button" data-toggle="collapse" data-target="#collapse_content" aria-expanded="false" aria-controls="collapse_content">
          Inne pola...
        </button>
        <div class="collapse mb-2" id="collapse_content">
          <div class="card card-body pt-1">
            <div class="form-row">
              <div class="col-md">
                <label for="translator_input" class="col-form-label pb-1">Tłumacz</label>
                <input id="translator_input" type="text" name="translator_input" class="form-control" maxlength="3000">
              </div>
            </div>
            <div class="form-row">
              <div class="col-md">
                <label for="publisher_input" class="col-form-label pb-1">Wydawnictwo</label>
                <input id="publisher_input" type="text" name="publisher_input" class="form-control" maxlength="3000">
              </div>
            </div>
            <div class="form-row">
              <div id="npages_container" class="col-md">
                <label for="number_of_pages_input" class="col-form-label pb-1">Liczba stron</label>
                <input id="number_of_pages_input" type="number" name="number_of_pages_input" class="form-control" min="1" max="9999" step="1" autocomplete="off">
                <div class="invalid-tooltip">
                  Liczba stron musi być w przedziale od 1 do 9999
                </div>
              </div>
            </div>
            <div class="form-row">
              <div class="col-md">
                <label class="col-form-label pb-1">Forma książki</label>
                <div>
                  <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="book_form_input[]" class="custom-control-input book_form" id="bf_book_input" autocomplete="off" value="książka">
                    <label class="custom-control-label" for="bf_book_input">książka</label>
                  </div>
                  <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="book_form_input[]" class="custom-control-input book_form" id="bf_ebook_input" autocomplete="off" value="e-book">
                    <label class="custom-control-label" for="bf_ebook_input">e-book</label>
                  </div>
                  <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="book_form_input[]" class="custom-control-input book_form" id="bf_audiobook_input" autocomplete="off" value="audiobook">
                    <label class="custom-control-label" for="bf_audiobook_input">audiobook</label>
                  </div>
                </div>
                <div class="small text-black-50">* po wybraniu samego audiobooka usuwana jest liczba stron</div>
                <div class="small text-black-50">* po wybraniu więcej niż jednej formy usuwany jest ISBN</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="col-md mb-3">
          <label for="descr_input">Opis</label>
          <textarea id="descr_input" class="form-control mb-1" name="descr_input" rows="3" autocomplete="off" required></textarea>
          <div class="invalid-tooltip">
            Opis jest wymagany
          </div>
          <button id="descr_bold_btn" type="button" class="btn btn-outline-secondary btn-minw-40" aria-label="Pogrubienie" title="Pogrubienie">
            <i class="fa fa-bold" aria-hidden="true"></i>
          </button>
          <button id="descr_italic_btn" type="button" class="btn btn-outline-secondary btn-minw-40" aria-label="Pochylenie" title="Pochylenie">
            <i class="fa fa-italic" aria-hidden="true"></i>
          </button>
          <button id="descr_quote_btn" type="button" class="btn btn-outline-secondary btn-minw-40" aria-label="Cytat" title="Cytat">
            <i class="fa fa-quote-right" aria-hidden="true"></i>
          </button>
          <button id="descr_link_btn" type="button" class="btn btn-outline-secondary btn-minw-40" aria-label="Link" title="Link">
            <i class="fa fa-chain" aria-hidden="true"></i>
          </button>
          <button id="descr_code_btn" type="button" class="btn btn-outline-secondary btn-minw-40" aria-label="Kod" title="Kod">
            <i class="fa fa-code" aria-hidden="true"></i>
          </button>
          <button id="descr_spoil_btn" type="button" class="btn btn-outline-secondary" aria-label="Spoiler" title="Spoiler">
            spoil
          </button>
          <div class="btn-group dropup">
            <button id="descr_lenny_btn" type="button" class="btn btn-outline-secondary" aria-label="lennyface" title="lennyface">
              ( ͡° ͜ʖ ͡°)
            </button>
            <button id="descr_lenny_dropdown_btn" type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-reference="parent">
              <span class="sr-only">Rozwiń</span>
            </button>
            <div class="dropdown-menu dropdown-menu-noborder" aria-labelledby="descr_lenny_dropdown_btn">
              <table class="small lennytab">
              <tr>
                <td><a href="#" class="lenny" title="">( ͡° ʖ̯ ͡°)</a></td>
                <td><a href="#" class="lenny" title="">( ͡º ͜ʖ͡º)</a></td>
                <td><a href="#" class="lenny" title="">( ͡°( ͡° ͜ʖ( ͡° ͜ʖ ͡°)ʖ ͡°) ͡°)</a></td>
                <td><a href="#" class="lenny" title="">(⌐ ͡■ ͜ʖ ͡■)</a></td>
                <td><a href="#" class="lenny" title="">(╥﹏╥)</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">(╯︵╰,)</a></td>
                <td><a href="#" class="lenny" title="">(ʘ‿ʘ)</a></td>
                <td><a href="#" class="lenny" title="">(｡◕‿‿◕｡)</a></td>
                <td><a href="#" class="lenny" title="">ᕙ(⇀‸↼‶)ᕗ</a></td>
                <td><a href="#" class="lenny" title="">ᕦ(òóˇ)ᕤ</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">(✌ ﾟ ∀ ﾟ)☞</a></td>
                <td><a href="#" class="lenny" title="">ʕ•ᴥ•ʔ</a></td>
                <td><a href="#" class="lenny" title="">ᶘᵒᴥᵒᶅ</a></td>
                <td><a href="#" class="lenny" title="">(⌒(oo)⌒)</a></td>
                <td><a href="#" class="lenny" title="">ᄽὁȍ ̪ őὀᄿ</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">( ͡€ ͜ʖ ͡€)</a></td>
                <td><a href="#" class="lenny" title="">( ͡° ͜ʖ ͡°)</a></td>
                <td><a href="#" class="lenny" title="">( ͡° ͜ʖ ͡°)ﾉ⌐■-■</a></td>
                <td><a href="#" class="lenny" title="">(⌐ ͡■ ͜ʖ ͡■)</a></td>
                <td><a href="#" class="lenny" title="">¯\_(ツ)_/¯</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">(ꖘ⏏ꖘ)</a></td>
                <td><a href="#" class="lenny" title="">(╯°□°）╯︵ ┻━┻</a></td>
                <td><a href="#" class="lenny" title="">( ͡~ ͜ʖ ͡°)</a></td>
                <td><a href="#" class="lenny" title="">( ಠ_ಠ)</a></td>
                <td><a href="#" class="lenny" title="">(・へ・)</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">(ง✿﹏✿)ง</a></td>
                <td><a href="#" class="lenny" title="">(づ•﹏•)づ</a></td>
                <td><a href="#" class="lenny" title="">乁(♥ ʖ̯♥)ㄏ</a></td>
                <td><a href="#" class="lenny" title="">|૦ઁ෴૦ઁ|</a></td>
                <td><a href="#" class="lenny" title="">乁(⫑ᴥ⫒)ㄏ</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">(ꖘ‸ꖘ)</a></td>
                <td><a href="#" class="lenny" title="">ᕙ(✿ ͟ʖ✿)ᕗ</a></td>
                <td><a href="#" class="lenny" title="">(งⱺ ͟ل͜ⱺ)ง</a></td>
                <td><a href="#" class="lenny" title="">(￣෴￣)</a></td>
                <td><a href="#" class="lenny" title="">ヽ( ͠°෴ °)ﾉ</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">└[⚆ᴥ⚆]┘</a></td>
                <td><a href="#" class="lenny" title="">ヽ(☼ᨓ☼)ﾉ</a></td>
                <td><a href="#" class="lenny" title="">XD</a></td>
                <td><a href="#" class="lenny" title="">(ⴲ﹏ⴲ)/</a></td>
                <td><a href="#" class="lenny" title="">(ಠ‸ಠ)</a></td>
              </tr>
              <tr>
                <td><a href="#" class="lenny" title="">(ง ͠° ͟ل͜ ͡°)ง</a></td>
                <td><a href="#" class="lenny" title="">ლ(ಠ_ಠ ლ)</a></td>
                <td><a href="#" class="lenny" title="">(－‸ლ)</a></td>
                <td><a href="#" class="lenny" title="">( ͡° ͜ʖ ͡° )つ──☆*:・ﾟ</a></td>
                <td><a href="#" class="lenny" title="">(╭☞σ ͜ʖσ)╭☞</a></td>
              </tr>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="col-md mb-3">
          <label for="tags_input">Dodatkowe tagi</label>
          <input id="tags_input" type="text" name="tags_input" class="form-control" maxlength="1500" autocomplete="off" value="<?php echo $additional_tags_value ?>">
          <div class="invalid-tooltip">
            Nieprawidłowe tagi. Dozwolone są tylko znaki alfanumeryczne, odstęp/spacja oraz znak #
          </div>

          <div class="custom-control custom-checkbox mt-1 ml-1">
            <input id="save_tags_input" type="checkbox" name="save_tags_input" class="form-control custom-control-input" <?php echo $save_tags_check_value ?>>
            <label class="custom-control-label" for="save_tags_input"><span class="small text-secondary align-text-bottom">Zapamiętaj dodatkowe tagi</span></label>
          </div>
        </div>
      </div>

      <div class="form-row align-items-center">
        <div class="col mb-3">
          <div class="custom-file">
            <input id="file_input" type="file" name="file_input" class="form-control custom-file-input" lang="pl">
            <label class="custom-file-label" for="file_input">Dołącz obrazek z dysku</label>
          </div>
        </div>
        <div class="col-auto mb-3">
          <a id="file_input_reset_button" class="btn btn-primary" href="" aria-label="Usuń plik" title="Usuń plik">
            <i class="fa fa-times" aria-hidden="true"></i>
          </a>
        </div>
      </div>

      <div class="form-row">
        <div class="col-md mb-3">
          <label for="image_url_input">Url obrazka</label>
          <input id="image_url_input" type="url" name="image_url_input" class="form-control" autocomplete="off">
          <div class="invalid-tooltip">
            Url jest nieprawidłowy
          </div>
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

      <div class="custom-control custom-checkbox mb-1">
        <input id="bold_labels_input" type="checkbox" name="bold_labels_input" class="form-control custom-control-input" <?php echo $bold_labels_check_value ?>>
        <label class="custom-control-label" for="bold_labels_input">Pogrubiaj etykiety</label>
      </div>

      <div class="custom-control custom-checkbox mb-1">
        <input id="use_star_rating_input" type="checkbox" name="use_star_rating_input" class="form-control custom-control-input" <?php echo $star_rating_check_value ?>>
        <label class="custom-control-label" for="use_star_rating_input">Ocena w postaci gwiazdek</label>
      </div>

      <div class="custom-control custom-checkbox mb-3">
        <input id="add_ad_input" type="checkbox" name="add_ad_input" class="form-control custom-control-input" <?php echo $add_ad_check_value ?>>
        <label class="custom-control-label" for="add_ad_input">Dołącz informację o tej stronie</label>
      </div>

      <div class="form-row mb-3 align-items-center">
        <div class="col-auto">
          <button class="btn btn-primary pl-5 pr-5" type="submit">Wyślij</button>
        </div>
        <div class="col-auto">
          <button id="preview_button" class="btn btn-outline-primary pl-4 pr-4" type="button">Podgląd</button>
        </div>
      </div>
    </form>

    <footer class="mt-4 mb-4">
      <div class="container text-center">
        <span class="text-muted"><?php echo site_globals::$footer_info ?></span>
      </div>
    </footer>

  </div>

  <script src="./js/jquery-3.5.1.min.js"></script>
  <script src="./js/bootstrap.bundle.min.js?v=2"></script>
  <script src="./js/jquery-ui.min.js"></script>
  <script src="./js/star-rating.min.js" type="text/javascript"></script>
  <script src="./js/main.js?v=12" type="text/javascript"></script>
</body>

</html>