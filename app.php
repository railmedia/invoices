<?php
session_start();
require_once('config.php');
if(!is_logged_in()) {
    header('Location: index.php');
}
$elements = new Facturi_Elements;
echo $elements->header();
echo $elements->top();
global $dbo;
if(isset($_GET['t'])) {

    switch($_GET['t']) {

        case 'viewinvoices':
            echo $elements->app_view_invoices();
        break;

        case 'addinvoice':
            echo $elements->app_add_invoice();
        break;

        case 'editinvoice':
            echo $elements->app_edit_invoice();
        break;

        case 'logout':
            session_destroy();
            header('Location: index.php');
        break;

    }

} else {
    echo $elements->app_main();
}
echo $elements->footer();
?>
