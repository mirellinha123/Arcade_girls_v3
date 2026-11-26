<?php
require_once __DIR__ . '/config.php';
require_once DBAPI;
require_once __DIR__ . '/inc/auth.php';

if (is_logged_in()) {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

$erro = '';
$nome = '';
$email = '';
$username = '';

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

    if ($nome === '' || strlen($nome) > 100) {
        $erro = 'Informe seu nome completo (até 100 caracteres).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $erro = 'Informe um e-mail válido.';
    } elseif (!preg_match('/\A[a-z0-9._-]{3,40}\z/', $username)) {
        $erro = 'O usuário deve ter de 3 a 40 caracteres: letras, números, ponto, hífen ou sublinhado.';
    } elseif (strlen($senha) < 8 || strlen($senha) > 72 || strpos($senha, "\0") !== false) {
        $erro = 'A senha deve ter de 8 a 72 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } else {
        $db = open_database();
        try {
            if (!$db) {
                throw new RuntimeException('Não foi possível conectar ao banco de dados.');
            }
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = db_execute(
                $db,
                "INSERT INTO Usuarios (Nome, Email, Username, Senha, Perfil) VALUES (?, ?, ?, ?, 'usuario')",
                [$nome, $email, $username, $hash]
            );
            $stmt->close();
            db_message('Conta criada. Entre com seu usuário ou e-mail.', 'success');
            header('Location: ' . BASEURL . 'login.php');
            exit;
        } catch (Throwable $e) {
            if ($e instanceof mysqli_sql_exception && (int)$e->getCode() === 1062) {
                $erro = 'Este e-mail ou nome de usuário já está em uso.';
            } elseif ($e instanceof RuntimeException) {
                $erro = $e->getMessage();
            } else {
                error_log('Erro ao criar conta: ' . $e->getMessage());
                $erro = 'Não foi possível criar a conta.';
            }
        } finally {
            close_database($db);
        }
    }
}

include HEADER_TEMPLATE;
?>
<section class="auth-panel">
    <div class="auth-user-icon">
        <?= foto_html(null, 'foto-grande') ?>
    </div>
    <h1 class="text-center">Criar conta</h1>
    <p class="text-center text-muted">Novas contas recebem acesso de usuário comum.</p>
    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" action="<?= BASEURL ?>cadastro.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome completo</label>
            <input id="nome" name="nome" class="form-control" maxlength="100" autocomplete="name" required value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input id="email" type="email" name="email" class="form-control" maxlength="150" autocomplete="email" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Nome de usuário</label>
            <input id="username" name="username" class="form-control" minlength="3" maxlength="40" pattern="[A-Za-z0-9._-]+" autocomplete="username" required value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input id="senha" type="password" name="senha" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
        </div>
        <div class="mb-4">
            <label for="confirmacao" class="form-label">Confirmar senha</label>
            <input id="confirmacao" type="password" name="confirmacao" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Criar conta</button>
    </form>
    <p class="text-center mt-3 mb-0"><a href="<?= BASEURL ?>login.php">Voltar ao login</a></p>
</section>
<?php include FOOTER_TEMPLATE; ?>
