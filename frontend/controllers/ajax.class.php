<?php
require_once('db.class.php');

if(isset( $_POST['action'] ) && !empty($_POST['action'])) {

    $ajax = new Facturi_Ajax($_POST);

}

class Facturi_Ajax {

    var $action;

    function __construct($data) {
        if(!isset($data['action']) || (isset($data['action']) && empty($data['action']))) {
            return;
        }

        $this->action = $data['action'];
        call_user_func(array($this, $this->action), $data);

    }

    function login($data) {

        global $dbo;

        // $q = $db->query("SELECT * FROM `facturi`");
        // var_dump($q->fetch_all());

        $userexists = $dbo->get_var("SELECT user_login FROM users WHERE user_login = '{$data['user']}'");

        if($userexists) {
            $userpass = $dbo->get_var("SELECT user_pass FROM users WHERE user_login = '{$data['user']}'");
            if(password_verify($data['pass'], $userpass)) {
                //all good
                $userid = $dbo->get_var("SELECT id FROM users WHERE user_login = '{$data['user']}'");
                echo $userid;
            } else {
                //Wrong pass
                echo 0;
            }
        } else {
            //Inexistant user
            echo -1;
        }

        $user = $data['user'];
        $pass = $data['pass'];

    }

    function invoice_number_unique($data) {

        global $dbo;

        $numberexists = $dbo->get_var("SELECT numar FROM facturi WHERE numar = {$data['number']}");

        if($numberexists) {
            echo 1;
        } else {
            echo 0;
        }

    }

    function delete_invoice($data) {

        global $dbo;

        $invoiceid = $data['invoiceid'];

        $dbo->delete(
            'facturi',
            array('id' => $invoiceid)
        );

        $dbo->delete(
            'produse',
            array('id_factura' => $invoiceid)
        );

    }

}
?>
