<?php
require_once __DIR__ . '/../config.php';

// Devolve os dados do usuario logado ou null, para visitantes.
function current_user() {
    return $_SESSION['usuario'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['usuario']['id']);
}

function is_admin() {
    return is_logged_in() && ($_SESSION['usuario']['perfil'] === 'admin');
}

// Chamar apenas DEPOIS de conferir email e senha com password_verify().
function login_user($usuario) {
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id' => (int)$usuario['Id'],
        'nome' => $usuario['Nome'],
        'perfil' => $usuario['Perfil'],
        'foto' => $usuario['Foto'] ?? null,
        'username' => $usuario['Username'] ?? '',
    ];
}

function logout_user() {
    unset($_SESSION['usuario']);
    session_regenerate_id(true);
}

// Usar nas paginas que alteram registros, depois de criar login.php.
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASEURL . 'login.php');
        exit;
    }

    require_once DBAPI;
    $db = open_database();
    if (!$db) {
        http_response_code(503);
        exit('Não foi possível validar sua sessão no banco de dados.');
    }

    try {
        $stmt = db_execute($db, 'SELECT Id, Nome, Perfil, Foto, Username FROM Usuarios WHERE Id = ? LIMIT 1', [(int)$_SESSION['usuario']['id']]);
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } finally {
        close_database($db);
    }

    if (!$usuario) {
        logout_user();
        header('Location: ' . BASEURL . 'login.php');
        exit;
    }

    $_SESSION['usuario'] = [
        'id' => (int)$usuario['Id'],
        'nome' => $usuario['Nome'],
        'perfil' => $usuario['Perfil'],
        'foto' => $usuario['Foto'] ?? null,
        'username' => $usuario['Username'],
    ];
}

// Usar nas paginas de gerenciamento dos usuarios.
function require_admin() {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Acesso permitido apenas ao administrador.');
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_valid($token) {
    return is_string($token) &&
        hash_equals(csrf_token(), $token);
}
