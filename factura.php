<?php
session_start();
header('Content-Type: text/html; charset=utf-8' );
require_once('config.php');
if(!is_logged_in() || !isset($_GET['id']) || (isset($_GET['id']) && empty($_GET['id']))) {
    header('Location: index.php');
}
$elements = new Facturi_Elements;
echo $elements->header('invoice');
global $dbo;
$invoice = $dbo->get_results("SELECT * FROM facturi WHERE id={$_GET['id']}");
if($invoice) {
    $invoice = $invoice[0];
}
$userid = get_current_user_id();
$useraddress = $dbo->get_results("SELECT * FROM user_addresses WHERE user_id = {$userid} AND status = 1");
if($useraddress) {
    $useraddress = $useraddress[0];
}
$products = $dbo->get_results("SELECT * FROM produse WHERE id_factura={$_GET['id']}");
$currency = isset($_GET['curr']) ? $_GET['curr'] : 'EUR';
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'es';
$vatrate = get_option('vat_rate');
?>
<div class="wrapper">

	<div class="numefurnizor font">
   	    <?php _e('Proveedor', $lang); ?>: <?php echo $useraddress['name']; ?><br />
	    <?php _e('NIF', $lang); ?>: <?php echo $useraddress['nif']; ?><br />
	    <?php _e('Dirección', $lang); ?>: <?php echo $useraddress['address']; ?><br />
	    IBAN: <?php echo $useraddress['iban']; ?><br />
        S.W.I.F.T.: <?php echo $useraddress['swift']; ?><br/>
        <?php _e('Banco', $lang); ?>: <?php echo $useraddress['bank']; ?>
    </div>
    <div class="titluserienumar font" style="padding:5px">
        <a class="titlu"><?php _e('Factura', $lang); ?></a>
        <table class="tableserienumar" width="300px">
            <tr>
                <td align="center">
                    <?php _e('Serie', $lang); ?>: <?php echo $invoice['serie']."-".$invoice['numar'] ?><br />
                    <?php _e('Fecha', $lang); ?>: <?php echo $invoice['data'] ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="numeclient font" style="padding:5px;">
        <?php _e('Cliente', $lang); ?>: <?php echo $invoice['numeclient'] ?><br />
        <?php _e('No. Reg. Mercantil', $lang); ?>: <?php echo $invoice['regcom']; ?><br />
        <?php _e('NIF', $lang); ?>: <?php echo $invoice['cuicif'] ?><br />
        <?php _e('Dirección', $lang); ?>: <?php echo $invoice['adresa'] ?><br />
        IBAN: <?php echo $invoice['iban'] ?><br />
        <?php _e('Banco', $lang); ?>: <?php echo $invoice['banca'] ?>
    </div>
    <div class="clear"></div>
    <div class="content">
    	<table width="100%" class="tablecontent font" cellspacing="0" cellpadding="0">
        	<tr>
                <td align="center" class="tablecontentcell first-cell" width="40px">
                	<strong>#</strong>
                </td>
                <td align="center" class="tablecontentcell" width="470px">
                	<strong><?php _e('Concepto', $lang); ?></strong>
                </td>
                <td align="center" class="tablecontentcell" width="40px">
                	<strong><?php _e('Cantidad', $lang); ?></strong>
                </td>
                <td align="center" class="tablecontentcell" width="90px">
                	<strong><?php _e('Base', $lang); ?><br/> - <?php print $currency; ?> -</strong>
                </td>
                <td align="center" class="tablecontentcell" width="90px">
                	<strong><?php _e('Importe', $lang); ?><br/>- <?php print $currency; ?> -</strong>
                </td>
                <td align="center" class="tablecontentcellright last-cell" width="90px">
                	<strong><?php _e('IVA', $lang); ?> (<?php echo $vatrate; ?>%)<br/>- <?php print $currency; ?> -</strong>
                </td>
            </tr>
            <tr>
				<td align="center" class="tablecontentcell first-cell" width="40px">0</td>
				<td align="center" class="tablecontentcell" width="470px">1</td>
				<td align="center" class="tablecontentcell" width="40px">2</td>
				<td align="center" class="tablecontentcell" width="90px">3</td>
				<td align="center" class="tablecontentcell" width="90px">4 (2 x 3)</td>
				<td align="center" class="tablecontentcellright last-cell" width="90px">5</td>
            </tr>
            <?php
                $vatinvoice = $invoice['vat_invoice'];
                $counter = 1;
                $totalinvoice = 0;
                $totalvat = 0;

                foreach($products as $product) {

                    // if($vatinvoice) {
                    //
                    //     $vatprods = $invoice['vat_products'];
                    //     $unitprice = $product['pretunitar'];
                    //     if($vatprods == 1) {
                    //         $theunitprice = number_format($unitprice / (1 + $vatrate / 100), 2);
                    //         $vatvalue = number_format($unitprice - $theunitprice, 2);
                    //     } else {
                    //         $theunitprice = number_format($unitprice * (1 + $vatrate / 100), 2);
                    //         $vatvalue = number_format($theunitprice - $unitprice, 2);
                    //     }
                    //
                    // } else {
                    //
                    //     $theunitprice = $product['pretunitar'];
                    //     $vatvalue = 0;
                    //
                    // }

                    $theunitprice = calculate_invoice_unitprice($invoice['id'], $product['pretunitar'], $invoice['vat_invoice'], $invoice['vat_products'], $invoice['vat_rate']);
                    $vatvalue = calculate_invoice_vat($invoice['id'], $product['pretunitar'], $invoice['vat_invoice'], $invoice['vat_products'], $invoice['vat_rate']);

                    $totalinvoice += $theunitprice * $product['cantitate'];
                    $totalvat += $vatvalue * $product['cantitate'];
            ?>
            	<tr>
            		<td align="center" class="tablecontentcell first-cell" width="40px">
	                <?php echo $counter; ?>
	                </td>
	                <td align="left" class="tablecontentcell" width="470px">
	                <strong><?php echo $product['produs']; ?></strong>
	                </td>
	                <td align="center" class="tablecontentcell" width="40px">
	                <?php echo $product['cantitate']; ?>
	                </td>
	                <td align="center" class="tablecontentcell" width="90px">
	                <?php echo $theunitprice; ?>
	                </td>
	                <td align="center" class="tablecontentcell" width="90px">
	                <?php
                        echo $theunitprice * $product['cantitate'];
                        //echo number_format($product['pretunitar'] * $product['cantitate'], 2, '.', '');
	                    //$totalinvoice = $totalinvoice + ($product['pretunitar'] * $product['cantitate']);
                    ?>
	                </td>
	                <td align="center" class="tablecontentcellright last-cell" width="90px">
	                <?php
                        if($vatinvoice) {
                            echo number_format($vatvalue * $product['cantitate'], 2);
                            //echo number_format(0.21 * ($product['pretunitar'] * $product['cantitate']), 2, '.', '');
    	                	//$totalvat = $totalvat + (0.21 * ($product['pretunitar'] * $product['cantitate']));
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
    <br class="clear" />
    <table width="100%" class="tablecontent font2" cellpadding="0" cellspacing="0">
    	<tr>
        	<td style="width:5.6%;" class="tablecontentcell first-cell" valign="top">&nbsp;</td>
            <td class="tablecontentcell" style="width:52.4%;" valign="top">&nbsp;</td>
            <td class="tablecontentcellright last-cell" valign="top">
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
