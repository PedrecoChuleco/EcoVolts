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

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <a href="<?= route('orcamento') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="sun-medium" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Novo orçamento</h2>
        <p class="text-sm text-eco-muted">Informe sua conta de energia e os dados do telhado.</p>
    </a>

    <a href="<?= route('relatorio') ?>"
       class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
        <i data-lucide="file-bar-chart" class="text-eco-amber mb-3" width="28" height="28"></i>
        <h2 class="font-serif text-xl mb-1">Meus relatórios</h2>
        <p class="text-sm text-eco-muted">Veja o payback e a economia estimada dos seus orçamentos.</p>
    </a>
</div>

<a href="auth/logout.php" class="mt-10 inline-flex items-center gap-2 text-sm text-eco-muted hover:text-eco-ink">
    <i data-lucide="log-out" width="16" height="16"></i> Sair
</a>

<?php require __DIR__ . '/includes/footer.php'; ?>
