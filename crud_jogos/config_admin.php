<?php
// Uso temporario, apenas pelo terminal, no projeto local.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/config.php';
require_once DBAPI;

function ler_campo($mensagem) {
    echo $mensagem;
    $linha = fgets(STDIN);
    if ($linha === false) {
        throw new RuntimeException('Entrada cancelada.');
    }
    return rtrim($linha, "\r\n");
}

$db = open_database();
if (!$db) {
    fwrite(STDERR, "Nao foi possivel conectar ao MySQL.\n");
    exit(1);
}

$transacao = false;
try {
    $usernameAdmin = 'mirella';
    $stmt = db_execute($db, "SELECT Id, Email FROM Usuarios WHERE Username = ? AND Perfil = 'admin' LIMIT 1", [$usernameAdmin]);
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$admin) {
        echo "Administrador mirella nao encontrado. Nenhum registro foi alterado.\n";
    } else {
        $nome = trim(ler_campo('Novo nome: '));
        $email = trim(ler_campo('Novo e-mail: '));
        echo "Use uma senha exclusiva para este projeto local. A digitacao fica visivel no terminal.\n";
        $senha = ler_campo('Nova senha (8 a 72 bytes): ');
        $confirmacao = ler_campo('Confirme a senha: ');
        if ($nome === '' || strlen($nome) > 100 || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Nome ou e-mail invalido.');
        }
        if (strlen($senha) < 8 || strlen($senha) > 72 || $senha !== $confirmacao) {
            throw new RuntimeException('Senha invalida ou confirmacao diferente.');
        }
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $id = (int)$admin['Id'];
        $db->begin_transaction();
        $transacao = true;
        $stmt = db_execute($db, "UPDATE Usuarios SET Nome = ?, Email = ?, Senha = ? WHERE Id = ? AND Username = ? AND Perfil = 'admin'", [$nome, $email, $hash, $id, $usernameAdmin]);
        $alterados = $stmt->affected_rows;
        $stmt->close();
        if ($alterados !== 1) {
            throw new RuntimeException('Nao foi possivel alterar a conta administrativa.');
        }
        $stmt = db_execute($db, 'SELECT Senha FROM Usuarios WHERE Id = ?', [$id]);
        $registro = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$registro || !password_verify($senha, $registro['Senha'])) {
            throw new RuntimeException('O hash salvo nao passou na verificacao. Confira se Senha e VARCHAR(255).');
        }
        $db->commit();
        $transacao = false;
        echo "Administrador atualizado com sucesso!\nE-mail: $email\nEntre no site com a senha que voce escolheu.\n";
    }
} catch (Throwable $e) {
    if ($transacao) {
        $db->rollback();
    }
    if ($e instanceof mysqli_sql_exception) {
        fwrite(STDERR, "Erro no banco de dados. Confira se o e-mail ja existe e a estrutura da tabela Usuarios.\n");
    } else {
        fwrite(STDERR, $e->getMessage() . "\n");
    }
    exit(1);
} finally {
    close_database($db);
}
