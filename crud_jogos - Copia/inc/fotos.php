<?php
require_once __DIR__ . '/../config.php';

function salvar_foto(?array $arquivo, string $prefixo): ?string {
    if ($arquivo === null || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (!in_array($prefixo, ['cli', 'usr', 'game'], true)) {
        throw new RuntimeException('Tipo de foto inválido.');
    }
    if (($arquivo['error'] ?? -1) !== UPLOAD_ERR_OK ||
        empty($arquivo['tmp_name']) ||
        empty($arquivo['size']) ||
        $arquivo['size'] > 2 * 1024 * 1024 ||
        !is_uploaded_file($arquivo['tmp_name'])) {
        throw new RuntimeException('Escolha uma foto de até 2 MB.');
    }

    $tipos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipo = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
    if (!isset($tipos[$tipo]) || @getimagesize($arquivo['tmp_name']) === false) {
        throw new RuntimeException('A foto precisa ser JPG, PNG ou WEBP.');
    }
    $nome = $prefixo . '_' . bin2hex(random_bytes(12)) . '.' . $tipos[$tipo];
    if (!move_uploaded_file($arquivo['tmp_name'], ABSPATH . 'uploads/' . $nome)) {
        throw new RuntimeException('Não foi possível salvar a foto na pasta uploads.');
    }
    return $nome;
}

function foto_html(?string $nome, string $classe = 'foto-mini'): string {
    $classe = $classe === 'foto-grande' ? 'foto-grande' : 'foto-mini';
    if (!$nome || !preg_match('/^(cli|usr)_[a-f0-9]{24}\.(jpg|png|webp)$/', $nome)) {
        return '<span class="' . $classe . ' foto-sem"><i class="fa fa-user" aria-hidden="true"></i></span>';
    }
    $url = htmlspecialchars(BASEURL . 'uploads/' . $nome, ENT_QUOTES, 'UTF-8');
    return '<img src="' . $url . '" class="' . $classe . '" alt="Foto de perfil" loading="lazy">';
}

function apagar_foto(?string $nome): void {
    if ($nome && preg_match('/^(cli|usr|game)_[a-f0-9]{24}\.(jpg|png|webp)$/', $nome)) {
        $caminho = ABSPATH . 'uploads/' . $nome;
        if (is_file($caminho)) {
            unlink($caminho);
        }
    }
}
