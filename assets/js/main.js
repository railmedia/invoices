jQuery(document).ready(function(){

    if(jQuery('#login_form').length > 0) {
        facturi_validate_login_form();
    }

});

function facturi_validate_login_form() {

    var form    = jQuery('#do_login_form'),
        fields  = [jQuery('#login_user'), jQuery('#login_pass')],
        userid  = jQuery('#login_id'),
        trigger = jQuery('#login_button'),
        errors  = 0;

    trigger.on('click', function(e) {

        e.preventDefault();

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

        if(errors == 0) {

            jQuery.ajax({
                type: 'POST',
                url: 'http://localhost/facturi/ajax.php',
                data: {
                    action : 'login',
                    user : jQuery('#login_user').val(),
                    pass : jQuery('#login_pass').val()
                },
                dataType: 'html',
                success: function(data) {
                    jQuery('.notification').remove();
                    if(data == 0) {
                        //pass incorrect
                        form.after('<p class="notification error">Parola incorecta</p>');
                    } else if(data == -1) {
                        //user inexistant
                        form.after('<p class="notification error">Utilizatorul nu a fost gasit in baza de date</p>');
                    } else {
                        userid.val(data);
                        form.after('<div style="text-align:center"><div class="preloader"></div></div>');
                        form.fadeOut();
                        setTimeout(function(){
                            form.submit();
                        }, 1000);
                    }
                }
            });

        }

    });

}
