<?php
require_once __DIR__ . '/../config.php';
require_once DBAPI;
require_once __DIR__ . '/../inc/fotos.php';

function validar_jogo(array $input): array
{
    $campos = [
        'Nome' => 150,
        'Plataforma' => 80,
        'Genero' => 80,
        'ClassificacaoIndicativa' => 40,
        'EstadoConservacao' => 40,
        'TipoMidia' => 40,
        'Descricao' => 10000,
    ];
    $dados = [];
    foreach ($campos as $campo => $limite) {
        if (!is_string($input[$campo] ?? null)) {
            throw new InvalidArgumentException("Preencha \"$campo\".");
        }
        $valor = trim($input[$campo]);
        if ($valor === '' || strlen($valor) > $limite) {
            throw new InvalidArgumentException("Preencha \"$campo\" (máximo de $limite caracteres).");
        }
        $dados[$campo] = $valor;
    }
    if (!in_array($dados['EstadoConservacao'], ['Novo', 'Excelente', 'Muito bom', 'Bom', 'Regular'], true)) {
        throw new InvalidArgumentException('Selecione um estado de conservação válido.');
    }
    if (!in_array($dados['TipoMidia'], ['Cartucho', 'Disco', 'Digital', 'Outro'], true)) {
        throw new InvalidArgumentException('Selecione um tipo de mídia válido.');
    }

    $ano = filter_var($input['AnoLancamento'] ?? null, FILTER_VALIDATE_INT);
    if ($ano === false || $ano < 1950 || $ano > (int)date('Y') + 1) {
        throw new InvalidArgumentException('Informe um ano de lançamento válido.');
    }
    $preco = filter_var($input['Preco'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($preco === false || $preco < 0 || $preco > 99999999.99) {
        throw new InvalidArgumentException('Informe um preço válido, igual ou maior que zero.');
    }
    $estoque = filter_var($input['Estoque'] ?? null, FILTER_VALIDATE_INT);
    if ($estoque === false || $estoque < 0) {
        throw new InvalidArgumentException('O estoque deve ser um número inteiro igual ou maior que zero.');
    }
    $dataEntrada = $input['DataEntrada'] ?? null;
    if (!is_string($dataEntrada)) {
        throw new InvalidArgumentException('Informe uma data de entrada válida.');
    }
    $dataValida = DateTime::createFromFormat('Y-m-d', $dataEntrada);
    if (!$dataValida || $dataValida->format('Y-m-d') !== $dataEntrada) {
        throw new InvalidArgumentException('Informe uma data de entrada válida.');
    }

    $dados['AnoLancamento'] = $ano;
    $dados['Preco'] = number_format($preco, 2, '.', '');
    $dados['Estoque'] = $estoque;
    $dados['DataEntrada'] = $dataEntrada;
    return $dados;
}

function jogo_url_foto(?string $foto): ?string
{
    if (!$foto || strlen($foto) > 128 ||
        !preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9 _.-]*\.(?:jpg|png|webp)\z/i', $foto) ||
        strpos($foto, '..') !== false) {
        return null;
    }
    if (!is_file(ABSPATH . 'uploads/' . $foto)) {
        return null;
    }
    return BASEURL . 'uploads/' . rawurlencode($foto);
}
