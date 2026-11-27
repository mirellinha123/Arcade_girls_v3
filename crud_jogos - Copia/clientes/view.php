<?php
require_once __DIR__ . '/../inc/auth.php';
require_once DBAPI;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
$cliente = $id ? find('Clientes', $id) : null;

if (!$cliente) {
    db_message('Cliente não encontrado.', 'warning');
    header('Location: ' . BASEURL . 'clientes/index.php');
    exit;
}

$nome = htmlspecialchars((string)($cliente['Nome'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string)($cliente['Email'] ?: 'Não informado'), ENT_QUOTES, 'UTF-8');
$telefone = htmlspecialchars((string)($cliente['Telefone'] ?: 'Não informado'), ENT_QUOTES, 'UTF-8');
$cpf = htmlspecialchars((string)($cliente['CPF'] ?: 'Não informado'), ENT_QUOTES, 'UTF-8');
$dataNascimentoTimestamp = !empty($cliente['DataNascimento']) ? strtotime((string)$cliente['DataNascimento']) : false;
$dataNascimento = $dataNascimentoTimestamp ? date('d/m/Y', $dataNascimentoTimestamp) : 'Não informada';
$dataTimestamp = !empty($cliente['DataCad']) ? strtotime((string)$cliente['DataCad']) : false;
$dataCadastro = $dataTimestamp ? date('d/m/Y H:i', $dataTimestamp) : 'Não informada';

include HEADER_TEMPLATE;
?>

<section class="detalhe-pagina">
    <div class="cliente-detalhe-topo">
        <div>
            <a href="index.php" class="detalhe-voltar">
                <i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar aos clientes
            </a>
            <h1 class="cliente-detalhe-nome"><?= $nome ?></h1>
        </div>
        <?php if (is_logged_in()): ?>
            <div class="cliente-detalhe-acoes detalhe-acoes">
                <a href="edit.php?id=<?= (int)$cliente['Id'] ?>" class="btn btn-primary">
                    <i class="fa fa-pencil" aria-hidden="true"></i> Editar cliente
                </a>
                <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal"
                    data-bs-target="#delete-modal" data-id="<?= (int)$cliente['Id'] ?>" data-nome="<?= $nome ?>">
                    <i class="fa fa-trash" aria-hidden="true"></i> Excluir
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="detalhe-card cliente-detalhe-informacoes">
            <div class="detalhe-titulo-secao">
                <div>
                    <h3>Informações pessoais</h3>
                </div>
            </div>

            <dl class="detalhe-campos">
                <div class="detalhe-campo">
                    <dt><i class="fa fa-user" aria-hidden="true"></i> Nome</dt>
                    <dd><?= $nome ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-envelope-o" aria-hidden="true"></i> E-mail</dt>
                    <dd><?= $email ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-phone" aria-hidden="true"></i> Telefone</dt>
                    <dd><?= $telefone ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-id-card-o" aria-hidden="true"></i> CPF</dt>
                    <dd><?= $cpf ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-calendar" aria-hidden="true"></i> Data de nascimento</dt>
                    <dd><?= htmlspecialchars($dataNascimento, ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
                <div class="detalhe-campo">
                    <dt><i class="fa fa-calendar" aria-hidden="true"></i> Data do cadastro</dt>
                    <dd><?= htmlspecialchars($dataCadastro, ENT_QUOTES, 'UTF-8') ?></dd>
                </div>
            </dl>
    </div>
</section>

<?php if (is_logged_in()) include __DIR__ . '/modal.php'; ?>
<?php include FOOTER_TEMPLATE; ?>
