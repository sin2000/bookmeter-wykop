<?php

require_once __DIR__ . '/../utils/site_globals.php';
require_once __DIR__ . '/../utils/app_auth.php';
require_once __DIR__ . '/../utils/bm_database.php';
require_once __DIR__ . '/../stats/utils/stats_utils.php';
require_once __DIR__ . '/../stats/utils/summary_utils.php';

setlocale(LC_TIME, 'pl_PL.UTF-8');

$base_url = (new app_auth())->get_current_base_url(true);

$statu = new stats_utils(stats_utils::bm_edition_6);
$edition_start_date = htmlspecialchars($statu->get_edition_start_date(true));
$edition_end_date = htmlspecialchars($statu->get_edition_end_date(true));
$start_day = htmlspecialchars(strftime('%A', $statu->get_edition_start_date()->getTimestamp()));
$end_day = htmlspecialchars(strftime('%A', $statu->get_edition_end_date()->getTimestamp()));
$edition_start_date_sum = htmlspecialchars(strftime('%e %B %Y', $statu->get_edition_start_date()->getTimestamp()));
$edition_end_date_sum = htmlspecialchars(strftime('%e %B %Y', $statu->get_edition_end_date()->getTimestamp()));

$bmdb = new bm_database($statu->get_bm_db_filepath());
$last_upd_arr = $bmdb->fetch_last_update_times();

$summ_util = new summary_utils($statu, $bmdb);
$has_edition_ended = $summ_util->has_edition_ended();
$progress = htmlspecialchars($summ_util->get_progress());
$edition_end = $has_edition_ended ? 'zakończyła' : 'zakończy';
$time_to_end = htmlspecialchars($summ_util->get_time_left_to_end());
$book_count = htmlspecialchars($summ_util->get_book_count());
$book_per_day = htmlspecialchars($summ_util->get_book_per_day());
$login_count = htmlspecialchars($summ_util->get_login_count());
$book_per_all_users = htmlspecialchars($summ_util->get_book_per_user($login_count));
$css_dnone_on_end = $has_edition_ended ? 'd-none' : '';

$top_users_html = $summ_util->get_top_users();
$top_voted_users_html = $summ_util->get_top_voted_users();
$last_joined_users = $summ_util->get_last_joined_users();
$top_books_html = $summ_util->get_top_books();
$top_voted_books_html = $summ_util->get_top_voted_books();
$top_authors_html = $summ_util->get_top_authors();
$top_popular_books_html = $summ_util->get_top_popular_books();
$top_genres_html = $summ_util->get_top_genres();
$sex_stats = $summ_util->get_sex_stats();
$book_by_sex = $summ_util->get_book_count_by_sex();
$worst_books_html = $summ_util->get_top_books(true);

?>

<!doctype html>
<html lang="pl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="../css/bootstrap.min.css?v=2" />
  <title>bookmeter</title>
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
</head>

