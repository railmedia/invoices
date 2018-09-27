<?php
class Facturi_Elements_Views {

    function top($items) {
        ob_start();
        if($items) {
?>
    <section id="top" class="top">
        <nav id="top-menu" class="top-menu">
            <ul class="sf-menu">
            <?php foreach($items as $item => $menu) { ?>
                <li>
                    <a href="<?php echo $menu['link'] ?>"><?php echo $menu['label']; ?></a>
                    <?php if(isset($menu['submenu']) && $menu['submenu']) { ?>
                    <ul class="sub-menu">
                        <?php foreach($menu['submenu'] as $submenu) { ?>
                        <li><a href="<?php echo $submenu['link']; ?>" target="<?php echo $submenu['target']; ?>"><?php echo $submenu['label']; ?></a></li>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                </li>
            <?php } ?>
            </ul>
        </nav>
        <div class="top-title">
            <h1>Facturare</h1>
        </div>
        <nav id="logout-menu" class="logout-menu">
            <ul class="sf-menu">
                <li>
                    <a href="app.php?t=logout">Logout</a>
                </li>
            </ul>
        </nav>
        <div class="clear"></div>
    </section>
<?php
        }
        return ob_get_clean();
    }

    function app_main($latestinvoices) {
        ob_start();
?>
    <div class="wrapper">
        <div id="last-invoices" class="widget">
            <div class="widget-title">
                <h2>Ultimele facturi adaugate</h2>
            </div>
            <div class="widget-content">
                <?php
                    if($latestinvoices) {
                        foreach($latestinvoices as $invoice) {
                ?>
                <div class="widget-row">
                    <a href="factura.php?t=view&id=<?php echo $invoice['id']; ?>" target="_blank">Factura <?php echo $invoice['serie']; ?>-<?php echo $invoice['numar'] ?></a> - <?php echo $invoice['data']; ?>
                </div>
                <?php
                        }
                    }
                ?>
            </div>
        </div>
    </div>
<?php
        return ob_get_clean();
    }

    function app_view_invoices($latestinvoices) {
        global $dbo;
        ob_start();
?>
    <div class="wrapper">
        <div id="all-invoices" class="widget">
            <div class="widget-title">
                <h2>Vizualizare facturi</h2>
            </div>
            <div class="widget-invoice-action">
                <a href="app.php?t=addinvoice"><i class="fas fa-plus-circle"></i>Adauga factura</a>
            </div>
            <div class="widget-content">
                <?php if($latestinvoices) { ?>
                <div id="remove-bulk" class="widget-invoice-action">
                    <a><i class="fas fa-minus-circle"></i>Stergere facturi selectate</a>
                </div>
                <form id="remove-bulk-invoices" method="POST" action="">
                    <input type="hidden" name="bulk-delete-invoices" />
                <div class="widget-row">
                    <div class="widget-column widget-column-heading widget-select-invoice">#</div>
                    <div class="widget-column widget-column-heading widget-invoice-main">Factura</div>
                    <div class="widget-column widget-column-heading widget-invoice-client">Client</div>
                    <div class="widget-column widget-column-heading widget-invoice-data">Data</div>
                    <div class="widget-column widget-column-heading widget-invoice-actions">Actiuni</div>
                </div>
                <?php
                    foreach($latestinvoices as $invoice) {
                        $prods = $dbo->get_results("SELECT * FROM produse WHERE id_factura = {$invoice['id']}");
                ?>
                <div class="widget-row">
                    <div class="widget-column widget-select-invoice">
                        <input type="checkbox" name="bulk-invoice[]" value="<?php echo $invoice['id']; ?>" />
                    </div>
                    <div class="widget-column widget-invoice-main">
                        <a href="factura.php?t=view&id=<?php echo $invoice['id']; ?>" data-invoice-id="<?php echo $invoice['id']; ?>" class="open-quickview-invoice" target="_blank">Factura <?php echo $invoice['serie']; ?>-<?php echo $invoice['numar'] ?></a></a>
                        <div class="quick-view-panel" id="quick-view-panel-<?php echo $invoice['id']; ?>">
                            <div class="close-panel"><i class="fas fa-times-circle"></i></div>
                            <strong>Client:</strong> <?php echo $invoice['numeclient']; ?>
                            <p><strong>Produse:</strong></p>
                            <?php
                                $total = 0;
                                $vatinvoice = $invoice['vat_invoice'];
                                $vatrate = $invoice['vat_rate'];

                                foreach($prods as $prod) {
                                    $prodprice = $prod['pretunitar'] * $prod['cantitate'];
                                    $total += $prodprice;
                                    echo '<span class="quick-view-border-bottom">'.(int)$prod['cantitate'].' x '.$prod['produs'].' - '.$prodprice.'</span>';
                                }

                                //echo '<hr/>';

                                $theunitprice = calculate_invoice_unitprice($invoice['id'], $total, $invoice['vat_invoice'], $invoice['vat_products'], $invoice['vat_rate']);
                                $vatvalue = calculate_invoice_vat($invoice['id'], $total, $invoice['vat_invoice'], $invoice['vat_products'], $invoice['vat_rate']);
                                echo '<strong>Total:</strong> ' .$invoice['currency'].' '.$theunitprice.'<br/>';
                                echo '<strong>TVA:</strong> '.$vatvalue;
                            ?>
                        </div>
                    </div>
                    <div class="widget-column widget-column-heading widget-invoice-client"><?php echo $invoice['numeclient']; ?></div>
                    <div class="widget-column widget-column-heading widget-invoice-data"><?php echo $invoice['data']; ?></div>
                    <div class="widget-column widget-invoice-actions">
                        <a href="factura.php?t=view&id=<?php echo $invoice['id']; ?>" data-invoice-curr="<?php echo $invoice['currency']; ?>" data-invoice-id="<?php echo $invoice['id']; ?>" class="view-invoice" title="Vizualizare" target="_blank"><i class="fas fa-eye"></i></a>
                        <a id="generate-pdf-<?php echo $invoice['id']; ?>" class="generate-pdf" data-invoice-curr="<?php echo $invoice['currency']; ?>" data-invoice-id="<?php echo $invoice['id']; ?>" href="pdf.php?invoiceid=<?php echo $invoice['id']; ?>&lang=es&curr=<?php echo $invoice['currency']; ?>" title="Generare PDF"><i class="fas fa-file-pdf"></i></a>

                        <?php echo $this->app_generate_view_panel($invoice['id'], $invoice['currency']); ?>

                        <a href="app.php?t=editinvoice&id=<?php echo $invoice['id']; ?>" title="Editare"><i class="fas fa-pen-square"></i></a>
                        <a id="remove-invoice-<?php echo $invoice['id']; ?>" class="remove-invoice" data-invoiceid="<?php echo $invoice['id']; ?>" title="Stergere"><i class="fas fa-minus-circle"></i></a>
                    </div>
                </div>
                <?php } ?>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
<?php
        return ob_get_clean();
    }

    function app_generate_view_panel($invoiceid, $currency) {
        ob_start();
?>

        <div class="config-generate-pdf" id="config-generate-pdf-<?php echo $invoiceid; ?>">
            <div class="close-panel"><i class="fas fa-times-circle"></i></div>
            Limba<br/>
            <select class="select-lang">
                <option value="es">Spaniola</option>
                <option value="en">Engleza</option>
            </select>
            <a href="pdf.php?invoiceid=<?php echo $invoiceid; ?>&lang=es&curr=<?php echo $currency; ?>" class="generate-pdf-final">Genereaza PDF</a>
            <a href="factura.php?t=view&id=<?php echo $invoiceid; ?>&lang=es&curr=<?php echo $currency; ?>" target="_blank" class="view-invoice-final">Vizualizeaza factura</a>
        </div>
<?php
        return ob_get_clean();
    }

    function app_add_invoice($number = null, $vatrate) {
        ob_start();
?>
<div class="wrapper">
    <form id="add-invoices-form" method="post" class="add-edit-invoices" action="">
    <div id="add-invoices" class="widget">
        <div class="widget-title">
            <h2>Adauga factura</h2>
        </div>
        <div class="widget-content">
            <div class="add-invoice-section">
                <h3>Date factura</h3>
                <div class="widget-row">
                    <label for="data">Data*</label>
                    <div class="field">
                        <input type="text" id="data" name="data" value="<?php echo date('d.m.Y'); ?>" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="serie">Serie*</label>
                    <div class="field">
                        <input type="text" id="serie" name="serie" readonly="readonly" value="RME" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="numar">Numar*</label>
                    <div class="field">
                        <input type="text" id="numar" name="numar" value="<?php echo $number; ?>" /><br/>
                        <small><em>Ultimul numar adaugat: <?php echo $number - 1; ?></em></small>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Date client</h3>
                <div class="widget-row">
                    <label for="nume">Nume*</label>
                    <div class="field">
                        <input type="text" id="nume" name="nume" value="" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="reg_com">Reg. Com. (optional)</label>
                    <div class="field">
                        <input type="text" id="reg_com" name="reg_com" value="" />
                    </div>
                </div>
                <div class="clear"></div>
                <div class="widget-row">
                    <label for="nif">NIF</label>
                    <div class="field">
                        <input type="text" id="nif" name="nif" value="" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="adresa">Adresa*</label>
                    <div class="field">
                        <textarea id="adresa" name="adresa"></textarea>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Date bancare (optional)</h3>
                <div class="widget-row">
                    <label for="iban">IBAN</label>
                    <div class="field">
                        <input type="text" id="iban" name="iban" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="banca">Banca</label>
                    <div class="field">
                        <input type="text" id="banca" name="banca" />
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Configurare TVA</h3>
                <div class="widget-row">
                    <label for="invoice-vat">TVA factura</label>
                    <div class="field">
                        <select id="invoice-vat" name="invoice-vat">
                            <option value="1">Factura emisa cu TVA</option>
                            <option value="0">Factura emisa fara TVA</option>
                        </select>
                    </div>
                </div>
                <div id="products-vat-row" class="widget-row">
                    <label for="products-vat">TVA produse</label>
                    <div class="field">
                        <select id="products-vat" name="products-vat">
                            <option value="1">Preturile contin TVA</option>
                            <option value="0">Preturile nu contin TVA</option>
                        </select>
                    </div>
                </div>
                <div class="clear"></div>
                <div id="invoice-vat-rate" class="widget-row">
                    <label for="invoice-vat-rate">Valoare TVA (%)</label>
                    <div class="field">
                        <input type="text" id="invoice-vat-rate" name="invoice-vat-rate" value="<?php echo $vatrate; ?>" />
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Moneda</h3>
                <div class="widget-row">
                    <div class="field">
                        <select id="invoice-currency" name="invoice-currency">
                            <option value="EUR">EUR</option>
                            <option value="CHF">CHF</option>
                            <option value="USD">USD</option>
                            <option value="GBP">GBP</option>
                            <option value="RON">RON</option>
                        </select>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <div id="populate-invoice" class="widget">
        <div class="widget-content">
            <div class="add-invoice-section">
                <h3>Produse</h3>
                <p><a id="factura-adauga-produs"><i class="fas fa-plus-circle"></i> Adauga produs</a></p>
                <div id="invoice-products-container">
                    <div class="widget-row">
                        <div class="widget-column">
                            <label>Nume</label>
                        </div>
                        <div class="widget-column">
                            <label>UM</label>
                        </div>
                        <div class="widget-column">
                            <label>Cantitate</label>
                        </div>
                        <div class="widget-column">
                            <label>Pret Unitar</label>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section section-bottom section-center">
                <div class="widget-row-full">
                    <button id="save-invoice">Salveaza factura</button>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
<?php
        return ob_get_clean();
    }

    function app_edit_invoice($invoice, $products) {
        ob_start();
?>
<div class="wrapper">
    <form id="edit-invoices-form" method="post" class="add-edit-invoices" action="">
    <div id="add-invoices" class="widget">
        <div class="widget-title">
            <h2>Modifica factura <?php echo $invoice['serie']; ?>-<?php echo $invoice['numar']; ?></h2>
        </div>
        <div class="widget-invoice-action">
            <a href="app.php?t=viewinvoices"><i class="fas fa-undo"></i>Inapoi la lista de facturi</a>
            <a href="factura.php?t=view&id=<?php echo $invoice['id']; ?>&lang=es&curr=EUR" data-invoice-curr="<?php echo $invoice['currency']; ?>" data-invoice-id="<?php echo $invoice['id']; ?>" class="view-invoice" target="_blank"><i class="fas fa-eye"></i>Vizualizare factura</a>
            <a href="pdf.php?invoiceid=<?php echo $invoice['id']; ?>&lang=es&curr=EUR" data-invoice-curr="<?php echo $invoice['currency']; ?>" data-invoice-id="<?php echo $invoice['id']; ?>" class="generate-pdf"><i class="fas fa-file-pdf"></i>Genereaza PDF</a>

            <?php echo $this->app_generate_view_panel($invoice['id'], $invoice['currency']); ?>

            <a id="remove-invoice-<?php echo $invoice['id'] ?>" class="remove-invoice" data-invoiceid="<?php echo $invoice['id'] ?>" title="Stergere"><i class="fas fa-minus-circle"></i> Stergere Factura</a>
        </div>
        <div class="widget-content">
            <div class="add-invoice-section">
                <h3>Date factura</h3>
                <div class="widget-row">
                    <label for="data">Data*</label>
                    <div class="field">
                        <input type="text" id="data" name="data" value="<?php echo $invoice['data']; ?>" />
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Date client</h3>
                <div class="widget-row">
                    <label for="nume">Nume*</label>
                    <div class="field">
                        <input type="text" id="nume" name="nume" value="<?php echo $invoice['numeclient']; ?>" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="reg_com">Reg. Com. (optional)</label>
                    <div class="field">
                        <input type="text" id="reg_com" name="reg_com" value="<?php echo $invoice['regcom']; ?>" />
                    </div>
                </div>
                <div class="clear"></div>
                <div class="widget-row">
                    <label for="nif">NIF</label>
                    <div class="field">
                        <input type="text" id="nif" name="nif" value="<?php echo $invoice['cuicif']; ?>" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="adresa">Adresa*</label>
                    <div class="field">
                        <textarea id="adresa" name="adresa"><?php echo $invoice['adresa']; ?></textarea>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Date bancare (optional)</h3>
                <div class="widget-row">
                    <label for="iban">IBAN</label>
                    <div class="field">
                        <input type="text" id="iban" name="iban" value="<?php echo $invoice['iban']; ?>" />
                    </div>
                </div>
                <div class="widget-row">
                    <label for="banca">Banca</label>
                    <div class="field">
                        <input type="text" id="banca" name="banca" value="<?php echo $invoice['banca']; ?>" />
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Configurare TVA</h3>
                <div class="widget-row">
                    <label for="invoice-vat">TVA factura</label>
                    <div class="field">
                        <select id="invoice-vat" name="invoice-vat">
                            <option <?php echo isset($invoice['vat_invoice']) && $invoice['vat_invoice'] == 1 ? 'selected="selected"' : ''; ?> value="1">Factura emisa cu TVA</option>
                            <option <?php echo isset($invoice['vat_invoice']) && $invoice['vat_invoice'] == 0 ? 'selected="selected"' : ''; ?> value="0">Factura emisa fara TVA</option>
                        </select>
                    </div>
                </div>
                <?php $productvatclass = isset($invoice['vat_invoice']) && $invoice['vat_invoice'] == 0 ? 'hidden' : 'visible'; ?>
                <div id="products-vat-row" class="widget-row <?php echo $productvatclass; ?>">
                    <label for="products-vat">TVA produse</label>
                    <div class="field">
                        <select id="products-vat" name="products-vat">
                            <option <?php echo isset($invoice['vat_products']) && $invoice['vat_products'] == 1 ? 'selected="selected"' : ''; ?> value="1">Preturile contin TVA</option>
                            <option <?php echo isset($invoice['vat_products']) && $invoice['vat_products'] == 0 ? 'selected="selected"' : ''; ?> value="0">Preturile nu contin TVA</option>
                        </select>
                    </div>
                </div>
                <div class="clear"></div>
                <div id="invoice-vat-rate" class="widget-row <?php echo $productvatclass; ?>">
                    <label for="invoice-vat-rate">Valoare TVA (%)</label>
                    <div class="field">
                        <input type="text" id="invoice-vat-rate" name="invoice-vat-rate" value="<?php echo $invoice['vat_rate']; ?>" />
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section">
                <h3>Moneda</h3>
                <div class="widget-row">
                    <div class="field">
                        <select id="invoice-currency" name="invoice-currency">
                            <option <?php echo isset($invoice['currency']) && $invoice['currency'] == 'EUR' ? 'selected="selected"' : ''; ?> value="EUR">EUR</option>
                            <option <?php echo isset($invoice['currency']) && $invoice['currency'] == 'CHF' ? 'selected="selected"' : ''; ?> value="CHF">CHF</option>
                            <option <?php echo isset($invoice['currency']) && $invoice['currency'] == 'USD' ? 'selected="selected"' : ''; ?> value="USD">USD</option>
                            <option <?php echo isset($invoice['currency']) && $invoice['currency'] == 'GBP' ? 'selected="selected"' : ''; ?> value="GBP">GBP</option>
                            <option <?php echo isset($invoice['currency']) && $invoice['currency'] == 'RON' ? 'selected="selected"' : ''; ?> value="RON">RON</option>
                        </select>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <div id="populate-invoice" class="widget">
        <div class="widget-content">
            <div class="add-invoice-section">
                <h3>Produse</h3>
                <p><a id="factura-adauga-produs"><i class="fas fa-plus-circle"></i> Adauga produs</a></p>
                <div id="invoice-products-container">
                    <div class="widget-row">
                        <div class="widget-column">
                            <label>Nume</label>
                        </div>
                        <div class="widget-column">
                            <label>UM</label>
                        </div>
                        <div class="widget-column">
                            <label>Cantitate</label>
                        </div>
                        <div class="widget-column">
                            <label>Pret Unitar</label>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php if($products) { foreach($products as $product) { ?>
                    <div class="widget-row">
                        <div class="widget-column">
                            <div class="field">
                                <input type="text" class="product_name" placeholder="Nume" name="products[name][]" value="<?php echo $product['produs']; ?>" />
                            </div>
                        </div>
                        <div class="widget-column">
                            <div class="field">
                                <input type="text" class="product_um" placeholder="UM" name="products[um][]" value="<?php echo $product['um']; ?>" />
                            </div>
                        </div>
                        <div class="widget-column">
                            <div class="field">
                                <input type="text" class="product_qty" placeholder="Cantitate" name="products[qty][]" value="<?php echo $product['cantitate']; ?>" />
                            </div>
                        </div>
                        <div class="widget-column">
                            <div class="field">
                                <input type="text" class="product_unit_price" placeholder="Pret unitar" name="products[unit_price][]" value="<?php echo $product['pretunitar']; ?>" />
                            </div>
                        </div>
                        <div class="widget-row-actions">
                            <a class="remove-product"><i class="fas fa-minus-circle"></i></a>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php } } ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="add-invoice-section section-bottom section-center">
                <div class="widget-row-full">
                    <button id="save-edit-invoice">Salveaza modificarile</button>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
<?php
        return ob_get_clean();
    }

}
?>
