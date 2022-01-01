<?php

require '../../utils/bm_database.php';
require '../../stats/utils/stats_utils.php';

$filename = 'bookmeter.csv';
$bm_db = new bm_database((new stats_utils(stats_utils::bm_edition_5))->get_bm_db_filepath());
$bm_db->start_get_bm_view();

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="' . $filename . '";');

ob_end_clean();
$handle = fopen('php://output', 'w');

fputcsv($handle, ['Id', 'Data', 'Nick', 'Pasek', 'Autor', 'Tytuł', 'Gatunek', 'Ocena', 'Plusy']);

while($row = $bm_db->get_next_bm_view_row())
{
  fputcsv($handle, $row);
}

fclose($handle);

?>
