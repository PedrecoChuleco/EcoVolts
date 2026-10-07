<?php
/**
 * Helpers de autorização e proteção CSRF.
 *
 * userCan() consulta as tabelas Perfil_Permissao / Permissao (populadas por
 * database/schema.sql), então dar ou tirar uma permissão de um papel é só
 * mexer nessas tabelas — não precisa editar código.
 */
require_once __DIR__ . '/db.php';

/** O usuário logado tem a permissão $permissao? (consulta o banco, com cache por requisição) */
function userCan(string $permissao): bool
{
    static $cache = [];

    $idUsuario = (int) ($_SESSION['user']['id'] ?? 0);
    if ($idUsuario === 0) {
        return false;
    }

    $chave = $idUsuario . ':' . $permissao;
    if (isset($cache[$chave])) {
        return $cache[$chave];
    }

    $stmt = getDb()->prepare(
        'SELECT 1
         FROM Usuario u
         JOIN Perfil_Permissao pp ON pp.id_perfil = u.id_perfil
         JOIN Permissao p         ON p.id_permissao = pp.id_permissao
         WHERE u.id_usuario = :id AND u.ativo = 1 AND u.is_system = 0
           AND p.nome_permissao = :perm
         LIMIT 1'
    );
    $stmt->execute(['id' => $idUsuario, 'perm' => $permissao]);

    return $cache[$chave] = (bool) $stmt->fetchColumn();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

/** <input hidden> para colocar dentro de todo <form method="post"> de ação sensível. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && is_string($_POST['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
