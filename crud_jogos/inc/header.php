<?php require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/fotos.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>ARCADE GIRL'S</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?php echo BASEURL; ?>img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.2/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/style.css">
    <link rel="stylesheet" href="<?php echo BASEURL; ?>css/arcade.css?v=20261125-4">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-theme" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand brand-sistema" href="<?= BASEURL ?>index.php">
                <strong>ARCADE GIRL'S</strong>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCrud"
                aria-controls="navbarCrud" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarCrud">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASEURL ?>clientes/index.php">Clientes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASEURL ?>jogos/index.php">Jogos</a>
                    </li>

                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASEURL ?>usuarios/index.php">Usuários</a>
                        </li>
                    <?php endif; ?>

                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <button class="nav-link dropdown-toggle d-flex align-items-center gap-2 border-0 bg-transparent"
                                type="button" id="menuConta" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= foto_html(current_user()['foto'] ?? null) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuConta">
                                <li><a class="dropdown-item" href="<?= BASEURL ?>minha_conta.php">Minha conta</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="post" action="<?= BASEURL ?>logout.php" class="navbar-logout-form">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="dropdown-item"><i class="fa fa-sign-out" aria-hidden="true"></i> Sair da conta</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASEURL ?>login.php">Entrar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASEURL ?>cadastro.php">Criar conta</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container">