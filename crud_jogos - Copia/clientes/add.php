<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once DBAPI;
require_once __DIR__ . '/../inc/validators.php';

$erro = '';
$nome = '';
$email = '';
$telefone = '';
$cpf = '';
$dataNascimento = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $dataNascimento = trim($_POST['data_nascimento'] ?? '');

    if ($nome === '' || strlen($nome) > 120) {
        $erro = 'Informe um nome válido.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $erro = 'Informe um e-mail válido.';
    } elseif (!cpf_valido($cpf)) {
        $erro = 'Informe um CPF válido.';
    } elseif (!data_nascimento_valida($dataNascimento)) {
        $erro = 'Informe uma data de nascimento válida, que não seja futura.';
    } elseif (
        $telefone !== '' &&
        !preg_match('/^\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}$/', $telefone)
    ) {
        $erro = 'Informe um telefone válido com DDD.';
    } elseif (strlen($telefone) > 25) {
        $erro = 'O telefone é muito longo.';

    } else {
        try {
            $cliente = [
                'Nome' => $nome,
                'Email' => $email,
                'Telefone' => $telefone !== '' ? $telefone : null,
                'CPF' => cpf_formatado($cpf),
                'DataNascimento' => $dataNascimento !== '' ? $dataNascimento : null
            ];

            if (save('Clientes', $cliente)) {
                header('Location: ' . BASEURL . 'clientes/index.php');
                exit;
            }

            $erro = 'Não foi possível cadastrar o cliente. Verifique se o CPF já está cadastrado.';

        } catch (Throwable $e) {
            if ($e instanceof RuntimeException) {
                $erro = $e->getMessage();
            } else {
                error_log('Erro ao cadastrar cliente: ' . $e->getMessage());
                $erro = 'Não foi possível cadastrar o cliente.';
            }
        }
    }
}

include HEADER_TEMPLATE;
?>

<h2>Novo Cliente</h2>
<hr>

<div class="card shadow-sm">
    <div class="card-body">

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars(
                    $erro,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="add.php" class="cliente-formulario">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(
                csrf_token(),
                ENT_QUOTES,
                'UTF-8'
            ); ?>">

            <div class="mb-3">
                <label class="form-label" for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" class="form-control" maxlength="120" value="<?php echo htmlspecialchars(
                    $nome,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" maxlength="150" value="<?php echo htmlspecialchars(
                    $email,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>" required>
            </div>

            <div class="mb-3">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" name="cpf" id="cpf" class="form-control" maxlength="14" inputmode="numeric"
                    placeholder="000.000.000-00" pattern="([0-9]{11}|[0-9]{3}\.[0-9]{3}\.[0-9]{3}-[0-9]{2})"
                    value="<?php echo htmlspecialchars($cpf, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="telefone">Telefone</label>

                <input type="tel" name="telefone" id="telefone" class="form-control" maxlength="15" inputmode="numeric"
                    placeholder="(15) 99999-9999" pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}"
                    title="Informe um telefone com DDD."
                    value="<?php echo htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8'); ?>">

            </div>

            <div class="mb-3">
                <label class="form-label" for="data_nascimento">Data de nascimento</label>
                <input type="date" name="data_nascimento" id="data_nascimento" class="form-control"
                    max="<?php echo date('Y-m-d'); ?>"
                    value="<?php echo htmlspecialchars($dataNascimento, ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Salvar
                </button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>

        </form>
    </div>
</div>

<?php include FOOTER_TEMPLATE; ?>

<script src="<?php echo BASEURL; ?>js/functions.js"></script>