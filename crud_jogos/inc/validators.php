<?php
function cpf_valido(string $cpf): bool
{
    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11 || preg_match('/\A(\d)\1{10}\z/', $cpf)) {
        return false;
    }

    for ($posicao = 9; $posicao < 11; $posicao++) {
        $soma = 0;
        for ($indice = 0; $indice < $posicao; $indice++) {
            $soma += (int)$cpf[$indice] * (($posicao + 1) - $indice);
        }
        $digito = ($soma * 10) % 11;
        if ($digito === 10) {
            $digito = 0;
        }
        if ($digito !== (int)$cpf[$posicao]) {
            return false;
        }
    }
    return true;
}

function cpf_formatado(string $cpf): string
{
    $numeros = preg_replace('/\D/', '', $cpf);
    return substr($numeros, 0, 3) . '.' . substr($numeros, 3, 3) . '.' .
        substr($numeros, 6, 3) . '-' . substr($numeros, 9, 2);
}

function data_nascimento_valida(string $data): bool
{
    if ($data === '') {
        return true;
    }

    $dataFormatada = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
    $erros = DateTimeImmutable::getLastErrors();

    return $dataFormatada instanceof DateTimeImmutable
        && $dataFormatada->format('Y-m-d') === $data
        && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
        && $dataFormatada <= new DateTimeImmutable('today');
}
