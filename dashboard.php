<?php
require __DIR__ . '/includes/config.php';

// Simple route guard: bounce guests back to login.
if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

$variant   = 'account';
$pageTitle = 'EcoVolts - Menu';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Área da conta
</div>
<h1 class="mb-1.5 font-serif text-[34px] font-medium">
    Olá, <?= htmlspecialchars($auth['user']['name'] ?? $auth['user']['email'] ?? 'visitante') ?>
</h1>
<p class="text-eco-muted mb-8">Comece um novo orçamento ou revise seu último relatório.</p>

<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <a href="<?= route('orcamento') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="sun-medium" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Novo orçamento</h2>
        <p class="text-sm text-eco-muted">Informe sua conta de energia e os dados do telhado.</p>
    </a>

    <a href="<?= route('historico') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="file-bar-chart" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Meus orçamentos</h2>
        <p class="text-sm text-eco-muted">Veja o payback e a economia estimada de cada orçamento já simulado.</p>
    </a>

    <a href="<?= route('perfil') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="user-round" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Meu perfil</h2>
        <p class="text-sm text-eco-muted">Atualize seus dados, endereço e senha.</p>
    </a>
</div>

<?php if (userCan('consultar_estoque') || userCan('consultar_movimentacao_estoque') || userCan('consultar_clientes') || userCan('consultar_orcamentos')): ?>
<h2 class="mb-4 mt-10 font-serif text-xl">Área comercial</h2>
<div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
    <?php if (userCan('consultar_estoque')): ?>
    <a href="<?= route('placas') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="panels-top-left" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Placas / Estoque</h2>
        <p class="text-sm text-eco-muted">Consulte a quantidade disponível de cada produto.</p>
    </a>
    <?php endif; ?>

    <?php if (userCan('consultar_movimentacao_estoque')): ?>
    <a href="<?= route('movimentacao_estoque') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="arrow-left-right" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Movimentações</h2>
        <p class="text-sm text-eco-muted">Veja entradas e saídas registradas no estoque.</p>
    </a>
    <?php endif; ?>

    <?php if (userCan('consultar_clientes')): ?>
    <a href="<?= route('clientes') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="users-round" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Clientes</h2>
        <p class="text-sm text-eco-muted">Consulte os clientes e o histórico de orçamentos.</p>
    </a>
    <?php endif; ?>

    <?php if (userCan('consultar_orcamentos')): ?>
    <a href="<?= route('orcamentos') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="files" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Orçamentos</h2>
        <p class="text-sm text-eco-muted">Consulte os orçamentos de todos os clientes.</p>
    </a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (userCan('administrar_usuarios')): ?>
<h2 class="mb-4 mt-10 font-serif text-xl">Administração</h2>
<div class="grid grid-cols-1 gap-5 md:grid-cols-3">
    <a href="<?= route('usuarios') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="users" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Usuários</h2>
        <p class="text-sm text-eco-muted">Crie, edite, desative e exclua contas de clientes e vendedores.</p>
    </a>
</div>
<?php endif; ?>

<a href="auth/logout.php" class="mt-10 inline-flex items-center gap-2 text-sm text-eco-muted hover:text-eco-ink">
    <i data-lucide="log-out" width="16" height="16"></i> Sair
</a>