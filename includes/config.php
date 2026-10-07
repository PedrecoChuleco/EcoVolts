<?php
/**
 * Shared bootstrap: session, fake "auth", and a tiny route() helper
 * so the markup below can stay close to the original Inertia version.
 *
 * Swap the $routes map or the $auth logic for your real login system
 * whenever you wire up a database / auth layer.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function route(string $name): string
{
    static $routes = [
        'home'      => 'index.php',
        'login'     => 'login.php',
        'register'  => 'register.php',
        'dashboard' => 'dashboard.php',
        'perfil'    => 'perfil.php',
        'orcamento' => 'orcamento.php',
        'relatorio' => 'relatorio.php',
        'historico'             => 'historico.php',
        'usuarios'              => 'usuarios.php',
        'placas'                => 'placas.php',
        'produto_form'          => 'produto-form.php',
        'movimentacao_estoque'  => 'movimentacao-estoque.php',
        'movimentacao_estoque_form' => 'movimentacao-estoque-form.php',
        'clientes'              => 'clientes.php',
        'orcamentos'            => 'orcamentos.php',
    ];

    return $routes[$name] ?? '#';
}

/** True when the current script filename matches $needle (e.g. "login.php"). */
function isActive(string $needle): bool
{
    return str_contains(basename($_SERVER['SCRIPT_NAME'] ?? ''), $needle);
}

// A cada requisição, confere no banco se o usuário da sessão ainda existe e
// está ativo, e atualiza nome/perfil. Assim, se um administrador desativar,
// excluir ou mudar o papel de alguém, isso vale imediatamente — sem esperar
// a pessoa sair da sessão.
if (!empty($_SESSION['user']['id'])) {
    require_once __DIR__ . '/db.php';

    $stmtSessao = getDb()->prepare(
        'SELECT u.nome_usuario, u.ativo, u.is_system, p.nome_perfil
         FROM Usuario u LEFT JOIN Perfil p ON p.id_perfil = u.id_perfil
         WHERE u.id_usuario = :id LIMIT 1'
    );
    $stmtSessao->execute(['id' => $_SESSION['user']['id']]);
    $usuarioSessao = $stmtSessao->fetch();

    if (!$usuarioSessao || (int) $usuarioSessao['ativo'] !== 1 || (int) $usuarioSessao['is_system'] === 1) {
        unset($_SESSION['user']);
    } else {
        $_SESSION['user']['name']   = $usuarioSessao['nome_usuario'];
        $_SESSION['user']['perfil'] = $usuarioSessao['nome_perfil'];
    }
}

$auth = [
    'user' => $_SESSION['user'] ?? null,
];
