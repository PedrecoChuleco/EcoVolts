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
    ];

    return $routes[$name] ?? '#';
}

/** True when the current script filename matches $needle (e.g. "login.php"). */
function isActive(string $needle): bool
{
    return str_contains(basename($_SERVER['SCRIPT_NAME'] ?? ''), $needle);
}

// Fake auth: set $_SESSION['user'] on login, unset() it on logout.
$auth = [
    'user' => $_SESSION['user'] ?? null,
];
