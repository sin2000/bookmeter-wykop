<?php

require_once __DIR__ . '/../utils/site_globals.php';
require_once __DIR__ . '/../utils/app_auth.php';

$base_url = (new app_auth())->get_current_base_url();

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
        <h5><b><a href="https://www.wykop.pl/tag/bookmeter" target="_blank">Bookmeter</a></b> - regulamin</h5>
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
            <a class="dropdown-item" href="../podsumowanie/index.php">Podsumowanie</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="https://www.wykop.pl/tag/bookmeter/" target="_blank">Tag bookmeter</a>
          </div>
        </div>
      </div>
    </div>

    <div id="sec_content" class="container p-0">
      <h5 class="mb-1">Regulamin dodawania wpisów na <a href="https://www.wykop.pl/tag/bookmeter" target="_blank">bookmeter</a></h5>
      <hr class="mt-0 border-primary">

      <ol class="pl-4">
        <li>
        Bookmeter to akcja, w której dodajemy książki, kończąc na koniec roku. Podsumowanie odbędzie się na początku stycznia nowego roku.
        </li>
        <li>
        W edycji mogą brać udział książki zarówno w formie papierowej, elektronicznej, a także audiobooki.
        </li>
        <li>
        Prawidłowo dodany wpis musi zawierać: tytuł, autora, ocenę, zdjęcie okładki oraz krótką opinię/recenzję (minimum 3 zdania).
        </li>
        <li>
        Jeden wpis = jedna książka. Wiem, że o wiele łatwiej jest dodać np. ostatnich 5 książek w jednym wpisie,
        ale później ciężko jest to wyciągnąć do stworzenia podsumowania, dlatego w miarę możliwości bardzo proszę o dodawanie zgodne z powyższym.
        </li>
        <li>
        Wszystkie wpisy uważane za trolling i SPAM będą usuwane z bazy.
        </li>
        <li>
        Organizatorem #bookmeter jest @<a href="https://www.wykop.pl/ludzie/kizimajaro/" target="_blank">kizimajaro</a> - tutaj można zgłaszać wszelkie sugestie czy uwagi.
        </li>
        <li>
        Przede wszystkim proszę czerpać nieograniczoną przyjemność z czytanych książek ( ͡° ͜ʖ ͡°).
        </li>
      </ol>
    </div>

    <footer class="mt-5 mb-4">
      <div class="container text-center">
      </div>
    </footer>

  </div>

  <script src="../js/jquery-3.5.1.min.js"></script>
  <script src="../js/bootstrap.bundle.min.js?v=2"></script>
</body>

</html>
