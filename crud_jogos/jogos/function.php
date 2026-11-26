<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once DBAPI;
require_once __DIR__ . '/../inc/auth.php';

$customers = [];
$customer = null;
$listaErro = '';

function formatData($data, $formato = 'd/m/Y')
{
    if (empty($data)) {
        return '';
    }

    try {
        return (new DateTime($data, new DateTimeZone('America/Sao_Paulo')))
            ->format($formato);
    } catch (Exception $e) {
        return '';
    }
}

function formatarMoeda($valor)
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function index()
{
    global $customers, $listaErro;

    try {
        $busca = $_GET['busca'] ?? '';
        $busca = is_string($busca) ? trim($busca) : '';
        $customers = $busca !== ''
            ? find_search('jogos', $busca)
            : find_all('jogos');
    } catch (Throwable $e) {
        error_log('Erro ao carregar catálogo de jogos: ' . $e->getMessage());
        $listaErro = 'Não foi possível carregar os jogos. Verifique a conexão com o banco de dados.';
    }
}

function view($id = null)
{
    global $customer;

    $id = (int) $id;
    if ($id <= 0) {
        header('Location: index.php');
        exit();
    }

    try {
        $customer = find('jogos', $id);
    } catch (Throwable $e) {
        error_log('Erro ao consultar jogo: ' . $e->getMessage());
        $_SESSION['message'] = 'Não foi possível carregar os dados do jogo.';
        $_SESSION['type'] = 'danger';
    }

    if (!$customer) {
        header('Location: index.php');
        exit();
    }
}

function validarJogo(array $dados)
{
    $campos = [
        'codigo_produto',
        'nome',
        'plataforma',
        'preco',
        'genero',
        'ano_lancamento',
        'classificacao_indicativa',
        'data_chegada',
        'quantidade_estoque',
        'estado_conservacao',
        'tipo_midia',
        'descricao',
    ];

    foreach ($campos as $campo) {
        if (!isset($dados[$campo]) || !is_scalar($dados[$campo])
            || trim((string) $dados[$campo]) === '') {
            return 'Preencha todos os campos obrigatórios.';
        }
    }

    if (!is_numeric($dados['preco']) || (float) $dados['preco'] < 0) {
        return 'Informe um preço válido.';
    }

    if (filter_var($dados['quantidade_estoque'], FILTER_VALIDATE_INT) === false
        || (int) $dados['quantidade_estoque'] < 0) {
        return 'Informe uma quantidade de estoque válida.';
    }

    if (filter_var($dados['ano_lancamento'], FILTER_VALIDATE_INT) === false
        || (int) $dados['ano_lancamento'] < 1950
        || (int) $dados['ano_lancamento'] > 2100) {
        return 'Informe um ano de lançamento válido.';
    }

    $data = DateTime::createFromFormat('!Y-m-d', (string) $dados['data_chegada']);
    if (!$data || $data->format('Y-m-d') !== $dados['data_chegada']) {
        return 'Informe uma data de chegada válida.';
    }

    $dados = array_intersect_key($dados, array_flip($campos));
    foreach ($dados as $campo => $valor) {
        $dados[$campo] = trim((string) $valor);
    }

    return $dados;
}

function add()
{
    global $customer;

    require_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Formulário inválido.');
    }

    $dados = $_POST['customer'] ?? [];
    if (!is_array($dados)) {
        $dados = [];
    }
    if (!can_manage_inventory()) {
        $dados['preco'] = '0.00';
        $dados['quantidade_estoque'] = '0';
    }
    $customer = $dados;
    $validacao = validarJogo($dados);
    if (is_string($validacao)) {
        $_SESSION['message'] = $validacao;
        $_SESSION['type'] = 'danger';
        return;
    }

    try {
        $validacao['foto'] = '';
        save('jogos', $validacao);
        header('Location: index.php');
        exit();
    } catch (Throwable $e) {
        error_log('Erro ao cadastrar jogo: ' . $e->getMessage());
        $_SESSION['message'] = 'Não foi possível cadastrar o jogo. Confira os dados e a estrutura do banco.';
        $_SESSION['type'] = 'danger';
    }
}

function edit()
{
    global $customer;

    require_login();

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        header('Location: index.php');
        exit();
    }

    try {
        $jogoAtual = find('jogos', $id);
    } catch (Throwable $e) {
        error_log('Erro ao consultar jogo para edição: ' . $e->getMessage());
        $_SESSION['message'] = 'Não foi possível carregar o jogo para edição.';
        $_SESSION['type'] = 'danger';
        header('Location: index.php');
        exit();
    }

    if (!$jogoAtual) {
        header('Location: index.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_valid($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            exit('Formulário inválido.');
        }

        $dados = $_POST['customer'] ?? [];
        if (!is_array($dados)) {
            $dados = [];
        }
        if (!can_manage_inventory()) {
            $dados['preco'] = $jogoAtual['preco'];
            $dados['quantidade_estoque'] = $jogoAtual['quantidade_estoque'];
        }
        $customer = $jogoAtual;
        foreach ($dados as $campo => $valor) {
            if (is_scalar($valor) && array_key_exists($campo, $customer)) {
                $customer[$campo] = (string) $valor;
            }
        }
        $validacao = validarJogo($dados);

        if (is_string($validacao)) {
            $_SESSION['message'] = $validacao;
            $_SESSION['type'] = 'danger';
            return;
        }

        try {
            update('jogos', $id, $validacao);
            header('Location: view.php?id=' . $id);
            exit();
        } catch (Throwable $e) {
            error_log('Erro ao atualizar jogo: ' . $e->getMessage());
            $_SESSION['message'] = 'Não foi possível atualizar o jogo. Confira os dados e a estrutura do banco.';
            $_SESSION['type'] = 'danger';
            return;
        }
    }

    $customer = $jogoAtual;
}

function delete($id = null)
{
    require_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !csrf_valid($_POST['csrf_token'] ?? null)) {
        http_response_code($_SERVER['REQUEST_METHOD'] === 'POST' ? 403 : 405);
        exit('Solicitação inválida.');
    }

    $id = filter_var($_POST['id'] ?? $id, FILTER_VALIDATE_INT);
    if (!$id || $id <= 0) {
        header('Location: index.php');
        exit();
    }

    try {
        remove('jogos', $id);
    } catch (Throwable $e) {
        error_log('Erro ao excluir jogo: ' . $e->getMessage());
        $_SESSION['message'] = 'Não foi possível excluir o jogo.';
        $_SESSION['type'] = 'danger';
    }

    header('Location: index.php');
    exit();
}
