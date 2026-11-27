<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_once DBAPI;
require_once __DIR__ . '/../inc/fotos.php';

$erro = '';
$nome = '';
$email = '';
$username = '';
$perfil = 'usuario';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }

    $nome = trim((string)($_POST['nome'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $username = strtolower(trim((string)($_POST['username'] ?? '')));
    $senha = (string)($_POST['senha'] ?? '');
    $confirmacao = (string)($_POST['confirmacao'] ?? '');
    $perfil = (string)($_POST['perfil'] ?? 'usuario');

    if ($nome === '' || strlen($nome) > 100) {
        $erro = 'Informe um nome válido (até 100 bytes).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $erro = 'Informe um e-mail válido.';
    } elseif (!preg_match('/\A[a-z0-9._-]{3,40}\z/', $username)) {
        $erro = 'Informe um nome de usuário válido (3 a 40 caracteres).';
    } elseif (strlen($senha) < 8 || strlen($senha) > 72 || strpos($senha, "\0") !== false) {
        $erro = 'Informe uma senha válida de 8 a 72 bytes.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } elseif (!in_array($perfil, ['usuario', 'admin'], true)) {
        $erro = 'Selecione um perfil válido.';
    } else {
        $foto = null;
        $db = open_database();
        try {
            if (!$db) {
                throw new RuntimeException('Sem conexão.');
            }
            $foto = salvar_foto($_FILES['foto'] ?? null, 'usr');
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = db_execute($db, 'INSERT INTO Usuarios (Nome, Email, Username, Senha, Perfil, Foto) VALUES (?, ?, ?, ?, ?, ?)', [$nome, $email, $username, $hash, $perfil, $foto]);
            $stmt->close();
            db_message('Usuário cadastrado com sucesso.', 'success');
            header('Location: ' . BASEURL . 'usuarios/index.php');
            exit;
        } catch (Throwable $e) {
            apagar_foto($foto);
            if ($e instanceof RuntimeException) {
                $erro = $e->getMessage();
            } elseif ($e instanceof mysqli_sql_exception && (int)$e->getCode() === 1062) {
                $erro = 'Este e-mail ou nome de usuário já está cadastrado.';
            } else {
                error_log('Erro ao cadastrar usuário: ' . $e->getMessage());
                $erro = 'Não foi possível cadastrar o usuário.';
            }
        } finally {
            close_database($db);
        }
    }
}
include HEADER_TEMPLATE;
?>

<section class="usuarios-pagina usuarios-form-pagina">
<h2>Novo Usuário</h2>
<hr>
<div class="card shadow-sm">
    <div class="card-body">
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" action="add.php" enctype="multipart/form-data" class="usuario-create-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <input id="nome" type="text" name="nome" class="form-control" maxlength="100" value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input id="email" type="email" name="email" class="form-control" maxlength="150" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Nome de usuário</label>
                <input id="username" type="text" name="username" class="form-control" minlength="3" maxlength="40" pattern="[A-Za-z0-9._-]+" required value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input id="senha" type="password" name="senha" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
            </div>
            <div class="mb-3">
                <label for="confirmacao" class="form-label">Confirmar senha</label>
                <input id="confirmacao" type="password" name="confirmacao" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
            </div>
            <div class="mb-3">
                <label for="perfil" class="form-label">Perfil</label>
                <select id="perfil" name="perfil" class="form-select" required>
                    <option value="usuario" <?php echo $perfil === 'usuario' ? 'selected' : ''; ?>>Usuário comum</option>
                    <option value="admin" <?php echo $perfil === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto de perfil (opcional)</label>
                <input type="file" id="foto" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                <small class="text-muted">JPG, PNG ou WEBP, até 2 MB.</small>
                <div class="mt-3">
                    <img id="previewFoto" class="foto-grande" alt="Prévia da foto" style="display:none;">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</section>
<?php include FOOTER_TEMPLATE; ?>
