<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}
if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Formulário inválido.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$jogo = $id ? find('Jogos', $id) : null;
if (!$jogo) {
    db_message('Jogo não encontrado.', 'warning');
} elseif (remove('Jogos', $id)) {
    apagar_foto($jogo['Foto'] ?? null);
}
header('Location: ' . BASEURL . 'jogos/index.php');
exit;
