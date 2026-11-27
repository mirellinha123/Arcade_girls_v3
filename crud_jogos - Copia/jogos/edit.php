<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$jogo = $id ? find('Jogos', $id) : null;
if (!$jogo) {
    db_message('Jogo não encontrado.', 'warning');
    header('Location: ' . BASEURL . 'jogos/index.php');
    exit;
}

$titulo = 'Editar jogo';
$acao = 'edit.php?id=' . (int)$id;
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }
    foreach (['Nome', 'Plataforma', 'Genero', 'AnoLancamento', 'Preco', 'ClassificacaoIndicativa', 'DataEntrada', 'Estoque', 'EstadoConservacao', 'TipoMidia', 'Descricao'] as $campo) {
        if (isset($_POST[$campo]) && is_string($_POST[$campo])) {
            $jogo[$campo] = $_POST[$campo];
        }
    }

    $novaFoto = null;
    try {
        $dados = validar_jogo($_POST);
        $novaFoto = salvar_foto($_FILES['Foto'] ?? null, 'game');
        if ($novaFoto !== null) {
            $dados['Foto'] = $novaFoto;
        }
        $set = implode(', ', array_map(static function ($coluna) {
            return "`$coluna` = ?";
        }, array_keys($dados)));
        $valores = array_values($dados);
        $valores[] = (int)$id;
        $db = open_database();
        if (!$db) {
            throw new RuntimeException('Não foi possível conectar ao banco de dados.');
        }
        try {
            $stmt = db_execute($db, "UPDATE Jogos SET $set WHERE Id = ?", $valores);
            $stmt->close();
        } finally {
            close_database($db);
        }
        if ($novaFoto !== null) {
            apagar_foto($jogo['Foto'] ?? null);
        }
        db_message('Jogo atualizado com sucesso.', 'success');
        header('Location: ' . BASEURL . 'jogos/view.php?id=' . (int)$id);
        exit;
    } catch (Throwable $e) {
        apagar_foto($novaFoto);
        if ($e instanceof InvalidArgumentException || $e instanceof RuntimeException) {
            $erro = $e->getMessage();
        } else {
            error_log('Erro ao editar jogo: ' . $e->getMessage());
            $erro = 'Não foi possível atualizar o jogo.';
        }
    }
}

include HEADER_TEMPLATE;
include __DIR__ . '/_form.php';
include FOOTER_TEMPLATE;
