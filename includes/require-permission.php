<?php
/**
 * Guard reutilizável para áreas protegidas por uma permissão específica.
 *
 * Antes do require:
 *   $requiredPermission = 'consultar_clientes';
 *   require __DIR__ . '/includes/require-permission.php';
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth-helpers.php';

$requiredPermission = $requiredPermission ?? '';
if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

if ($requiredPermission === '' || !userCan($requiredPermission)) {
    http_response_code(403);
    $variant   = 'account';
    $pageTitle = 'EcoVolts - Acesso negado';
    require __DIR__ . '/header.php';
    ?>
    <div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
        <span class="roof-tick"><i data-lucide="lock"></i></span> Acesso restrito
    </div>
    <h1 class="mb-3 font-serif text-[34px] font-medium">Acesso negado</h1>
    <p class="mb-6 text-eco-muted">Você não tem permissão para acessar esta área.</p>
    <a href="<?= route('dashboard') ?>"
       class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
        Voltar ao menu
    </a>
    <?php
    require __DIR__ . '/footer.php';
    exit;
}
