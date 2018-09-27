<?php

define('APP_DIR', __DIR__);
define('ABS_PATH', __DIR__);

require_once(APP_DIR.'/frontend/init.php');

function is_logged_in() {
    if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
        return true;
    } else {
        return false;
    }
}

function get_current_user_id() {
    if(isset($_SESSION['user_id']) && $_SESSION['user_id']) {
        return $_SESSION['user_id'];
    } else {
        return false;
    }
}

function get_option($option) {

    global $dbo;

    return $dbo->get_var("SELECT `option_value` FROM `options` WHERE `option_name` = '{$option}'");

}

function calculate_invoice_unitprice($invoiceid, $unitprice, $vatinvoice, $vatprods, $vatrate) {

    if($vatinvoice) {

        if($vatprods == 1) {
            $theunitprice = number_format($unitprice / (1 + $vatrate / 100), 2);
        } else {
            $theunitprice = number_format($unitprice * (1 + $vatrate / 100), 2);
        }

    } else {

        $theunitprice = $unitprice;

    }

    return $theunitprice;

}

function calculate_invoice_vat($invoiceid, $unitprice, $vatinvoice, $vatprods, $vatrate) {

    if($vatinvoice) {

        if($vatprods == 1) {
            $theunitprice = (float)$unitprice / (1 + $vatrate / 100);
            //echo $unitprice.' - '.$theunitprice;
            $vatvalue = number_format((float)$unitprice - (float)$theunitprice, 2);
        } else {
            $theunitprice = $unitprice * (1 + $vatrate / 100);
            $vatvalue = number_format($theunitprice - $unitprice, 2);
        }

    } else {

        $vatvalue = 0;

    }

    return $vatvalue;

}

function _e($string, $lang) {
    global $dbo;
    $tr = $dbo->get_var("SELECT {$lang} FROM languages WHERE es = '{$string}'");
    if($tr) {
        echo $tr;
    } else {
        echo $string;
    }
}

function __($string, $lang) {
    global $dbo;
    $tr = $dbo->get_var("SELECT {$lang} FROM languages WHERE es = '{$string}'");
    if($tr) {
        return $tr;
    } else {
        return $string;
    }
}

?>