<body>
  <div id="main_content" class="container-fluid mt-3">
    <div class="row">
      <div class="col-auto align-self-center">
        <h5><b>Podsumowanie - VI edycja <a href="https://www.wykop.pl/tag/bookmeter" target="_blank">Bookmeter</a></b> <span class="small text-muted"><?php echo $edition_start_date ?> - <?php echo $edition_end_date ?></span></h5>
      </div>
    </div>
    <div class="row no-gutters mb-2">
      <div class="col-auto align-middle pr-2 mb-1">
        <div class="btn-group dropright">
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Menu
          </button>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="<?php echo $base_url ?>">Dodaj wpis</a>
            <a class="dropdown-item" href="../tabela/index.php">Tabela wpisów</a>
            <a class="dropdown-item" href="index.php"><b>Podsumowanie</b></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="../podsumowanie/edycja5/index.php">V edycja</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="https://www.wykop.pl/tag/bookmeter/" target="_blank">Tag bookmeter</a>
          </div>
        </div>
      </div>
      <div class="col-sm align-self-center form-group mb-1">
        <div class="alert alert-secondary alert-dismissible fade show small m-0 p-2" role="alert"
          title="Ostatnie aktualizacje podsumowania. Aktualizacja - podsumowanie uwzględnia tylko nowe wpisy od poprzedniej aktualizacji(pod uwagę brane są także odświeżone plusy z ~50 najnowszych wpisów). Pełna aktualizacja - podsumowanie uwzględnia wszystkie wpisy.">
          <b>Aktualizacja:</b> <?php echo $last_upd_arr[0] ?>. <b>Pełna aktualizacja:</b> <?php echo $last_upd_arr[1] ?>.
          <button type="button" class="close p-2" data-dismiss="alert" aria-label="Zamknij" title="">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
    </div>

    <div id="sec_content" class="container p-0 small">
      <h5>Bookmeter Edycja VI – krótkie podsumowanie</h5>

      <div class="progress mb-2" style="height: 20px;">
        <div id="bmprogress" class="progress-bar progress-bar-striped bg-success text-center progress-bar-animated" role="progressbar" style="width: <?php echo $progress ?>%" 
          aria-valuenow="<?php echo $progress ?>" aria-valuemin="0" aria-valuemax="100">
          <span class="text-black-50 position-absolute" style="right: 0; left: 0;"><b><?php echo $progress ?>%</b></span>
        </div>
      </div>

      <div>
        <div>
        V Edycja rozpoczęła się <b><?php echo $edition_start_date_sum ?> roku(<?php echo $start_day ?>)</b>, 
        a <?php echo $edition_end ?> się <b><?php echo $edition_end_date_sum ?>(<?php echo $end_day ?>)</b>.
        </div>
        <div class="mt-1 <?php echo $css_dnone_on_end?>">
        Czas pozostały do końca edycji to: <b><?php echo $time_to_end ?></b>.
        </div>
        <div class="mt-1">
        Dotychczas przeczytaliśmy <b><?php echo $book_count ?></b>, co daje nam średnio <b><?php echo $book_per_day ?></b> na dzień.
        </div>
        <div class="mt-1">
        Różowe paski dodały <b><?php echo $book_by_sex['fem'] ?></b>, niebieskie paski dodały <b><?php echo $book_by_sex['mal'] ?></b>, a pozostali <b><?php echo $book_by_sex['unk'] ?></b>.
        </div>
        <div class="mt-1">
        W tej edycji wzięło udział <b><?php echo $login_count ?> wykopowiczów</b>, w tym <?php echo $sex_stats ?>.
        </div>
        <div class="mt-1">
        Na każdego uczestnika przypada średnio <b><?php echo $book_per_all_users ?></b>.
        </div>
      </div>

      <div class="mt-3"><b>Top 20 wykopowiczów:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Login</th>
          <th scope="col">Liczba dodanych książek</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $top_users_html ?>
      </tbody>
      </table>

      <div class="mt-3"><b>Uczestnicy, którzy zebrali największą liczbę plusów:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Login</th>
          <th scope="col">Liczba plusów (razem)</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $top_voted_users_html ?>
      </tbody>
      </table>

      <div class="mt-3"><b>Ostatnio dołączyli:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Login</th>
          <th scope="col">Data dołączenia</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $last_joined_users ?>
      </tbody>
      </table>

      <div class="mt-4"><b>Top 20 najwyżej ocenionych książek:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Autor</th>
          <th scope="col">Tytuł</th>
          <th scope="col">Średnia ocena</th>
          <th scope="col">Suma plusów</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $top_books_html
       ?>
      </tbody>
      </table>

      <div class="mt-4"><b>TOP 10 dodanych książek z największą liczbą zebranych plusów:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Autor</th>
          <th scope="col">Tytuł</th>
          <th scope="col">Suma plusów</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $top_voted_books_html ?>
      </tbody>
      </table>
      
      <div class="mt-4"><b>Najpopularniejsze książki w tej edycji:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Autor</th>
          <th scope="col">Tytuł</th>
          <th scope="col">Liczba wpisów</th>
          <th scope="col">Średnia ocena</th>
          <th scope="col">Liczba plusów (razem)</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $top_popular_books_html ?>
      </tbody>
      </table>

      <div class="mt-4"><b>Najpopularniejsi autorzy w tej edycji:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Autor</th>
          <th scope="col">Liczba dodanych książek</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $top_authors_html ?>
      </tbody>
      </table>

      <div class="mt-4"><b>Najczęściej dodawane gatunki:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Gatunek</th>
          <th scope="col">Liczba dodanych książek</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $top_genres_html ?>
      </tbody>
      </table>

      <div class="mt-4"><b>Najgorzej oceniane książki:</b></div>
      <table class="table table-sm mt-2 table-hover">
      <thead class="thead-light">
        <tr>
          <th scope="col">Miejsce</th>
          <th scope="col">Autor</th>
          <th scope="col">Tytuł</th>
          <th scope="col">Średnia ocena</th>
          <th scope="col">Suma plusów</th>
        </tr>
      </thead>
      <tbody>
        <?php echo $worst_books_html ?>
      </tbody>
      </table>

    </div>

    <footer class="mt-5 mb-4">
      <div class="container text-center">
        <span class="text-muted"><?php echo site_globals::$footer_info ?></span>
      </div>
    </footer>

  </div>

  <script src="../js/jquery-3.5.1.min.js"></script>
  <script src="../js/bootstrap.bundle.min.js?v=2"></script>
  <script src="../stats/js/summary.js"></script>
</body>

</html>
