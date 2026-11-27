<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_once DBAPI;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Formulário inválido.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if ($id && find('Clientes', $id)) {
    remove('Clientes', $id);
} else {
    db_message('Cliente não encontrado.', 'warning');
}

header('Location: ' . BASEURL . 'clientes/index.php');
exit;
