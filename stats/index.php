<?php

require_once '../utils/site_globals.php';
require_once '../utils/app_auth.php';

session_start();
$app = new app_auth;
$base_url = $app->get_current_base_url();

$end_time = '';
$datafile_time = filemtime('../data/bookmeter.csv');
if($datafile_time !== false)
{
    $dt = new DateTime();
    $dt->setTimestamp($datafile_time);
    $dt->sub(new DateInterval('P1D'));
    $end_time = $dt->format('Y-m-d') . ' 23:59:59';
    $end_time = htmlspecialchars($end_time);
}

?>

<!doctype html>
<html lang="pl">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="../css/datatables.min.css"/>
  <!-- <link rel="stylesheet" href="./css/stats.css" /> -->
  <title>bookmeter</title>
</head>

<body>
  <div id="main_content" class="container-fluid mt-3">

    <div class="row">
      <div class="col-auto">
        <h2><a href="https://www.wykop.pl/tag/bookmeter" target="_blank">Wykop / #Bookmeter</a></h2>
      </div>
    </div>
    <div class="row">
        <div class="col-auto align-self-center">
            <b>Statystyki - IV edycja Bookmeter</b> od 2020-07-29 do <?php echo $end_time; ?>
        </div>
    </div>

    <div class="form-row mb-3 small">
      <div class="col align-self-center text-left">
        <a href="../data/bookmeter.csv">Pobierz plik CSV</a>
        -
        <a href="../data/undetected.txt">Pokaż niewykryte</a>
        -
        <a href="<?php echo $base_url; ?>">Dodaj wpis</a>
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
  <script src="../js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="../js/datatables.min.js"></script>
  <script src="./js/stats.js"></script>
</body>

</html>
