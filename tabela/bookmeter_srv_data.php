<?php
 
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simple to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

require '../utils/confidential_vars.php';
require '../stats/utils/stats_utils.php';
 
// DB table to use
$table = 'entry_view';
 
// Table's primary key
$primaryKey = 'id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array('db' => 'entry_id', 'dt' => 0),
    array('db' => 'datestr',  'dt' => 1),
    array('db' => 'login_name',   'dt' => 2),
    array('db' => 'sex',     'dt' => 3),
    array('db' => 'authors',     'dt' => 4),
    array('db' => 'title',     'dt' => 5),
    array('db' => 'genre_name',     'dt' => 6),
    array('db' => 'rate',     'dt' => 7, 'type' => PDO::PARAM_INT),
    array('db' => 'vote_count', 'dt' => 8, 'type' => PDO::PARAM_INT),
    // array(
    //     'db'        => 'start_date',
    //     'dt'        => 4,
    //     'formatter' => function( $d, $row ) {
    //         return date( 'jS M y', strtotime($d));
    //     }
    // ),
    // array(
    //     'db'        => 'salary',
    //     'dt'        => 5,
    //     'formatter' => function( $d, $row ) {
    //         return '$'.number_format($d);
    //     }
    // )
);
 
// SQL server connection information
$sql_details = array(
    'filepath' => confidential_vars::bm_ed6_db_filepath,
);

$ign_logins = (new stats_utils(stats_utils::bm_actual_edition))->get_ignored_logins();
$adv_filter = null;
if($ign_logins != '')
{
    $adv_filter = [
        'column' => 'login_name',
        'operator' => '<>',
        'values' => explode(' ', $ign_logins)
    ];
}
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require('../stats/utils/ssp.class.php');
 
echo json_encode(
    SSP::complex($_GET, $sql_details, $table, $primaryKey, $columns, $adv_filter)
);
