<section class="form-pagina">
    <a class="detalhe-voltar" href="<?= BASEURL ?>jogos/index.php"><i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar ao catálogo</a>
    <h1><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" action="<?= htmlspecialchars($acao, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="card retro-form">
        <div class="card-body">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="row g-3 jogo-form-grid">
                <div class="col-lg-6"><label class="form-label" for="Nome">Nome do jogo</label><input class="form-control" id="Nome" name="Nome" maxlength="150" required value="<?= htmlspecialchars((string)($jogo['Nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="Plataforma">Plataforma</label><input class="form-control" id="Plataforma" name="Plataforma" maxlength="80" required placeholder="ex.: Super Nintendo" value="<?= htmlspecialchars((string)($jogo['Plataforma'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="Genero">Gênero</label><input class="form-control" id="Genero" name="Genero" maxlength="80" required value="<?= htmlspecialchars((string)($jogo['Genero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="AnoLancamento">Ano de lançamento</label><input class="form-control" id="AnoLancamento" name="AnoLancamento" type="number" min="1950" max="<?= (int)date('Y') + 1 ?>" required value="<?= htmlspecialchars((string)($jogo['AnoLancamento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="Preco">Preço (R$)</label><input class="form-control" id="Preco" name="Preco" type="number" min="0" max="99999999.99" step="0.01" required value="<?= htmlspecialchars((string)($jogo['Preco'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="Estoque">Quantidade em estoque</label><input class="form-control" id="Estoque" name="Estoque" type="number" min="0" step="1" required value="<?= htmlspecialchars((string)($jogo['Estoque'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="ClassificacaoIndicativa">Classificação indicativa</label><input class="form-control" id="ClassificacaoIndicativa" name="ClassificacaoIndicativa" maxlength="40" required placeholder="ex.: Livre" value="<?= htmlspecialchars((string)($jogo['ClassificacaoIndicativa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="DataEntrada">Data de entrada na loja</label><input class="form-control" id="DataEntrada" name="DataEntrada" type="date" required value="<?= htmlspecialchars((string)($jogo['DataEntrada'] ?? date('Y-m-d')), ENT_QUOTES, 'UTF-8') ?>"></div>
                <div class="col-lg-6"><label class="form-label" for="EstadoConservacao">Estado de conservação</label><select class="form-select" id="EstadoConservacao" name="EstadoConservacao" required><?php foreach (['Novo', 'Excelente', 'Muito bom', 'Bom', 'Regular'] as $estado): ?><option value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>" <?= ($jogo['EstadoConservacao'] ?? 'Bom') === $estado ? 'selected' : '' ?>><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
                <div class="col-lg-6"><label class="form-label" for="TipoMidia">Tipo de mídia</label><select class="form-select" id="TipoMidia" name="TipoMidia" required><?php foreach (['Cartucho', 'Disco', 'Digital', 'Outro'] as $tipo): ?><option value="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>" <?= ($jogo['TipoMidia'] ?? 'Cartucho') === $tipo ? 'selected' : '' ?>><?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
                <div class="col-12"><label class="form-label" for="Descricao">Descrição completa</label><textarea class="form-control" id="Descricao" name="Descricao" rows="5" maxlength="10000" required><?= htmlspecialchars((string)($jogo['Descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                <div class="col-12">
                    <div class="upload-field">
                        <label class="form-label" for="Foto">Capa do jogo (opcional)</label>
                        <?php if ($fotoAtual = jogo_url_foto($jogo['Foto'] ?? null)): ?>
                            <div class="upload-preview"><img class="form-capa-preview" src="<?= htmlspecialchars($fotoAtual, ENT_QUOTES, 'UTF-8') ?>" alt="Capa atual"></div>
                        <?php else: ?>
                            <div class="upload-preview"><span class="jogo-sem-capa upload-placeholder"><i class="fa fa-gamepad" aria-hidden="true"></i><small>ARTE PADRÃO</small></span></div>
                        <?php endif; ?>
                        <input class="form-control" id="Foto" name="Foto" type="file" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, PNG ou WEBP; máximo de 2 MB. Sem capa, será exibida a arte padrão.</small>
                    </div>
                </div>
            </div>
            <div class="form-actions mt-4"><button class="btn btn-primary" type="submit"><i class="fa fa-save" aria-hidden="true"></i> Salvar jogo</button><a class="btn btn-outline-secondary" href="<?= BASEURL ?>jogos/index.php">Cancelar</a></div>
        </div>
    </form>
</section>
