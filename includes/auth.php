<?php
/**
 * Controle de sessão e proteção de páginas.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Retorna true se existe um usuário logado na sessão atual. */
function estaLogado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/** Bloqueia o acesso a páginas protegidas, redirecionando para o login. */
function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: ' . caminhoRaiz('index.php') . '?erro=' . urlencode('Faça login para continuar.'));
        exit;
    }
}

/**
 * Ajuda a montar caminhos relativos corretos tanto quando este arquivo
 * é incluído a partir da raiz do site quanto de dentro de /includes.
 */
function caminhoRaiz(string $arquivo): string
{
    $dentroDeIncludes = strpos($_SERVER['SCRIPT_NAME'], '/includes/') !== false;
    return $dentroDeIncludes ? "../{$arquivo}" : $arquivo;
}
