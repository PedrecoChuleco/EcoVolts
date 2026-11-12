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

/**
 * URL base do projeto, sem barra no fim. Calculada a partir de onde o script em
 * execucao esta dentro do projeto, entao funciona em qualquer lugar:
 *   http://localhost/ecovolts/...  -> "/ecovolts"   (XAMPP, htdocs/ecovolts)
 *   http://ecovolts.test/...       -> ""            (VirtualHost na raiz)
 */
function baseUrl(): string
{
    $raiz   = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
    $script = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $nome   = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));

    if ($raiz !== '' && $script !== '' && stripos($script, $raiz . '/') === 0) {
        $rel = substr($script, strlen($raiz));                 // "/conta/dashboard.php"
        if (strlen($nome) >= strlen($rel) && strcasecmp(substr($nome, -strlen($rel)), $rel) === 0) {
            $base = substr($nome, 0, strlen($nome) - strlen($rel));
            return implode('/', array_map('rawurlencode', explode('/', $base)));
        }
    }

    return '';
}

if (!defined('BASE_URL')) {
    define('BASE_URL', baseUrl());
}

/** URL de um arquivo dentro de assets/, ex.: asset('css/style.css'). */
function asset(string $caminho): string
{
    return BASE_URL . '/assets/' . ltrim($caminho, '/');
}

/**
 * URL de uma pagina pelo nome (chave = nome do arquivo com "_" no lugar de "-").
 * Para mover uma pagina de pasta, basta mudar o caminho aqui.
 * Nome desconhecido devolve '#'.
 */
function route(string $name): string
{
    static $routes = [
        // (raiz)
        'home' => 'index.php',
        // auth
        'login'            => 'auth/login.php',
        'login_handler'    => 'auth/login-handler.php',
        'logout'           => 'auth/logout.php',
        'register'         => 'auth/register.php',
        'register_handler' => 'auth/register-handler.php',
        // conta
        'dashboard'      => 'conta/dashboard.php',
        'perfil'         => 'conta/perfil.php',
        'perfil_handler' => 'conta/perfil-handler.php',
        // simulador
        'historico'         => 'simulador/historico.php',
        'orcamento'         => 'simulador/orcamento.php',
        'orcamento_handler' => 'simulador/orcamento-handler.php',
        'relatorio'         => 'simulador/relatorio.php',
        // orcamentos
        'orcamento_admin_handler'  => 'orcamentos/orcamento-admin-handler.php',
        'orcamento_detalhe'        => 'orcamentos/orcamento-detalhe.php',
        'orcamento_form'           => 'orcamentos/orcamento-form.php',
        'orcamento_pdf'            => 'orcamentos/orcamento-pdf.php',
        'orcamento_status_handler' => 'orcamentos/orcamento-status-handler.php',
        'orcamentos'               => 'orcamentos/orcamentos.php',
        // clientes
        'clientes'         => 'clientes/clientes.php',
        'clientes_form'    => 'clientes/clientes-form.php',
        'clientes_handler' => 'clientes/clientes-handler.php',
        // estoque
        'movimentacao_estoque'         => 'estoque/movimentacao-estoque.php',
        'movimentacao_estoque_form'    => 'estoque/movimentacao-estoque-form.php',
        'movimentacao_estoque_handler' => 'estoque/movimentacao-estoque-handler.php',
        'placas'                       => 'estoque/placas.php',
        'produto_form'                 => 'estoque/produto-form.php',
        'produto_handler'              => 'estoque/produto-handler.php',
        // usuarios
        'usuario_form'    => 'usuarios/usuario-form.php',
        'usuario_handler' => 'usuarios/usuario-handler.php',
        'usuarios'        => 'usuarios/usuarios.php',
        // relatorios
        'exportar'    => 'relatorios/exportar.php',
        'indicadores' => 'relatorios/indicadores.php',
    ];

    return isset($routes[$name]) ? BASE_URL . '/' . $routes[$name] : '#';
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
