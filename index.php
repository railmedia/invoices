<?php
session_start();

if(isset($_POST) && $_POST) {
    $_SESSION['user_id'] = $_POST['user_id'];
}

if(isset($_SESSION['user_id'])) {
    header('Location: app.php?t=viewinvoices');
}

require_once('config.php');
$elements = new Facturi_Elements;
echo $elements->header();
?>
<div class="wrapper">
    <div id="login_form" class="login_form">
        <form id="do_login_form" name="form1" method="post" action="">
            <div class="login_form_block">
                <label for="username">User:</label>
                <input id="login_user" name="username" type="text" />
            </div>
            <div class="login_form_block">
                <label for="password">Password:</label>
                <input id="login_pass" name="password" type="password" />
            </div>
            <input type="hidden" id="login_id" name="user_id" />
            <div class="login_form_block">
                <button id="login_button">Login</button>
            </div>
        </form>
    </div>
</div>
<?php echo $elements->footer(); ?>
