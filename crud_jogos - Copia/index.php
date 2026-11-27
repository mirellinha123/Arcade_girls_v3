<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/inc/auth.php';

if (!is_logged_in()) {
    header('Location: ' . BASEURL . 'login.php');
    exit;
}
require_login();

include HEADER_TEMPLATE;
?>
<section class="arcade-home">
    <h1>DASHBOARD</h1>
    <div class="arcade-modules">
        <a class="dashboard-card" href="<?= BASEURL ?>clientes/index.php">
            <i class="fa fa-users" aria-hidden="true"></i>
            <p>CLIENTES</p>
        </a>
        <a class="dashboard-card" href="<?= BASEURL ?>jogos/index.php">
            <i class="fa fa-gamepad" aria-hidden="true"></i>
            <p>JOGOS</p>
        </a>
    </div>
</section>
<?php include FOOTER_TEMPLATE; ?>
