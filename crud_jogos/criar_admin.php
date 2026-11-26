<?php
// Ferramenta de inicialização local. Não é uma página web.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/config.php';
require_once DBAPI;
$db = open_database();
if (!$db) {
    fwrite(STDERR, "Não foi possível conectar ao banco.\n");
    exit(1);
}
try {
    $result = $db->query("SELECT Id FROM Usuarios WHERE Username = 'mirella_admin' AND Perfil = 'admin' LIMIT 1");
    if ($result->num_rows > 0) {
        echo "Já existe um administrador. Nenhum registro foi alterado.\n";
    } else {
        $nome = 'Mirella Admin';
        $email = 'mirella@arcade.local';
        $username = 'mirella_admin';
        $senha = bin2hex(random_bytes(12));
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = db_execute($db, "INSERT INTO Usuarios (Nome, Email, Username, Senha, Perfil) VALUES (?, ?, ?, ?, 'admin')", [$nome, $email, $username, $hash]);
        $stmt->close();
        echo "Administrador criado. Guarde a senha em local seguro.\nUsuário: $username\nE-mail: $email\nSenha: $senha\n";
    }
} catch (Throwable $e) {
    error_log('Erro ao criar administrador local: ' . $e->getMessage());
    fwrite(STDERR, "Não foi possível criar a conta. Confirme se o arquivo jogos_bd.sql foi importado e se o usuário mirella_admin está livre.\n");
    exit(1);
} finally {
    close_database($db);
}
