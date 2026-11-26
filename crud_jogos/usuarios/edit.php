<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$proprio = $id !== false && $id === (int)current_user()['id'];
if (!is_admin() && !$proprio) {
    http_response_code(403);
    exit('Acesso permitido apenas à própria conta ou ao administrador.');
}
require_once DBAPI;
require_once __DIR__ . '/../inc/fotos.php';

$usuario = $id ? find('usuarios', $id) : null;
if (!$usuario) {
    db_message('Usuário não encontrado.', 'danger');
    header('Location: index.php');
    exit;
}
$nome = $usuario['Nome'];
$email = $usuario['Email'];
$username = $usuario['Username'];
$perfil = $usuario['Perfil'];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }
    $nome = trim((string)($_POST['nome'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $username = strtolower(trim((string)($_POST['username'] ?? '')));
    $perfil = $proprio ? (string)$usuario['Perfil'] : (string)($_POST['perfil'] ?? '');
    $senha = (string)($_POST['senha'] ?? '');
    $confirmacao = (string)($_POST['confirmacao'] ?? '');
    if ($nome === '' || strlen($nome) > 100) {
        $erro = 'Informe um nome válido.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $erro = 'Informe um e-mail válido.';
    } elseif (!preg_match('/\A[a-z0-9._-]{3,40}\z/', $username)) {
        $erro = 'Informe um nome de usuário válido (3 a 40 caracteres).';
    } elseif (!in_array($perfil, ['usuario', 'admin'], true)) {
        $erro = 'Selecione um perfil válido.';
    } elseif ($senha !== '' && (strlen($senha) < 8 || strlen($senha) > 72 || strpos($senha, "\0") !== false)) {
        $erro = 'A nova senha deve ter de 8 a 72 bytes.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } else {
        $novaFoto = null;
        $db = open_database();
        try {
            if (!$db) {
                throw new RuntimeException('Sem conexão.');
            }
            if ($usuario['Perfil'] === 'admin' && $perfil !== 'admin') {
                $stmt = $db->query("SELECT COUNT(*) AS Total FROM Usuarios WHERE Perfil = 'admin'");
                $totalAdmins = (int)$stmt->fetch_assoc()['Total'];
                $stmt->close();
                if ($totalAdmins <= 1) {
                    throw new RuntimeException('Não é possível remover o último administrador do sistema.');
                }
            }
            $novaFoto = salvar_foto($_FILES['foto'] ?? null, 'usr');
            $sql = 'UPDATE Usuarios SET Nome = ?, Email = ?, Username = ?, Perfil = ?';
            $valores = [$nome, $email, $username, $perfil];
            if ($senha !== '') {
                $sql .= ', Senha = ?';
                $valores[] = password_hash($senha, PASSWORD_DEFAULT);
            }
            if ($novaFoto !== null) {
                $sql .= ', Foto = ?';
                $valores[] = $novaFoto;
            }
            $sql .= ' WHERE Id = ?';
            $valores[] = $id;
            $stmt = db_execute($db, $sql, $valores);
            $stmt->close();

            if ($proprio) {
                $_SESSION['usuario']['nome'] = $nome;
                $_SESSION['usuario']['username'] = $username;
                if ($novaFoto !== null) {
                    $_SESSION['usuario']['foto'] = $novaFoto;
                }
            }
            if ($novaFoto !== null) {
                apagar_foto($usuario['Foto'] ?? null);
            }
            db_message('Usuário atualizado com sucesso.', 'success');
            $destino = $proprio && !is_admin()
                ? BASEURL . 'minha_conta.php'
                : BASEURL . 'usuarios/index.php';
            header('Location: ' . $destino);
            exit;
        } catch (Throwable $e) {
            apagar_foto($novaFoto);
            if ($e instanceof RuntimeException) {
                $erro = $e->getMessage();
            } elseif ($e instanceof mysqli_sql_exception && (int)$e->getCode() === 1062) {
                $erro = 'Este e-mail ou nome de usuário já está cadastrado.';
            } else {
                error_log('Erro ao editar usuário: ' . $e->getMessage());
                $erro = 'Não foi possível atualizar o usuário.';
            }
        } finally {
            close_database($db);
        }
    }
}
$urlVoltar = $proprio && !is_admin()
    ? BASEURL . 'minha_conta.php'
    : BASEURL . 'usuarios/index.php';
include HEADER_TEMPLATE;
?>
<section class="usuarios-pagina usuarios-form-pagina">
<h2>Editar Usuário</h2>
<hr>
<div class="card shadow-sm"><div class="card-body">
    <?php if ($erro): ?><div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" action="edit.php?id=<?= $id ?>" enctype="multipart/form-data" class="usuario-edit-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3"><label for="nome" class="form-label">Nome</label><input id="nome" name="nome" type="text" maxlength="100" class="form-control" required value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="mb-3"><label for="email" class="form-label">E-mail</label><input id="email" name="email" type="email" maxlength="150" class="form-control" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="mb-3"><label for="username" class="form-label">Nome de usuário</label><input id="username" name="username" type="text" minlength="3" maxlength="40" pattern="[A-Za-z0-9._-]+" class="form-control" required value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="mb-3"><label for="perfil" class="form-label">Perfil</label>
            <select id="perfil" name="perfil" class="form-select" <?= $proprio ? 'disabled' : '' ?>>
                <option value="usuario" <?= $perfil === 'usuario' ? 'selected' : '' ?>>Usuário comum</option>
                <option value="admin" <?= $perfil === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
            <?php if ($proprio): ?><small class="text-muted">Você não pode remover seu próprio acesso administrativo.</small><?php endif; ?>
        </div>
        <div class="mb-3"><label for="senha" class="form-label">Nova senha (opcional)</label><input id="senha" name="senha" type="password" class="form-control" minlength="8" maxlength="72" autocomplete="new-password"><small class="text-muted">Deixe em branco para manter a senha atual.</small></div>
        <div class="mb-3"><label for="confirmacao" class="form-label">Confirmar nova senha</label><input id="confirmacao" name="confirmacao" type="password" class="form-control" maxlength="72" autocomplete="new-password"></div>
        <div class="usuario-avatar-editor">
            <div id="fotoAtual"><?= foto_html($usuario['Foto'] ?? null, 'foto-grande') ?></div>
            <img id="previewFoto" class="foto-grande" alt="Prévia da nova foto" style="display:none;">
        </div>
        <div class="mb-3 usuario-upload-campo">
            <label for="foto" class="form-label">Foto de perfil (opcional)</label>
            <input type="file" id="foto" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">Deixe em branco para manter a foto atual. Máximo 2 MB.</small>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Salvar</button>
            <a href="<?= htmlspecialchars($urlVoltar, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div></div>
</section>
<?php include FOOTER_TEMPLATE; ?>
