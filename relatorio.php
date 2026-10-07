<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/db.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

$db = getDb();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($id) {
    $stmt = $db->prepare(
        'SELECT o.id_orcamento, o.num_orcamento, o.payback, o.valor_totalCE, o.valor_totalCEPI,
                o.data_emissao, o.investimento, o.id_usuario_cliente,
                t.area_telhado, t.direcao_telhado, t.area_estimada, t.direcao_estimada, t.comporta_placas,
                op.qtd AS panel_count
         FROM Orcamento o
         LEFT JOIN Telhado t        ON t.id_telhado = o.id_telhado
         LEFT JOIN Orcamento_Prod op ON op.id_orcamento = o.id_orcamento
         WHERE o.id_orcamento = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $report = $stmt->fetch();
} else {
    // Sem ?id= na URL: mostra o orçamento mais recente do usuário logado.
    $stmt = $db->prepare(
        'SELECT o.id_orcamento, o.num_orcamento, o.payback, o.valor_totalCE, o.valor_totalCEPI,
                o.data_emissao, o.investimento, o.id_usuario_cliente,
                t.area_telhado, t.direcao_telhado, t.area_estimada, t.direcao_estimada, t.comporta_placas,
                op.qtd AS panel_count
         FROM Orcamento o
         LEFT JOIN Telhado t        ON t.id_telhado = o.id_telhado
         LEFT JOIN Orcamento_Prod op ON op.id_orcamento = o.id_orcamento
         WHERE o.id_usuario_cliente = :uid
         ORDER BY o.data_emissao DESC, o.id_orcamento DESC
         LIMIT 1'
    );
    $stmt->execute(['uid' => $auth['user']['id']]);
    $report = $stmt->fetch();
}

// Não existe orçamento nenhum, ou o orçamento encontrado não pertence a este
// usuário (impede ver o orçamento de outra pessoa trocando o ?id= na URL).
if (!$report || (int) $report['id_usuario_cliente'] !== (int) $auth['user']['id']) {
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

$name     = $auth['user']['name'] ?? $auth['user']['email'] ?? 'Cliente';
$address  = $auth['user']['address'] ?? null;
$roofFits = (int) $report['comporta_placas'] === 1;
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Resultado — <?= htmlspecialchars($report['num_orcamento']) ?>
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
                <?= $report['payback'] !== null ? htmlspecialchars((string) $report['payback']) . ' meses' : '—' ?>
            </dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Data de emissão</dt>
            <dd class="m-0 font-serif text-xl"><?= htmlspecialchars(date('d/m/Y', strtotime($report['data_emissao']))) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Conta de energia atual</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $report['valor_totalCE']) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Conta após instalação</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $report['valor_totalCEPI']) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Direção do telhado</dt>
            <dd class="m-0 font-serif text-xl">
                <?= htmlspecialchars($report['direcao_telhado']) ?><?= $report['direcao_estimada'] ? ' (estimada)' : '' ?>
            </dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Área do telhado</dt>
            <dd class="m-0 font-serif text-xl">
                <?= $report['area_telhado'] !== null ? htmlspecialchars((string) $report['area_telhado']) . ' m²' : 'Não informada (estimativa)' ?>
            </dd>
        </div>
    </dl>

    <div class="flex flex-wrap items-center justify-between gap-4 px-8 pt-[18px] pb-[26px]">
        <?php if ($roofFits): ?>
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

        <div class="flex items-center gap-3">
            <a href="<?= route('historico') ?>"
               class="inline-flex w-fit items-center gap-2 rounded-md border border-white/20 px-[22px] py-3 text-[15px] font-semibold text-white hover:border-white/40">
                Ver histórico
            </a>
            <a href="<?= route('dashboard') ?>"
               class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
                Voltar ao menu
            </a>
        </div>
    </div>
</div>