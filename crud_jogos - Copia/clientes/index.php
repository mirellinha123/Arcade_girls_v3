<?php
require_once __DIR__ . '/../config.php';
require_once DBAPI;
require_once __DIR__ . '/../inc/auth.php';
$clientes = find_all('Clientes') ?? [];
include HEADER_TEMPLATE;
?>
<section class="clientes-painel">
    <div class="catalogo-topo">
        <div>
            <h1>Clientes</h1>
        </div>
        <?php if (is_logged_in()): ?>
            <a href="add.php" class="btn btn-primary"><i class="fa fa-user-plus" aria-hidden="true"></i> Cadastrar cliente</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>" role="alert">
            <?= htmlspecialchars((string)$_SESSION['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['type']); ?>
    <?php endif; ?>

    <div class="clientes-barra mb-3">
        <label for="pesquisarCliente" class="visually-hidden">Pesquisar clientes</label>
        <i class="fa fa-search" aria-hidden="true"></i>
        <input type="search" id="pesquisarCliente" class="form-control" placeholder="Pesquisar por nome, e-mail, telefone ou CPF..." autocomplete="off">
    </div>
    <p class="clientes-contagem"><span id="clientesContagem"><?= count($clientes) ?></span> cliente(s) encontrado(s)</p>

    <div class="table-responsive arcade-table-wrap">
        <table class="table arcade-table align-middle">
            <thead><tr><th>ID</th><th>Nome completo</th><th>E-mail</th><th>Telefone</th><th>CPF</th><th>Data de nascimento</th><th class="table-actions">Ações</th></tr></thead>
            <tbody id="listaClientes">
                <?php foreach ($clientes as $cliente): ?>
                    <?php
                    $id = (int)$cliente['Id'];
                    $nome = htmlspecialchars((string)$cliente['Nome'], ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr data-cliente>
                        <td><?= $id ?></td>
                        <td><a class="table-primary-link" href="view.php?id=<?= $id ?>"><?= $nome ?></a></td>
                        <td><?= htmlspecialchars((string)($cliente['Email'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)($cliente['Telefone'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string)($cliente['CPF'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= !empty($cliente['DataNascimento']) ? htmlspecialchars(date('d/m/Y', strtotime($cliente['DataNascimento'])), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                        <td class="table-actions">
                            <a class="btn btn-sm btn-outline-primary" href="view.php?id=<?= $id ?>" aria-label="Visualizar <?= $nome ?>"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            <?php if (is_logged_in()): ?>
                                <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?= $id ?>" aria-label="Editar <?= $nome ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                <button class="btn btn-sm btn-outline-danger" type="button" aria-label="Excluir <?= $nome ?>"
                                    data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="<?= $id ?>" data-nome="<?= $nome ?>">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p id="clientesVazio" class="text-center text-muted py-5" <?= $clientes ? 'hidden' : '' ?>>
        <?= $clientes ? 'Nenhum cliente corresponde à pesquisa.' : 'Nenhum cliente cadastrado.' ?>
    </p>
</section>
<?php if (is_logged_in()) include __DIR__ . '/modal.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const busca = document.getElementById('pesquisarCliente');
    const itens = Array.from(document.querySelectorAll('[data-cliente]'));
    const contagem = document.getElementById('clientesContagem');
    const vazio = document.getElementById('clientesVazio');
    const normalizar = texto => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    busca.addEventListener('input', function () {
        const termo = normalizar(busca.value.trim());
        let visiveis = 0;
        itens.forEach(item => {
            const corresponde = normalizar(item.textContent).includes(termo);
            item.hidden = !corresponde;
            if (corresponde) visiveis++;
        });
        contagem.textContent = visiveis;
        vazio.hidden = visiveis !== 0;
        vazio.textContent = itens.length ? 'Nenhum cliente corresponde à pesquisa.' : 'Nenhum cliente cadastrado.';
    });
});
</script>
<?php include FOOTER_TEMPLATE; ?>
