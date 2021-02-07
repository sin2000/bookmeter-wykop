<?php

require_once '../utils/site_globals.php';
require_once 'utils/stats_utils.php';

$base_url = "https://bookmeter.ct8.pl";

$statu = new stats_utils(stats_utils::bm_edition_4);
$curr_edition = $statu->get_current_edition();
$curr_edition = htmlspecialchars($curr_edition);

$edition_start_date = $statu->get_edition_start_date(true);
$edition_start_date = htmlspecialchars($edition_start_date);

$end_time = $statu->get_bm_data_update_time();
$end_time = htmlspecialchars($end_time);

$csv_filepath = $statu->get_bookmeter_csv_filepath();
$undetected_filepath = $statu->get_undetected_filepath();

?>

<!doctype html>
<html lang="pl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="../css/bootstrap.min.css?v=2" />
  <link rel="stylesheet" type="text/css" href="../css/datatables.min.css?v=2"/>
  <link rel="stylesheet" href="./css/stats.css?v=2" />
  <title>bookmeter</title>
</head>

<body>
  <div id="main_content" class="container-fluid mt-3">
    <span id="bm_edition" class="hide"><?php echo $curr_edition ?></span>

    <div class="row">
      <div class="col-auto">
        <h2><a href="https://www.wykop.pl/tag/bookmeter" target="_blank">Wykop / #Bookmeter</a></h2>
      </div>
    </div>
    <div class="row">
        <div class="col-auto align-self-center">
            <b>Statystyki - IV edycja Bookmeter</b> od <?php echo $edition_start_date ?> do <?php echo $end_time ?>
        </div>
    </div>

    <div class="form-row mb-3 small">
      <div class="col align-self-center text-left">
        <a href="<?php echo $csv_filepath ?>">Pobierz plik CSV</a>
        -
        <a href="<?php echo $undetected_filepath ?>">Pokaż niewykryte</a>
        -
        <a href="<?php echo $base_url ?>">Dodaj wpis</a>
        -
        <b><a href="index.php">V edycja</a></b>
      </div>
    </div>

    <table id="bookmeter_grid" class="table table-striped table-bordered small" style="width:100%">
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
  <script src="./js/old_stats.js"></script>
</body>

</html>
