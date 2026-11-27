<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../inc/auth.php';
$jogos = find_all('Jogos') ?? [];
include HEADER_TEMPLATE;
?>
<section class="catalogo-pagina">
    <div class="catalogo-topo">
        <div>
            <h1>Jogos</h1>
        </div>
        <?php if (is_logged_in()): ?>
            <a href="add.php" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> Cadastrar jogo</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['type'] ?? 'info', ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars((string)$_SESSION['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['type']); ?>
    <?php endif; ?>

    <div class="clientes-barra mb-3">
        <label for="pesquisarJogo" class="visually-hidden">Pesquisar jogos</label>
        <i class="fa fa-search" aria-hidden="true"></i>
        <input type="search" id="pesquisarJogo" class="form-control" placeholder="Pesquisar por nome, plataforma ou gênero..." autocomplete="off">
    </div>
    <p class="clientes-contagem"><span id="jogosContagem"><?= count($jogos) ?></span> jogo(s) no catálogo</p>

    <div class="jogos-grade" id="listaJogos">
        <?php foreach ($jogos as $jogo): ?>
            <?php
            $id = (int)$jogo['Id'];
            $nome = htmlspecialchars($jogo['Nome'], ENT_QUOTES, 'UTF-8');
            $plataforma = htmlspecialchars($jogo['Plataforma'], ENT_QUOTES, 'UTF-8');
            $genero = htmlspecialchars($jogo['Genero'], ENT_QUOTES, 'UTF-8');
            $foto = jogo_url_foto($jogo['Foto'] ?? null);
            ?>
            <article class="jogo-card" data-jogo>
                <a class="jogo-capa" href="view.php?id=<?= $id ?>" aria-label="Ver <?= $nome ?>">
                    <?php if ($foto): ?>
                        <img src="<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') ?>" alt="Capa de <?= $nome ?>" loading="lazy">
                    <?php else: ?>
                        <span class="jogo-sem-capa" aria-label="Capa não cadastrada"><i class="fa fa-gamepad" aria-hidden="true"></i><small>NO COVER</small></span>
                    <?php endif; ?>
                </a>
                <div class="jogo-card-conteudo">
                    <p class="jogo-plataforma"><?= $plataforma ?></p>
                    <h2><a href="view.php?id=<?= $id ?>"><?= $nome ?></a></h2>
                    <p class="jogo-genero"><?= $genero ?> <span>·</span> <?= (int)$jogo['AnoLancamento'] ?></p>
                    <div class="jogo-card-rodape">
                        <strong>R$ <?= number_format((float)$jogo['Preco'], 2, ',', '.') ?></strong>
                        <span><?= (int)$jogo['Estoque'] ?> em estoque</span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <p id="jogosVazio" class="text-center text-muted py-5" <?= $jogos ? 'hidden' : '' ?>>
        <?= $jogos ? 'Nenhum jogo corresponde à pesquisa.' : 'O catálogo ainda não possui jogos.' ?>
    </p>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const busca = document.getElementById('pesquisarJogo');
    const itens = Array.from(document.querySelectorAll('[data-jogo]'));
    const contagem = document.getElementById('jogosContagem');
    const vazio = document.getElementById('jogosVazio');
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
        vazio.textContent = itens.length ? 'Nenhum jogo corresponde à pesquisa.' : 'O catálogo ainda não possui jogos.';
    });
});
</script>
<?php include FOOTER_TEMPLATE; ?>
