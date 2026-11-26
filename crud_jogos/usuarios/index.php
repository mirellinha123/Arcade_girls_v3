<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_once DBAPI;

$usuarios = [];
$erro = '';
$db = open_database();
try {
    if (!$db) {
        throw new RuntimeException('Sem conexão com o banco.');
    }
    $resultado = $db->query('SELECT Id, Nome, Email, Username, Perfil, DataCad FROM Usuarios ORDER BY Nome, Id');
    $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    error_log('Erro ao listar usuários: ' . $e->getMessage());
    $erro = 'Não foi possível consultar os usuários.';
} finally {
    close_database($db);
}

include HEADER_TEMPLATE;
?>
<section class="clientes-painel usuarios-pagina">
    <div class="catalogo-topo">
        <div><h1>Usuários</h1></div>
        <a href="add.php" class="btn btn-primary"><i class="fa fa-user-plus" aria-hidden="true"></i> Cadastrar usuário</a>
    </div>
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>" role="alert"><?= htmlspecialchars((string)$_SESSION['message'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php unset($_SESSION['message'], $_SESSION['type']); ?>
    <?php endif; ?>
    <?php if ($erro): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
    <?php else: ?>
        <div class="clientes-barra mb-3"><label for="pesquisarUsuario" class="visually-hidden">Pesquisar usuários</label><i class="fa fa-search" aria-hidden="true"></i><input id="pesquisarUsuario" type="search" class="form-control" placeholder="Pesquisar por nome, usuário, e-mail ou perfil..." autocomplete="off"></div>
        <p class="clientes-contagem"><span id="usuariosContagem"><?= count($usuarios) ?></span> usuário(s)</p>
        <div class="table-responsive arcade-table-wrap">
            <table class="table arcade-table align-middle">
                <thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Usuário</th><th>Perfil</th><th>Cadastro</th><th class="table-actions">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                        $id = (int)$usuario['Id'];
                        $nome = htmlspecialchars($usuario['Nome'], ENT_QUOTES, 'UTF-8');
                        $proprio = $id === (int)current_user()['id'];
                        ?>
                        <tr data-usuario>
                            <td><?= $id ?></td>
                            <td><a class="table-primary-link" href="view.php?id=<?= $id ?>"><?= $nome ?></a><?= $proprio ? ' <span class="badge text-bg-secondary">Sua conta</span>' : '' ?></td>
                            <td><?= htmlspecialchars($usuario['Email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($usuario['Username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= $usuario['Perfil'] === 'admin' ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= $usuario['Perfil'] === 'admin' ? 'Administrador' : 'Usuário comum' ?></span></td>
                            <td><?= !empty($usuario['DataCad']) ? htmlspecialchars(date('d/m/Y', strtotime($usuario['DataCad'])), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                            <td class="table-actions">
                                <a class="btn btn-sm btn-outline-primary" href="view.php?id=<?= $id ?>" aria-label="Visualizar <?= $nome ?>"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                <a class="btn btn-sm btn-outline-secondary" href="edit.php?id=<?= $id ?>" aria-label="Editar <?= $nome ?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                                <?php if (!$proprio): ?>
                                    <button class="btn btn-sm btn-outline-danger" type="button" aria-label="Excluir <?= $nome ?>"
                                        data-bs-toggle="modal" data-bs-target="#delete-modal" data-id="<?= $id ?>" data-nome="<?= $nome ?>">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-danger" type="button" disabled title="Sua própria conta não pode ser excluída" aria-label="Sua própria conta não pode ser excluída"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p id="usuariosVazio" class="text-center text-muted py-5" <?= $usuarios ? 'hidden' : '' ?>><?= $usuarios ? 'Nenhum usuário corresponde à pesquisa.' : 'Nenhum usuário cadastrado.' ?></p>
    <?php endif; ?>
</section>
<?php if (!$erro): include __DIR__ . '/modal.php'; endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const busca = document.getElementById('pesquisarUsuario');
    if (!busca) return;
    const itens = Array.from(document.querySelectorAll('[data-usuario]'));
    const contagem = document.getElementById('usuariosContagem');
    const vazio = document.getElementById('usuariosVazio');
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
    });
});
</script>
<?php include FOOTER_TEMPLATE; ?>
