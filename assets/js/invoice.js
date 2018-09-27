jQuery(document).ready(function(){

    if(jQuery('#save-invoice').length > 0) {
        facturi_datepickers();
        facturi_save_invoice();
    }

    if(jQuery('#save-edit-invoice').length > 0) {
        facturi_datepickers();
        facturi_save_edit_invoice();
    }

    if(jQuery('#factura-adauga-produs').length > 0) {
        jQuery('#factura-adauga-produs').on('click', function(){
            facturi_add_product();
            facturi_delete_product();
        });
    }

    if(jQuery('.remove-product').length > 0) {
        facturi_delete_product();
    }

    if(jQuery('.remove-invoice').length > 0) {
        facturi_delete_invoice();
    }

    if(jQuery('#remove-bulk').length > 0) {
        facturi_delete_bulk_invoices();
    }

    if(jQuery('#invoice-vat').length > 0) {
        facturi_vat_config();
    }

    if(jQuery('.generate-pdf').length > 0) {
        facturi_generate_pdf_config();
    }

    if(jQuery('.open-quickview-invoice').length > 0) {
        facturi_quick_view();
    }

});

function facturi_datepickers() {
    jQuery('#data').datepicker({ dateFormat: 'dd.mm.yy' });
}

function facturi_save_invoice() {

    jQuery('#save-invoice').on('click', function(e) {

        e.preventDefault();

        var form = jQuery('#add-invoices-form'),
            fields = [jQuery('#data'), jQuery('#serie'), jQuery('#numar'), jQuery('#nume'), jQuery('#adresa')],
            errors = 0;

        jQuery.each(fields, function(i, v){

            if(v.val() == '' || v.val() == ' ') {
                v.css('background', '#ffafaf');
                v.focus();
                errors = 1;
                return false;
            } else {
                v.css('background', '#fff');
                errors = 0;
            }

        });

        if(!errors) {

            jQuery.ajax({
                type: 'POST',
                url: 'http://localhost/facturi/ajax.php',
                data: {
                    action : 'invoice_number_unique',
                    number : jQuery('#numar').val()
                },
                dataType: 'html',
                success: function(data) {

                    jQuery('.notification').remove();

                    if(data == 0) {
                        form.after('<div style="text-align:center; position:absolute; top:50%; left:50%;"><div class="preloader"></div></div>');
                        form.fadeOut();
                        setTimeout(function(){
                            form.submit();
                        }, 1000);
                    } else if(data == 1) {
                        form.after('<p class="notification error">Numarul facturii exista deja in baza de date. Introdu un alt numar.</p>');
                        jQuery('#numar').css('background', '#ffafaf');
                        jQuery('#numar').focus();
                    }

                }
            });

        }

    });

}

function facturi_save_edit_invoice() {

    jQuery('#save-edit-invoice').on('click', function(e) {

        e.preventDefault();

        var form = jQuery('#edit-invoices-form'),
            fields = [jQuery('#data'), jQuery('#nume'), jQuery('#adresa')],
            errors = 0;

        jQuery.each(fields, function(i, v){

            if(v.val() == '' || v.val() == ' ') {
                v.css('background', '#ffafaf');
                v.focus();
                errors = 1;
                return false;
            } else {
                v.css('background', '#fff');
                errors = 0;
            }

        });

        if(!errors) {

            form.after('<div style="text-align:center; position:absolute; top:50%; left:50%;"><div class="preloader"></div></div>');
            form.fadeOut();
            setTimeout(function(){
                form.submit();
            }, 1000);

        }

    });

}

function facturi_add_product() {

    var container = jQuery('#invoice-products-container');

    var html = '' +
    '<div class="widget-row">' +
        '<div class="widget-column">' +
            '<div class="field">' +
                '<input type="text" class="product_name" placeholder="Nume" name="products[name][]" value="" />' +
            '</div>' +
        '</div>' +
        '<div class="widget-column">' +
            '<div class="field">' +
                '<input type="text" class="product_um" placeholder="UM" name="products[um][]" value="" />' +
            '</div>' +
        '</div>' +
        '<div class="widget-column">' +
            '<div class="field">' +
                '<input type="text" class="product_qty" placeholder="Cantitate" name="products[qty][]" value="" />' +
            '</div>' +
        '</div>' +
        '<div class="widget-column">' +
            '<div class="field">' +
                '<input type="text" class="product_unit_price" placeholder="Pret unitar" name="products[unit_price][]" value="" />' +
            '</div>' +
        '</div>' +
        '<div class="widget-row-actions">' +
            '<a class="remove-product"><i class="fas fa-minus-circle"></i></a>' +
        '</div>' +
        '<div class="clear"></div>' +
    '</div>';

    container.append(html);

}

