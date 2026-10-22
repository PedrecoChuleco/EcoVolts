<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/PadraoLigacao.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

$db = getDb();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Colunas lidas do orçamento, do telhado e da quantidade de placas.
$colunas = 'o.id_orcamento, o.num_orcamento, o.payback, o.valor_totalCE, o.valor_totalCEPI,
            o.data_emissao, o.investimento, o.id_usuario_cliente,
            o.padrao_ligacao, o.padrao_estimado,
            t.area_telhado, t.direcao_telhado, t.area_estimada, t.direcao_estimada, t.comporta_placas,
            op.qtd AS qtd_placas';

if ($id) {
    $consulta = $db->prepare(
        "SELECT $colunas
         FROM Orcamento o
         LEFT JOIN Telhado t         ON t.id_telhado = o.id_telhado
         LEFT JOIN Orcamento_Prod op ON op.id_orcamento = o.id_orcamento
         WHERE o.id_orcamento = :id
         LIMIT 1"
    );
    $consulta->execute(['id' => $id]);
} else {
    // Sem ?id= na URL: mostra o orçamento mais recente do usuário logado.
    $consulta = $db->prepare(
        "SELECT $colunas
         FROM Orcamento o
         LEFT JOIN Telhado t         ON t.id_telhado = o.id_telhado
         LEFT JOIN Orcamento_Prod op ON op.id_orcamento = o.id_orcamento
         WHERE o.id_usuario_cliente = :uid
         ORDER BY o.data_emissao DESC, o.id_orcamento DESC
         LIMIT 1"
    );
    $consulta->execute(['uid' => $auth['user']['id']]);
}
$relatorio = $consulta->fetch();

// Não existe orçamento nenhum, ou o orçamento encontrado não pertence a este
// usuário (impede ver o orçamento de outra pessoa trocando o ?id= na URL).
if (!$relatorio || (int) $relatorio['id_usuario_cliente'] !== (int) $auth['user']['id']) {
    header('Location: orcamento.php');
    exit;
}

function brl(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

$variant   = 'account';
$pageTitle = 'EcoVolts - Relatório';
require __DIR__ . '/includes/header.php';

$nome            = $auth['user']['name'] ?? $auth['user']['email'] ?? 'Cliente';
$endereco        = $auth['user']['address'] ?? null;
$telhadoComporta = (int) $relatorio['comporta_placas'] === 1;

// Padrão de ligação (orçamentos antigos podem não ter esse dado).
$padrao          = PadraoLigacao::tryFrom((string) ($relatorio['padrao_ligacao'] ?? ''));
$padraoEstimado  = (int) $relatorio['padrao_estimado'] === 1;
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Resultado — <?= htmlspecialchars($relatorio['num_orcamento']) ?>
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium">Orçamento final</h1>

<?php if ($padrao && $padraoEstimado): ?>
    <div class="mb-5 flex items-start gap-3 rounded-[10px] border border-eco-amber/60 bg-eco-amber/15 px-5 py-4 text-[14px] text-eco-ink" role="alert">
        <i data-lucide="triangle-alert" width="20" height="20" class="mt-0.5 shrink-0"></i>
        <p class="m-0">
            <strong>Padrão de ligação estimado.</strong>
            Como você não informou o padrão de ligação, consideramos o padrão
            <strong><?= htmlspecialchars(mb_strtolower($padrao->label())) ?></strong>,
            com valor mínimo de conta de <?= brl($padrao->custoMinimo()) ?>
            (<?= $padrao->consumoMinimoKwh() ?> kWh). A conta após a instalação e o
            payback podem variar se o seu padrão for diferente. Confirme o padrão
            na sua conta de energia ou na visita técnica.
        </p>
    </div>
<?php endif; ?>

<div class="rounded-[10px] bg-eco-ink text-white overflow-hidden">
    <div class="flex items-start justify-between gap-5 border-b border-white/12 px-8 pt-[26px] pb-[22px]">
        <div>
            <h3 class="mb-1 font-serif text-[22px] font-medium"><?= htmlspecialchars($nome) ?></h3>
            <?php if ($endereco): ?>
                <p class="m-0 text-[13.5px] text-[#9FB4AE]"><?= htmlspecialchars($endereco) ?></p>
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
            <dd class="m-0 font-serif text-xl"><?= (int) $relatorio['qtd_placas'] ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Investimento</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $relatorio['investimento']) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Payback</dt>
            <dd class="m-0 font-serif text-xl">
                <?= $relatorio['payback'] !== null ? htmlspecialchars((string) $relatorio['payback']) . ' meses' : '—' ?>
            </dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Data de emissão</dt>
            <dd class="m-0 font-serif text-xl"><?= htmlspecialchars(date('d/m/Y', strtotime($relatorio['data_emissao']))) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Conta de energia atual</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $relatorio['valor_totalCE']) ?></dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Conta após instalação</dt>
            <dd class="m-0 font-serif text-xl"><?= brl((float) $relatorio['valor_totalCEPI']) ?></dd>
        </div>
        <?php if ($padrao): ?>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Padrão de ligação</dt>
            <dd class="m-0 font-serif text-xl">
                <?= htmlspecialchars($padrao->label()) ?><?= $padraoEstimado ? ' (estimado)' : '' ?>
            </dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Valor mínimo da conta</dt>
            <dd class="m-0 font-serif text-xl"><?= brl($padrao->custoMinimo()) ?></dd>
        </div>
        <?php endif; ?>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Direção do telhado</dt>
            <dd class="m-0 font-serif text-xl">
                <?= htmlspecialchars($relatorio['direcao_telhado']) ?><?= $relatorio['direcao_estimada'] ? ' (estimada)' : '' ?>
            </dd>
        </div>
        <div>
            <dt class="mb-1 text-[12.5px] text-[#9FB4AE]">Área do telhado</dt>
            <dd class="m-0 font-serif text-xl">
                <?= $relatorio['area_telhado'] !== null ? htmlspecialchars((string) $relatorio['area_telhado']) . ' m²' : 'Não informada (estimativa)' ?>
            </dd>
        </div>
    </dl>

    <div class="flex flex-wrap items-center justify-between gap-4 px-8 pt-[18px] pb-[26px]">
        <?php if ($telhadoComporta): ?>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
