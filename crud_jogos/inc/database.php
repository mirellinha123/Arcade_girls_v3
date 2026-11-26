<?php
// Mantem as mesmas funcoes usadas no CRUD de celulares, agora com consultas preparadas.
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

function open_database() {
    if (!class_exists('mysqli')) {
        error_log('Extensão PHP mysqli não está habilitada.');
        return null;
    }
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Throwable $e) {
        error_log('Erro de conexao ao banco: ' . $e->getMessage());
        return null;
    }
}

function close_database($conn) {
    if ($conn instanceof mysqli) {
        $conn->close();
    }
}

// Nomes de tabelas e colunas nao podem ser parametros de SQL: usamos listas permitidas.
function db_table($table) {
    $tables = [
        'celular' => 'Celular',
        'clientes' => 'Clientes',
        'usuarios' => 'Usuarios',
        'jogos' => 'Jogos',
    ];
    $key = strtolower((string)$table);
    if (!isset($tables[$key])) {
        throw new InvalidArgumentException('Tabela nao permitida.');
    }
    return $tables[$key];
}

function db_columns($table) {
    $columns = [
        'Celular' => ['modelo', 'Marca', 'Processador', 'Memoria_Interna', 'DataCad', 'Foto'],
        'Clientes' => ['Nome', 'Email', 'Telefone', 'CPF', 'DataNascimento', 'DataCad'],
        'Usuarios' => ['Nome', 'Email', 'Username', 'Senha', 'Perfil', 'DataCad', 'Foto'],
        'Jogos' => ['Nome', 'Plataforma', 'Genero', 'AnoLancamento', 'Preco', 'ClassificacaoIndicativa', 'DataEntrada', 'Estoque', 'EstadoConservacao', 'TipoMidia', 'Descricao', 'Foto', 'DataCad'],
    ];
    return $columns[$table];
}

function db_valid_data($table, $data) {
    if (!is_array($data) || !$data) {
        throw new InvalidArgumentException('Nenhum dado informado.');
    }
    foreach (array_keys($data) as $column) {
        if (!in_array($column, db_columns($table), true)) {
            throw new InvalidArgumentException('Coluna nao permitida.');
        }
    }
}

// Prepara SQL e executa vinculando os valores; sem concatenar dados do formulario.
function db_execute($database, $sql, $values = []) {
    $stmt = $database->prepare($sql);
    if ($values) {
        $types = '';
        foreach ($values as $value) {
            $types .= is_int($value) ? 'i' : 's';
        }
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();
    return $stmt;
}

function db_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['type'] = $type;
}

function db_failure($e) {
    error_log('Erro na consulta SQL: ' . $e->getMessage());
    db_message('Nao foi possivel realizar a operacao.', 'danger');
}

// Busca um registro por ID ou todos os registros, quando o ID nao for informado.
function find($table = null, $id = null) {
    $database = null;
    try {
        $table = db_table($table);
        $database = open_database();
        if (!$database) {
            throw new RuntimeException('Sem conexao com o banco.');
        }

        if ($id !== null && $id !== '') {
            $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                return null;
            }
            $stmt = db_execute($database, "SELECT * FROM `$table` WHERE `Id` = ?", [$id]);
            $result = $stmt->get_result();
            $row = $result->fetch_assoc() ?: null;
            $stmt->close();
            return $row;
        }

        $result = $database->query("SELECT * FROM `$table`");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        return $rows ?: null;
    } catch (Throwable $e) {
        db_failure($e);
        return null;
    } finally {
        close_database($database);
    }
}

function find_all($table) {
    return find($table);
}

function save($table = null, $data = null) {
    $database = null;
    try {
        $table = db_table($table);
        db_valid_data($table, $data);
        $database = open_database();
        if (!$database) {
            throw new RuntimeException('Sem conexao com o banco.');
        }
        $columns = array_keys($data);
        $names = implode(', ', array_map(fn($col) => "`$col`", $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $stmt = db_execute(
            $database,
            "INSERT INTO `$table` ($names) VALUES ($placeholders)",
            array_values($data)
        );
        $stmt->close();
        db_message('Registro cadastrado com sucesso.', 'success');
        return true;
    } catch (Throwable $e) {
        db_failure($e);
        return false;
    } finally {
        close_database($database);
    }
}

function update($table = null, $id = 0, $data = null) {
    $database = null;
    try {
        $table = db_table($table);
        db_valid_data($table, $data);
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('ID invalido.');
        }
        $columns = array_keys($data);
        $set = implode(', ', array_map(fn($col) => "`$col` = ?", $columns));
        $database = open_database();
        if (!$database) {
            throw new RuntimeException('Sem conexao com o banco.');
        }
        $values = array_values($data);
        $values[] = $id;
        $stmt = db_execute($database, "UPDATE `$table` SET $set WHERE `Id` = ?", $values);
        $stmt->close();
        db_message('Registro atualizado com sucesso.', 'success');
        return true;
    } catch (Throwable $e) {
        db_failure($e);
        return false;
    } finally {
        close_database($database);
    }
}

function remove($table = null, $id = null) {
    $database = null;
    try {
        $table = db_table($table);
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            throw new InvalidArgumentException('ID invalido.');
        }
        $database = open_database();
        if (!$database) {
            throw new RuntimeException('Sem conexao com o banco.');
        }
        $stmt = db_execute($database, "DELETE FROM `$table` WHERE `Id` = ?", [$id]);
        $stmt->close();
        db_message('Registro removido com sucesso.', 'success');
        return true;
    } catch (Throwable $e) {
        db_failure($e);
        return false;
    } finally {
        close_database($database);
    }
}
