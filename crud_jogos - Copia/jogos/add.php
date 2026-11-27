<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/functions.php';

$titulo = 'Cadastrar jogo';
$acao = 'add.php';
$erro = '';
$jogo = [
    'DataEntrada' => date('Y-m-d'),
    'Estoque' => 0,
    'EstadoConservacao' => 'Bom',
    'TipoMidia' => 'Cartucho',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }
    $jogo = array_merge($jogo, is_array($_POST) ? $_POST : []);
    $foto = null;
    try {
        $dados = validar_jogo($_POST);
        $foto = salvar_foto($_FILES['Foto'] ?? null, 'game');
        if ($foto !== null) {
            $dados['Foto'] = $foto;
        }
        $db = open_database();
        if (!$db) {
            throw new RuntimeException('Não foi possível conectar ao banco de dados.');
        }
        try {
            $stmt = db_execute($db, 'INSERT INTO Jogos (Nome, Plataforma, Genero, AnoLancamento, Preco, ClassificacaoIndicativa, DataEntrada, Estoque, EstadoConservacao, TipoMidia, Descricao, Foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $dados['Nome'], $dados['Plataforma'], $dados['Genero'], $dados['AnoLancamento'], $dados['Preco'], $dados['ClassificacaoIndicativa'], $dados['DataEntrada'], $dados['Estoque'], $dados['EstadoConservacao'], $dados['TipoMidia'], $dados['Descricao'], $foto
            ]);
            $stmt->close();
        } finally {
            close_database($db);
        }
        db_message('Jogo cadastrado com sucesso.', 'success');
        header('Location: ' . BASEURL . 'jogos/index.php');
        exit;
    } catch (Throwable $e) {
        apagar_foto($foto);
        if ($e instanceof InvalidArgumentException || $e instanceof RuntimeException) {
            $erro = $e->getMessage();
        } else {
            error_log('Erro ao cadastrar jogo: ' . $e->getMessage());
            $erro = 'Não foi possível cadastrar o jogo.';
        }
    }
}

include HEADER_TEMPLATE;
include __DIR__ . '/_form.php';
include FOOTER_TEMPLATE;
