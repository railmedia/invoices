<?php
session_start();
require_once('vendor/dompdf/autoload.inc.php');
require_once('config.php');

use Dompdf\Dompdf;

$invoiceid = $_GET['invoiceid'];
$invoice = $dbo->get_results("SELECT * FROM facturi WHERE id = {$invoiceid}");
$invoice = $invoice[0];

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'es';

$dompdf = new Dompdf();
$dompdf->set_option('defaultFont', 'Helvetica');
$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml(pdf());
$dompdf->render();
$dompdf->stream(__('Factura', $lang).' '.$invoice['serie'].'-'.$invoice['numar']);

function pdf() {

    global $dbo;

    $invoiceid = $_GET['invoiceid'];
    $invoice = $dbo->get_results("SELECT * FROM facturi WHERE id = {$invoiceid}");
    $invoice = $invoice[0];

    $products = $dbo->get_results("SELECT * FROM produse WHERE id_factura = {$invoiceid}");

    $userid = get_current_user_id();
    $useraddress = $dbo->get_results("SELECT * FROM user_addresses WHERE user_id = {$userid} AND status = 1");
    $useraddress = $useraddress[0];

    $currency = isset($_GET['curr']) ? $_GET['curr'] : 'EUR';
    $lang = isset($_GET['lang']) ? $_GET['lang'] : 'es';
    $vatrate = get_option('vat_rate');

    ob_start();
?>
<html>
<head>
<style>
    @page { margin: 10px; }
    body { margin: 10px; font-size:15px; }
    .clear {clear:both;}
    .header-block {float:left; padding:5px; height:170px;}
    .header-left {width:34%; border:1px solid #a2a2a2;}
    .header-middle {width:28%; border-top:1px solid #a2a2a2; border-bottom:1px solid #a2a2a2; text-align:center;}
    .header-middle h3 {text-transform: uppercase;}
    .header-right {width:34%; border:1px solid #a2a2a2;}
    .invoice-series-number {padding:10px; border:1px solid #a2a2a2;}
    .invoice-body {position:relative; top:10px;}
    .invoice-body table td {text-align:center;}
    .invoice-body table td.align-left {text-align:left;}
    .invoice-body table td.align-right {text-align:right;}
    .invoice-body table td {border:1px solid #a2a2a2; padding:5px;}
    .invoice-body table td.number {width:40px;}
    .invoice-body table td.desc {width:317px;}
    .invoice-body table td.qty {width:40px;}
    .invoice-body table td.unit_price {width:90px;}
    .invoice-body table td.total {width:90px;}
    .invoice-body table td.vat {width:90px;}
    .invoice-footer {margin-top:20px;}
    .invoice-footer table td {border:1px solid #a2a2a2; padding:5px;}
    .invoice-footer table td.number {width:33px;}
    .invoice-footer table td.desc {width:270px;}
    .invoice-footer table td.total {width:310px;}
</style>
</head>
<body>
<div class="header-block header-left">
    <strong><?php _e('Proveedor', $lang); ?>:</strong> <?php echo $useraddress['name']; ?><br />
    <strong><?php _e('NIF', $lang); ?>:</strong> <?php echo $useraddress['nif']; ?><br />
    <strong><?php _e('Dirección', $lang); ?>:</strong> <?php echo $useraddress['address']; ?><br />
    <strong>IBAN:</strong> <?php echo $useraddress['iban']; ?><br />
    <strong>S.W.I.F.T.:</strong> <?php echo $useraddress['swift']; ?><br/>
    <strong><?php _e('Bank', $lang); ?>:</strong> <?php echo $useraddress['bank']; ?>
</div>
<div class="header-block header-middle">
    <h3><?php _e('Factura', $lang); ?></h3>
    <div class="invoice-series-number">
        <?php _e('Serie', $lang); ?>: <?php echo $invoice['serie']."-".$invoice['numar']; ?><br />
        <?php _e('Fecha', $lang); ?>: <?php echo $invoice['data']; ?>
    </div>
</div>
<div class="header-block header-right">
    <strong><?php _e('Cliente', $lang); ?>:</strong> <?php echo $invoice['numeclient'] ?><br />
    <strong><?php _e('No. Reg. Mercantil', $lang); ?>:</strong> <?php echo $invoice['regcom']; ?><br />
    <strong><?php _e('NIF', $lang); ?>:</strong> <?php echo $invoice['cuicif']; ?><br />
    <strong><?php _e('Dirección', $lang); ?>:</strong> <?php echo $invoice['adresa']; ?><br />
    <strong>IBAN:</strong> <?php echo $invoice['iban']; ?><br />
    <strong><?php _e('Bank', $lang); ?>:</strong> <?php echo $invoice['banca']; ?>
</div>
<div class="clear"></div>
<div class="invoice-body">
    <?php $currency = 'EUR'; ?>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td class="number first-cell">
                <strong>#</strong>
            </td>
            <td class="desc">
                <strong><?php _e('Concepto', $lang); ?></strong>
            </td>
            <td class="qty">
                <strong><?php _e('Cantidad', $lang); ?></strong>
            </td>
            <td class="unit_price">
                <strong><?php _e('Base', $lang); ?><br/> - <?php print $currency; ?> -</strong>
            </td>
            <td class="total">
                <strong><?php _e('Importe', $lang); ?><br/>- <?php print $currency; ?> -</strong>
            </td>
            <td class="vat last-cell">
                <strong><?php _e('IVA', $lang); ?> (21%)<br/>- <?php print $currency; ?> -</strong>
            </td>
        </tr>
        <tr>
            <td class="number first-cell">0</td>
            <td class="desc">1</td>
            <td class="qty">2</td>
            <td class="unit_price">3</td>
            <td class="total">4 (2 x 3)</td>
            <td class="vat last-cell">5</td>
        </tr>
        <?php
            $vatinvoice = $invoice['vat_invoice'];
            $counter = 1;
            $totalinvoice = 0;
            $totalvat = 0;

            foreach($products as $product) {

                if($vatinvoice) {

                    $vatprods = $invoice['vat_products'];
                    $unitprice = $product['pretunitar'];
                    if($vatprods == 1) {
                        $theunitprice = number_format($unitprice / (1 + $vatrate / 100), 2);
                        $vatvalue = number_format($unitprice - $theunitprice, 2);
                    } else {
                        $theunitprice = number_format($unitprice * (1 + $vatrate / 100), 2);
                        $vatvalue = number_format($theunitprice - $unitprice, 2);
                    }

                } else {

                    $theunitprice = $product['pretunitar'];
                    $vatvalue = 0;

                }

                $totalinvoice += $theunitprice * $product['cantitate'];
                $totalvat += $vatvalue * $product['cantitate'];
        ?>
            <tr>
                <td class="number first-cell">
                <?php echo $counter; ?>
                </td>
                <td class="desc align-left">
                <strong><?php echo $product['produs']; ?></strong>
                </td>
                <td class="qty">
                <?php echo $product['cantitate']; ?>
                </td>
                <td class="unit_price">
                <?php echo $theunitprice; ?>
                </td>
                <td class="total">
                <?php echo $theunitprice * $product['cantitate']; ?>
                </td>
                <td class="vat last-cell">
                <?php
                    if($vatinvoice) {
                        echo number_format($vatvalue * $product['cantitate'], 2);
                    } else {
                        echo 0;
                    }
                ?>
                </td>
            </tr>
        <?php
                $counter++;
            }
        ?>
        <tr>
            <td class="tablecontentcell font2 first-cell">&nbsp;</td>
            <td class="tablecontentcell font2">&nbsp;</td>
            <td class="tablecontentcell font2">&nbsp;</td>
            <td class="tablecontentcell font2">&nbsp;</td>
            <td class="tablecontentcell font2">&nbsp;</td>
            <td class="tablecontentcellright font2 last-cell">&nbsp;</td>
        </tr>
    </table>
</div>
<div class="invoice-footer">
    <table width="100%" cellpadding="0" cellspacing="0">
    	<tr>
        	<td class="number first-cell" valign="top">&nbsp;</td>
            <td class="desc" valign="top">&nbsp;</td>
            <td class="total last-cell" valign="top">
                <table width="100%" cellpadding="0" cellspacing="0">
                	<tr>
                    	<td class="tablecontentcell3" style="width:47.9%">
                    		<?php _e('Subtotales', $lang); ?>:
                    	</td>
                        <td class="tablecontentcell3" align="right" style="width:26.8%;">
                        	<?php echo number_format($totalinvoice, 2, '.', '');?>
                        </td>
                        <td class="tablecontentcell3" align="right">
                        	<?php echo number_format($totalvat, 2, '.', ''); ?>
                        </td>
                    </tr>
    			</table>
    			<table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                    	<td class="tablecontentcell3" style="width:47.9%;">
                        <?php _e('Total', $lang); ?>:
                        </td>
                        <td align="right" class="tablecontentcell3">
                        	<?php echo number_format($totalinvoice + $totalvat, 2); ?>
                        </td>
                     </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
<?php
    return ob_get_clean();
}
?>
