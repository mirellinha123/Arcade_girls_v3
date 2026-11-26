<?php
date_default_timezone_set('America/Sao_Paulo');
// Inicia a sessao antes de qualquer HTML ser enviado ao navegador.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Nome do banco de dados. */
define('DB_NAME', getenv('DB_NAME') ?: 'jogos_bd');
/** Usuario do MySQL local (troque ao publicar). */
define('DB_USER', getenv('DB_USER') ?: 'root');
/** Senha do MySQL local (troque ao publicar). */
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
/** Servidor do MySQL. */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

/** Caminho absoluto da pasta do projeto. */
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
/** A URL base é detectada em hospedagens comuns (inclusive quando em public_html/). */
if (!defined('BASEURL')) {
    $dirProjeto = str_replace('\\', '/', realpath(__DIR__));
    $raizSite = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $raizSite = $raizSite ? str_replace('\\', '/', $raizSite) : false;
    if ($raizSite && $dirProjeto === $raizSite) {
        $pastaUrl = '';
    } elseif ($raizSite && str_starts_with($dirProjeto, $raizSite . '/')) {
        $pastaUrl = trim(substr($dirProjeto, strlen($raizSite)), '/');
    } else {
        $pastaUrl = 'crud_jogos';
    }
    define('BASEURL', '/' . ($pastaUrl ? $pastaUrl . '/' : ''));
}
/** Caminhos dos arquivos compartilhados. */
if (!defined('DBAPI')) {
    define('DBAPI', ABSPATH . 'inc/database.php');
}
define('HEADER_TEMPLATE', ABSPATH . 'inc/header.php');
define('FOOTER_TEMPLATE', ABSPATH . 'inc/footer.php');

// Todas as páginas web dependem do banco; não renderize conteúdo incompleto sem ele.
if (PHP_SAPI !== 'cli') {
    require_once DBAPI;
    $databaseCheck = open_database();
    if (!$databaseCheck) {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>Banco de dados indisponível</title><style>';
        echo 'body{margin:0;min-height:100vh;display:grid;place-items:center;background:#07070a;color:#fff;font:600 1.1rem Rajdhani,Arial,sans-serif;padding:1.5rem}';
        echo 'main{max-width:38rem;padding:2rem;border:1px solid #ff69b4;border-radius:12px;background:#0f0f19;box-shadow:0 0 24px #ff69b433}';
        echo 'h1{color:#00f0ff;font-size:1.6rem}p{line-height:1.6;color:#ddd}code{color:#ff69b4}';
        echo '</style><main><h1>Banco de dados indisponível</h1>';
        echo '<p>Não foi possível conectar ao MySQL/MariaDB. Inicie o serviço do banco e confira as configurações <code>DB_HOST</code>, <code>DB_NAME</code>, <code>DB_USER</code> e <code>DB_PASSWORD</code>.</p>';
        echo '<p>Se o banco ainda não foi preparado, importe o arquivo <code>jogos_bd.sql</code> e recarregue esta página.</p></main></html>';
        exit;
    }
    close_database($databaseCheck);
}
