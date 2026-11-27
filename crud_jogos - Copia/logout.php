<?php
require_once __DIR__ . '/inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASEURL . 'login.php');
    exit;
}

if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Formulário inválido.');
}

logout_user();

header('Location: ' . BASEURL . 'index.php');
exit;
