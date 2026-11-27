<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_once DBAPI;
require_once __DIR__ . '/../inc/fotos.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
$usuario = $id ? find('Usuarios', $id) : null;
if (!$usuario) {
    db_message('Usuário não encontrado.', 'warning');
    header('Location: ' . BASEURL . 'usuarios/index.php');
    exit;
}

$nome = htmlspecialchars((string)($usuario['Nome'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string)($usuario['Email'] ?? ''), ENT_QUOTES, 'UTF-8');
$username = htmlspecialchars((string)($usuario['Username'] ?? ''), ENT_QUOTES, 'UTF-8');
$dataCadastro = !empty($usuario['DataCad']) ? date('d/m/Y H:i', strtotime($usuario['DataCad'])) : 'Não informada';
$admin = ($usuario['Perfil'] ?? '') === 'admin';
$proprio = (int)$usuario['Id'] === (int)current_user()['id'];

include HEADER_TEMPLATE;
?>

<section class="detalhe-pagina usuarios-pagina">
    <div class="detalhe-topo">
        <div>
            <a href="index.php" class="detalhe-voltar">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar aos usuários
            </a>
            <h2 class="mb-1">Detalhes do usuário</h2>
        </div>

        <a href="edit.php?id=<?= (int)$usuario['Id'] ?>" class="btn btn-primary">
            <i class="fa fa-pencil" aria-hidden="true"></i> Editar usuário
        </a>
    </div>

    <div class="detalhe-grid">
        <aside class="detalhe-card detalhe-perfil">
            <div class="detalhe-avatar">
                <?= foto_html($usuario['Foto'] ?? null, 'foto-grande') ?>
            </div>
            <span class="detalhe-etiqueta">Usuário #<?= (int)$usuario['Id'] ?></span>
            <h3 class="detalhe-nome"><?= $nome ?></h3>
            <span class="detalhe-papel <?= $admin ? 'detalhe-papel-admin' : '' ?>">
                <i class="fa <?= $admin ? 'fa-shield' : 'fa-user' ?>" aria-hidden="true"></i>
                <?= $admin ? 'Administrador' : 'Usuário comum' ?>
            </span>
            <?php if ($proprio): ?>
                <p class="detalhe-perfil-legenda mt-2">Esta é sua conta</p>
            <?php endif; ?>
        </aside>

        <div class="detalhe-card detalhe-informacoes">
            <div class="detalhe-titulo-secao">
                <div>
                    <h3>Informações da conta</h3>
                </div>
            </div>

            <dl class="detalhe-campos">
                <div class="detalhe-campo">
                    <dt><i class="fa fa-user" aria-hidden="true"></i> Nome</dt>
                    <dd><?= $nome ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-envelope-o" aria-hidden="true"></i> E-mail de acesso</dt>
                    <dd><?= $email ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-user" aria-hidden="true"></i> Nome de usuário</dt>
                    <dd><?= $username ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-shield" aria-hidden="true"></i> Perfil</dt>
                    <dd><?= $admin ? 'Administrador' : 'Usuário comum' ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-lock" aria-hidden="true"></i> Senha</dt>
                    <dd>Protegida e não exibida por segurança</dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-calendar" aria-hidden="true"></i> Data de cadastro</dt>
                    <dd><?= htmlspecialchars($dataCadastro, ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<?php include FOOTER_TEMPLATE; ?>
