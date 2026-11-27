<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../inc/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$jogo = $id ? find('Jogos', $id) : null;
if (!$jogo) {
    db_message('Jogo não encontrado.', 'warning');
    header('Location: ' . BASEURL . 'jogos/index.php');
    exit;
}

$foto = jogo_url_foto($jogo['Foto'] ?? null);
$h = static function ($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
include HEADER_TEMPLATE;
?>
<section class="detalhe-pagina">
    <div class="detalhe-topo">
        <div>
            <a href="index.php" class="detalhe-voltar"><i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar ao catálogo</a>
            <h1><?= $h($jogo['Nome']) ?></h1>
        </div>
        <?php if (is_logged_in()): ?>
            <div class="detalhe-acoes">
                <a href="edit.php?id=<?= (int)$jogo['Id'] ?>" class="btn btn-primary"><i class="fa fa-pencil" aria-hidden="true"></i> Editar</a>
                <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal"
                    data-bs-target="#delete-modal" data-id="<?= (int)$jogo['Id'] ?>" data-nome="<?= $h($jogo['Nome']) ?>">
                    <i class="fa fa-trash" aria-hidden="true"></i> Excluir
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="jogo-detalhe">
        <div class="jogo-detalhe-capa">
            <?php if ($foto): ?>
                <img src="<?= $h($foto) ?>" alt="Capa de <?= $h($jogo['Nome']) ?>">
            <?php else: ?>
                <span class="jogo-sem-capa jogo-sem-capa-grande"><i class="fa fa-gamepad" aria-hidden="true"></i><small>NO COVER</small></span>
            <?php endif; ?>
        </div>
        <div class="detalhe-card detalhe-informacoes">
            <div class="detalhe-titulo-secao"><div><h2>Ficha do jogo</h2></div></div>
            <dl class="detalhe-campos">
                <div class="detalhe-campo"><dt><i class="fa fa-gamepad" aria-hidden="true"></i> Nome</dt><dd><?= $h($jogo['Nome']) ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-desktop" aria-hidden="true"></i> Plataforma</dt><dd><?= $h($jogo['Plataforma']) ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-money" aria-hidden="true"></i> Preço</dt><dd>R$ <?= number_format((float)$jogo['Preco'], 2, ',', '.') ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-tags" aria-hidden="true"></i> Gênero</dt><dd><?= $h($jogo['Genero']) ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-calendar" aria-hidden="true"></i> Ano de lançamento</dt><dd><?= (int)$jogo['AnoLancamento'] ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-child" aria-hidden="true"></i> Classificação indicativa</dt><dd><?= $h($jogo['ClassificacaoIndicativa']) ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-calendar-plus-o" aria-hidden="true"></i> Entrada na loja</dt><dd><?= $h(date('d/m/Y', strtotime($jogo['DataEntrada']))) ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-cubes" aria-hidden="true"></i> Estoque</dt><dd><?= (int)$jogo['Estoque'] ?> unidade(s)</dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-star" aria-hidden="true"></i> Conservação</dt><dd><?= $h($jogo['EstadoConservacao']) ?></dd></div>
                <div class="detalhe-campo"><dt><i class="fa fa-hdd-o" aria-hidden="true"></i> Tipo de mídia</dt><dd><?= $h($jogo['TipoMidia']) ?></dd></div>
                <div class="detalhe-campo detalhe-campo-largo"><dt><i class="fa fa-file-text-o" aria-hidden="true"></i> Descrição</dt><dd class="jogo-descricao"><?= nl2br($h($jogo['Descricao'])) ?></dd></div>
            </dl>
        </div>
    </div>
</section>
<?php if (is_logged_in()) include __DIR__ . '/modal.php'; ?>
<?php include FOOTER_TEMPLATE; ?>
