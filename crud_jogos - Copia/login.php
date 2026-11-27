<?php
require_once __DIR__ . '/config.php';
require_once DBAPI;
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/fotos.php';

if (is_logged_in()) {
    header('Location: ' . BASEURL . 'index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }
    $login = trim((string) ($_POST['login'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($login === '' || $senha === '') {
        $erro = 'Preencha o usuário ou e-mail e a senha.';
    } else {
        $db = open_database();

        if (!$db) {
            $erro = 'Não foi possível conectar ao banco.';
        } else {
            try {
                $stmt = $db->prepare(
                    'SELECT Id, Nome, Username, Senha, Perfil, Foto
                     FROM Usuarios
                     WHERE Email = ? OR Username = ?
                     LIMIT 1'
                );

                $stmt->bind_param('ss', $login, $login);
                $stmt->execute();

                $usuario = $stmt->get_result()->fetch_assoc();
                $stmt->close();


                if ($usuario && password_verify($senha, $usuario['Senha'])) {

                    $lembrarFoto = isset($_POST['lembrar_foto'])
                        && !empty($usuario['Foto']);

                    setcookie('pw_foto_login', $lembrarFoto ? $usuario['Foto'] : '', [
                        'expires' => $lembrarFoto ? time() + 30 * 86400 : time() - 3600,
                        'path' => BASEURL,
                        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);

                    login_user($usuario);

                    header('Location: ' . BASEURL . 'index.php');
                    exit;
                }


                $erro = 'Usuário/e-mail ou senha inválidos.';

            } catch (Throwable $e) {
                error_log('Erro no login: ' . $e->getMessage());
                $erro = 'Ocorreu um erro ao entrar.';
            } finally {
                close_database($db);
            }
        }
    }
}


$fotoLembrada = $_COOKIE['pw_foto_login'] ?? null;

if (
    !is_string($fotoLembrada)
    || !preg_match('/^usr_[a-f0-9]{24}\.(jpg|png|webp)$/', $fotoLembrada)
    || !is_file(ABSPATH . 'uploads/' . $fotoLembrada)
) {
    $fotoLembrada = null;
}

include HEADER_TEMPLATE;
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="auth-user-icon">
                    <?= foto_html($fotoLembrada, 'foto-grande') ?>
                </div>
                <h2 class="text-center mb-4">Entrar no Arcade</h2>
                <?php if (!empty($_SESSION['message'])): ?>
                    <div class="alert alert-<?= htmlspecialchars($_SESSION['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars((string)$_SESSION['message'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <?php unset($_SESSION['message'], $_SESSION['type']); ?>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo BASEURL; ?>login.php">
                    <input type="hidden" name="csrf_token"
                        value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="mb-3">
                        <label for="login" class="form-label">Usuário ou e-mail</label>
                        <input type="text" name="login" id="login" class="form-control" autocomplete="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" name="senha" id="senha" class="form-control"
                            autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        Entrar
                    </button>
                </form>
                <p class="text-center mt-3 mb-0">Ainda não tem conta? <a href="<?= BASEURL ?>cadastro.php">Criar conta</a></p>
            </div>
        </div>
    </div>
</div>

<?php include FOOTER_TEMPLATE; ?>