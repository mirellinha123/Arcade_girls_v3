<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_once DBAPI;
require_once __DIR__ . '/../inc/fotos.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}
if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Formulário inválido.');
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id || $id === (int)current_user()['id']) {
    db_message('Você não pode excluir sua própria conta ou um ID inválido.', 'danger');
} else {
    $alvo = find('usuarios', $id);
    if ($alvo && $alvo['Perfil'] === 'admin') {
        $db = open_database();
        try {
            if (!$db) {
                throw new RuntimeException('Não foi possível conectar ao banco de dados.');
            }
            $resultado = $db->query("SELECT COUNT(*) AS Total FROM Usuarios WHERE Perfil = 'admin'");
            $totalAdmins = (int)$resultado->fetch_assoc()['Total'];
            if ($totalAdmins <= 1) {
                db_message('Não é possível excluir o último administrador do sistema.', 'danger');
            } elseif (remove('usuarios', $id)) {
                apagar_foto($alvo['Foto'] ?? null);
            }
        } catch (Throwable $e) {
            error_log('Erro ao validar exclusão de administrador: ' . $e->getMessage());
            db_message('Não foi possível validar ou excluir o administrador.', 'danger');
        } finally {
            close_database($db);
        }
    } elseif ($alvo && remove('usuarios', $id)) {
        apagar_foto($alvo['Foto'] ?? null);
    }
}
header('Location: index.php');
exit;
