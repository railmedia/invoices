<?php
class Facturi_Elements {

    var $views;

    function __construct() {
        $this->views = new Facturi_Elements_Views;
    }

    function header($type = null) {
        $class = $type ? $type : '';
        ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Facturi</title>
    <?php
        if(isset($_GET['t'])) {
            if($_GET['t'] == 'addinvoice' || $_GET['t'] == 'editinvoice' || $_GET['t'] == 'viewinvoices') {
    ?>
    <link href="vendor/components/jqueryui/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="vendor/components/font-awesome/css/fontawesome-all.min.css" rel="stylesheet" type="text/css" />
    <?php } } ?>
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="assets/js/superfish/css/superfish.css" rel="stylesheet" type="text/css" />
</head>
<body class="<?php echo $class; ?>">
<?php
        return ob_get_clean();
    }

    function footer() {
        ob_start();
?>
    <script type="text/javascript" src="vendor/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/main.js"></script>
    <?php
        if(isset($_GET['t'])) {
            if($_GET['t'] == 'addinvoice' || $_GET['t'] == 'editinvoice' || $_GET['t'] == 'viewinvoices') {
    ?>
    <script type="text/javascript" src="vendor/components/jqueryui/jquery-ui.min.js"></script>
    <script type="text/javascript" src="assets/js/invoice.js"></script>
    <?php } } ?>
</body>
</html>
<?php
        return ob_get_clean();
    }

    function top() {

        $menuitems = array(
            'facturi' => array(
                'label'     => 'Facturi',
                'link'      => 'app.php?t=viewinvoices',
                'submenu'   => array(
                    array(
                        'label' => 'Vizualizeaza',
                        'link'  => 'app.php?t=viewinvoices',
                        'target' => '_self'
                    ),
                    array(
                        'label' => 'Adauga',
                        'link'  => 'app.php?t=addinvoice',
                        'target' => '_blank'
                    )
                )
            ),
            'optiuni' => array(
                'label' => 'Optiuni',
                'link'  => ''
            )
        );

        echo $this->views->top($menuitems);

    }

    function app_main() {

        global $dbo;

        $latestinvoices = $dbo->get_results("SELECT id, serie, numar, data FROM facturi ORDER BY numar DESC LIMIT 0, 20");

        echo $this->views->app_main($latestinvoices);

    }

    function app_view_invoices() {

        global $dbo;
        if(isset($_POST['bulk-delete-invoices'])) {
            foreach($_POST['bulk-invoice'] as $invoiceid) {
                $dbo->delete(
                    'facturi',
                    array('id' => $invoiceid)
                );
                $dbo->delete(
                    'produse',
                    array('id_factura' => $invoiceid)
                );
            }

            header('Location: app.php?t=viewinvoices');

        }
        $invoices = $dbo->get_results("SELECT * FROM facturi ORDER BY numar DESC");
        echo $this->views->app_view_invoices($invoices);

    }

    function app_view_invoice() {

        global $dbo;
        $invoices = $dbo->get_results("SELECT id, serie, numar, data FROM facturi ORDER BY numar DESC");
        echo $this->views->app_view_invoices($invoices);

    }

    function app_add_invoice() {

        global $dbo;

        if(isset($_POST) && $_POST) {

            $data = array(
                'serie'         => $_POST['serie'],
                'numar'         => $_POST['numar'],
                'data'          => $_POST['data'],
                'numeclient'    => $_POST['nume'],
                'regcom'        => isset($_POST['reg_com']) && $_POST['reg_com'] ? $_POST['reg_com'] : '-',
                'cuicif'        => isset($_POST['nif']) && $_POST['nif'] ? $_POST['nif'] : '-',
                'adresa'        => $_POST['adresa'],
                'iban'          => isset($_POST['iban']) && $_POST['iban'] ? $_POST['iban'] : '-',
                'banca'         => isset($_POST['banca']) && $_POST['banca'] ? $_POST['banca'] : '-',
                'currency'      => $_POST['invoice-currency'],
                'vat_invoice'   => $_POST['invoice-vat'],
                'vat_products'  => isset($_POST['invoice-vat']) && $_POST['invoice-vat'] == '1' ? $_POST['products-vat'] : 0,
                'vat_rate'      => isset($_POST['invoice-vat']) && $_POST['invoice-vat'] == '1' ? $_POST['invoice-vat-rate'] : 0
            );

            $id = $dbo->insert(
                'facturi',
                $data,
                true
            );

            $this->app_save_invoice_products($id, $_POST['products']);
            //
            header('Location: app.php?t=editinvoice&id='.$id);

        }

        $number = $dbo->get_var("SELECT numar FROM facturi ORDER BY id DESC LIMIT 0,1");
        $vatrate = get_option('vat_rate');
        echo $this->views->app_add_invoice($number+1, $vatrate);

    }

    function app_edit_invoice() {

        global $dbo;

        if(isset($_POST) && $_POST) {

            $invoiceid = $_GET['id'];

            $data = array(
                'data'          => $_POST['data'],
                'numeclient'    => $_POST['nume'],
                'regcom'        => isset($_POST['reg_com']) && $_POST['reg_com'] ? $_POST['reg_com'] : '-',
                'cuicif'        => isset($_POST['nif']) && $_POST['nif'] ? $_POST['nif'] : '-',
                'adresa'        => $_POST['adresa'],
                'iban'          => isset($_POST['iban']) && $_POST['iban'] ? $_POST['iban'] : '-',
                'banca'         => isset($_POST['banca']) && $_POST['banca'] ? $_POST['banca'] : '-',
                'currency'      => $_POST['invoice-currency'],
                'vat_invoice'   => $_POST['invoice-vat'],
                'vat_products'  => isset($_POST['invoice-vat']) && $_POST['invoice-vat'] == '1' ? $_POST['products-vat'] : 0,
                'vat_rate'      => isset($_POST['invoice-vat']) && $_POST['invoice-vat'] == '1' ? $_POST['invoice-vat-rate'] : 0
            );

            $dbo->update(
                'facturi',
                $data,
                array('id' => $invoiceid)
            );

            $this->app_save_invoice_products($_GET['id'], $_POST['products']);

            header('Location: app.php?t=editinvoice&id='.$invoiceid);

        }

        $invoice = $dbo->get_results("SELECT * FROM `facturi` WHERE id = {$_GET['id']}");
        $products = $dbo->get_results("SELECT * FROM `produse` WHERE id_factura = {$_GET['id']}");

        echo $this->views->app_edit_invoice($invoice[0], $products);

    }

    function app_save_invoice_products($invoiceid, $products) {

        global $dbo;

        $dbo->delete('produse', array('id_factura' => $invoiceid));

        for($i = 0; $i < count($products['name']); $i++) {

            $data = array(
                'id_factura' => $invoiceid,
                'produs'     => $products['name'][$i],
                'um'         => $products['um'][$i],
                'cantitate'  => $products['qty'][$i],
                'pretunitar' => $products['unit_price'][$i]
            );

            $dbo->insert(
                'produse',
                $data
            );

        }

    }

}
?>