function facturi_delete_product() {

    var trigger = '.remove-product';

    jQuery('body').on('click', trigger, function(){
        jQuery(this).parent().parent('.widget-row').remove();
    });

}

function facturi_delete_invoice() {

    var trigger = jQuery('.remove-invoice');

    trigger.on('click', function(){

        var r = confirm('Confirma stergerea');

        if(r == true) {
            var invoiceid = jQuery(this).attr('data-invoiceid');

            jQuery.ajax({
                type: 'POST',
                url: 'http://localhost/facturi/ajax.php',
                data: {
                    action : 'delete_invoice',
                    invoiceid : invoiceid
                },
                dataType: 'html',
                success: function(data) {

                    window.location = 'app.php?t=viewinvoices';

                }
            });

        }

    });

}

function facturi_delete_bulk_invoices() {

    jQuery('.widget-select-invoice input').on('click', function(){
        if(jQuery('.widget-select-invoice input').is(":checked")) {
            jQuery('#remove-bulk').fadeIn();
        } else {
            jQuery('#remove-bulk').fadeOut();
        }
    });

    jQuery('#remove-bulk').on('click', function(){
        var r = confirm('Confirma stergerea');
        if(r == true) {
            jQuery('#remove-bulk-invoices').submit();
        }
    });

}

function facturi_vat_config() {

    var vatinvoice = jQuery('#invoice-vat'),
        vatprods = jQuery('#products-vat-row'),
        vatrate = jQuery('#invoice-vat-rate');

    vatinvoice.on('change', function(){
        if(jQuery(this).val() == 0) {
            vatprods.fadeOut();
            vatrate.fadeOut();
        } else {
            vatprods.fadeIn();
            vatrate.fadeIn();
        }
    });

}

function facturi_generate_pdf_config() {

    var trigger = jQuery('.generate-pdf, .view-invoice');

    trigger.on('click', function(e) {

        e.preventDefault();

        var id          = jQuery(this).attr('data-invoice-id'),
            curr        = jQuery(this).attr('data-invoice-curr'),
            trigger     = jQuery(this),
            configpanel = jQuery('#config-generate-pdf-'+id),
            selectlang  = jQuery('#config-generate-pdf-'+id+' .select-lang'),
            url         = jQuery('#config-generate-pdf-'+id+' .generate-pdf-final'),
            view        = jQuery('#config-generate-pdf-'+id+' .view-invoice-final'),
            close       = jQuery('#config-generate-pdf-'+id+' .close-panel');

        configpanel.fadeToggle();
        trigger.toggleClass('active');

        selectlang.on('change', function(){
            url.attr('href', 'pdf.php?invoiceid='+id+'&lang='+selectlang.val()+'&curr='+curr);
            view.attr('href', 'factura.php?t=view&id='+id+'&lang='+selectlang.val()+'&curr='+curr);
        });

        close.on('click', function(){
            configpanel.fadeOut();
            trigger.removeClass('active');
        });

    });

}

function facturi_quick_view() {

    var trigger = jQuery('.open-quickview-invoice');

    trigger.on('click', function(e) {

        e.preventDefault();

        var id = jQuery(this).attr('data-invoice-id'),
            panel = jQuery('#quick-view-panel-'+id),
            close = jQuery('#quick-view-panel-'+id+' .close-panel');

        jQuery('.quick-view-panel').draggable();

        jQuery('.quick-view-panel').hide();
        jQuery('#quick-view-panel-'+id).fadeToggle();

        close.on('click', function(){
            panel.fadeOut();
        });

    });

}
