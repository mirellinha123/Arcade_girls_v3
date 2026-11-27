<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
require_once DBAPI;
require_once __DIR__ . '/inc/fotos.php';

$erro = '';
$id = (int)current_user()['id'];
$usuario = find('Usuarios', $id);
if (!$usuario) {
    http_response_code(404);
    exit('Não foi possível localizar sua conta.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }
    $novaFoto = null;
    $db = null;
    try {
        $novaFoto = salvar_foto($_FILES['foto'] ?? null, 'usr');
        if ($novaFoto === null) {
            throw new RuntimeException('Escolha uma foto para atualizar seu perfil.');
        }
        $db = open_database();
        if (!$db) throw new RuntimeException('Sem conexão com o banco.');
        $stmt = db_execute($db, 'UPDATE Usuarios SET Foto = ? WHERE Id = ?', [$novaFoto, $id]);
        $stmt->close();
        $_SESSION['usuario']['foto'] = $novaFoto;
        apagar_foto($usuario['Foto'] ?? null);
        db_message('Sua foto foi atualizada!', 'success');
        header('Location: ' . BASEURL . 'minha_conta.php');
        exit;
    } catch (Throwable $e) {
        apagar_foto($novaFoto);
        if ($e instanceof RuntimeException) {
            $erro = $e->getMessage();
        } else {
            error_log('Erro ao atualizar foto da conta: ' . $e->getMessage());
            $erro = 'Não foi possível atualizar sua foto.';
        }
    } finally {
        close_database($db);
    }
}
include HEADER_TEMPLATE;
?>
<div class="minha-conta-topo">
    <h2>Minha Conta</h2>
    <a href="<?= BASEURL ?>usuarios/edit.php?id=<?= $id ?>" class="btn btn-primary">
        <i class="fa fa-pencil" aria-hidden="true"></i> Editar usuário
    </a>
</div>
<hr>
<div class="card shadow-sm mb-4"><div class="card-body">
    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['type']); ?>
    <?php endif; ?>
    <dl class="row">
        <dt class="col-sm-3">Nome</dt><dd class="col-sm-9"><?= htmlspecialchars($usuario['Nome'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-3">E-mail</dt><dd class="col-sm-9"><?= htmlspecialchars($usuario['Email'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-3">Perfil</dt><dd class="col-sm-9"><?= $usuario['Perfil'] === 'admin' ? 'Administrador' : 'Usuário comum' ?></dd>
    </dl>
    <form method="post" enctype="multipart/form-data" action="<?= BASEURL ?>minha_conta.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="upload-field account-upload-field mb-3">
            <div class="upload-preview account-upload-preview">
                <div id="fotoAtual"><?= foto_html($usuario['Foto'] ?? null, 'foto-grande') ?></div>
                <img id="previewFoto" class="foto-grande" alt="Prévia da nova foto" style="display:none;">
            </div>
            <div class="account-upload-content">
                <label for="foto" class="form-label">Trocar minha foto de perfil</label>
                <input type="file" id="foto" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                <small class="text-muted">JPG, PNG ou WEBP, até 2 MB.</small>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Salvar foto</button>
            <a href="<?= BASEURL ?>index.php" class="btn btn-secondary">Voltar</a>
        </div>
    </form>
</div></div>
<?php include FOOTER_TEMPLATE; ?>
