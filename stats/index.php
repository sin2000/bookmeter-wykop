<?php

require_once '../utils/site_globals.php';
require_once '../utils/bm_database.php';
require_once '../utils/app_auth.php';
require_once 'utils/stats_utils.php';

$base_url = (new app_auth())->get_current_base_url(true);

$statu = new stats_utils(stats_utils::bm_actual_edition);
$edition_start_date = htmlspecialchars($statu->get_edition_start_date(true));
$edition_end_date = htmlspecialchars($statu->get_edition_end_date(true));
$undetected_filepath = $statu->get_undetected_filepath();

$bmdb = new bm_database();
$last_upd_arr = $bmdb->fetch_last_update_times();

?>

<!doctype html>
<html lang="pl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="../css/bootstrap.min.css?v=2" />
  <link rel="stylesheet" type="text/css" href="../css/datatables.min.css?v=2" />
  <link rel="stylesheet" href="./css/stats.css?v=3" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="theme-color" content="#ffffff">
  <title>bookmeter</title>
</head>

<body>
  <div id="main_content" class="container-fluid mt-3">
    <div class="row">
      <div class="col-auto align-self-center">
        <h5><b>Statystyki - V edycja <a href="https://www.wykop.pl/tag/bookmeter" target="_blank">Bookmeter</a></b> <span class="small text-muted"><?php echo $edition_start_date ?> - <?php echo $edition_end_date ?></span></h5>
      </div>
    </div>
    <div class="row no-gutters mb-2">
      <div class="col-auto align-middle pr-2 mb-1">
        <div class="btn-group dropright">
          <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Menu
          </button>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="csv_download.php">Pobierz plik CSV</a>
            <a class="dropdown-item" href="<?php echo $undetected_filepath ?>">Pokaż niewykryte</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?php echo $base_url ?>">Dodaj wpis</a>
            <a class="dropdown-item" href="index.php"><b>Tabela wpisów</b></a>
            <a class="dropdown-item" href="summary.php">Podsumowanie</a>
            <a class="dropdown-item" href="bookmeter_4th_edition.php">IV edycja</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="https://www.wykop.pl/tag/bookmeter/" target="_blank">Tag bookmeter</a>
          </div>
        </div>
        <a href="summary.php" class="btn btn-outline-success ml-1" role="button">
            Podsumowanie
        </a>
      </div>
      <div class="col-sm align-self-center form-group mb-1">
        <div class="alert alert-secondary alert-dismissible fade show small m-0 p-2" role="alert"
          title="Ostatnie aktualizacje wpisów. Aktualizacja - dodawane są tylko nowe wpisy. Pełna aktualizacja - uaktualnienie treści, nowe wpisy oraz usuwanie skasowanych wpisów.">
          <b>Aktualizacja:</b> <?php echo $last_upd_arr[0] ?>. <b>Pełna aktualizacja:</b> <?php echo $last_upd_arr[1] ?>.
          <button type="button" class="close p-2" data-dismiss="alert" aria-label="Zamknij" title="">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
    </div>

    <table id="bookmeter_srv_grid" class="table table-striped table-bordered small" style="width:100%">
      <thead>
        <tr>
          <th>Id</th>
          <th>Data</th>
          <th>Nick</th>
          <th>Pasek</th>
          <th>Autor</th>
          <th>Tytuł</th>
          <th>Gatunek</th>
          <th>Ocena</th>
          <th>Plusy</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th>Id</th>
          <th>Data</th>
          <th>Nick</th>
          <th>Pasek</th>
          <th>Autor</th>
          <th>Tytuł</th>
          <th>Gatunek</th>
          <th>Ocena</th>
          <th>Plusy</th>
        </tr>
      </tfoot>
    </table>

    <footer class="mt-4 mb-4">
      <div class="container text-center">
        <span class="text-muted"><?php echo site_globals::$footer_info ?></span>
      </div>
    </footer>

  </div>

  <script src="../js/jquery-3.5.1.min.js"></script>
  <script src="../js/bootstrap.bundle.min.js?v=2"></script>
  <script type="text/javascript" src="../js/datatables.min.js?v=2"></script>
  <script src="./js/stats.js?v=3"></script>
</body>

</html>