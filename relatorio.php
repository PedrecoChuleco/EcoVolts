<?php
require __DIR__ . '/includes/config.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

$report = $_SESSION['orcamento_resultado'] ?? null;

if (!$report) {
    // Nenhum orçamento calculado ainda nesta sessão: manda pro formulário.
    header('Location: orcamento.php');
    exit;
}

function brl(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

$variant   = 'account';
$pageTitle = 'EcoVolts - Relatório';
require __DIR__ . '/includes/header.php';

$name    = $auth['user']['name'] ?? $auth['user']['email'] ?? 'Cliente';
$address = $auth['user']['address'] ?? null;
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Resultado
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium">Orçamento final</h1>

<div class="rounded-[10px] bg-eco-ink text-white overflow-hidden">
    <div class="flex items-start justify-between gap-5 border-b border-white/12 px-8 pt-[26px] pb-[22px]">
        <div>
            <h3 class="mb-1 font-serif text-[22px] font-medium"><?= htmlspecialchars($name) ?></h3>
            <?php if ($address): ?>
                <p class="m-0 text-[13.5px] text-[#9FB4AE]"><?= htmlspecialchars($address) ?></p>
            <?php endif; ?>
        </div>
        <div
            aria-hidden="true"
            class="size-[30px] shrink-0 rounded-[4px]"
            style="background-color:#17393F; background-image:
                repeating-linear-gradient(0deg, transparent 0 5px, rgba(232,163,61,0.6) 5px 6px),
                repeating-linear-gradient(90deg, transparent 0 5px, rgba(232,163,61,0.6) 5px 6px);"
        ></div>
    </div>

    <dl class="grid grid-cols-1 gap-x-7 gap-y-5 px-8 py-[26px] md:grid-cols-2">
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Quantidade de placas</dt>
            <dd class="m-0 font-serif text-xl"><?= (int) $report['panel_count'] ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Investimento</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $report['investimento']) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Payback</dt>
            <dd class="m-0 font-serif text-xl">
                <?= $report['payback_meses'] !== null ? htmlspecialchars((string) $report['payback_meses']) . ' meses' : '—' ?>
            </dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Data de emissão</dt>
            <dd class="m-0 font-serif text-xl"><?= htmlspecialchars($report['issued_at']) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Conta de energia atual</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $report['bill_amount']) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Conta após instalação</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $report['bill_after']) ?></dd>
        </div>
    </dl>

    <div class="flex flex-wrap items-center justify-between gap-4 px-8 pt-[18px] pb-[26px]">
        <?php if ($report['roof_fits']): ?>
            <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-[13.5px] border-[rgba(154,214,183,0.4)] bg-[rgba(47,107,79,0.35)] text-[#4f8c68]">
                <span class="size-[7px] rounded-full bg-[#5FCE93]"></span>
                O telhado comporta as placas
            </span>
        <?php else: ?>
            <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-[13.5px] border-[rgba(232,163,61,0.4)] bg-[rgba(181,80,47,0.35)] text-[#743a1d]">
                <span class="size-[7px] rounded-full bg-eco-amber"></span>
                O telhado pode não comportar as placas
            </span>
        <?php endif; ?>

        <a href="<?= route('dashboard') ?>"
           class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            Voltar ao menu
        </a>
    </div>
</div>