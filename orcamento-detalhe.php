<?php
$requiredPermission = 'consultar_orcamentos';
require __DIR__ . '/includes/require-permission.php';
require_once __DIR__ . '/includes/StatusOrcamento.php';
require_once __DIR__ . '/includes/PadraoLigacao.php';

$db = getDb();
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . route('orcamentos'));
    exit;
}

$stmt = $db->prepare(
    "SELECT o.*,
            c.nome_usuario AS cliente_nome, c.login AS cliente_login,
            c.email_usuario AS cliente_email, c.cpf_usuario, c.cnpj_usuario,
            v.nome_usuario AS vendedor_nome,
            t.area_telhado, t.direcao_telhado, t.area_estimada, t.direcao_estimada,
            t.comporta_placas
     FROM Orcamento o
     JOIN Usuario c ON c.id_usuario = o.id_usuario_cliente
     JOIN Usuario v ON v.id_usuario = o.id_usuario_vendedor
     LEFT JOIN Telhado t ON t.id_telhado = o.id_telhado
     WHERE o.id_orcamento = :id
     LIMIT 1"
);
$stmt->execute(['id' => $id]);
$orcamento = $stmt->fetch();

if (!$orcamento) {
    http_response_code(404);
    $variant = 'account';
    $wide = true;
    $pageTitle = 'EcoVolts - Orçamento não encontrado';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">Área comercial</div>
    <h1 class="mb-3 font-serif text-[34px] font-medium">Orçamento não encontrado</h1>
    <p class="mb-6 text-eco-muted">O orçamento solicitado não existe ou não está disponível.</p>
    <a href="<?= route('orcamentos') ?>" class="font-semibold text-eco-green hover:underline">← Voltar aos orçamentos</a>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmtItens = $db->prepare(
    "SELECT op.qtd, op.valor_unitario, op.desconto,
            p.nome_produto, p.marca_produto
     FROM Orcamento_Prod op
     JOIN Produto p ON p.id_produto = op.id_produto
     WHERE op.id_orcamento = :id
     ORDER BY p.nome_produto"
);
$stmtItens->execute(['id' => $id]);
$itens = $stmtItens->fetchAll();

$statusAtual    = StatusOrcamento::tryFrom((string) ($orcamento['status'] ?? '')) ?? StatusOrcamento::PADRAO;
$padrao         = PadraoLigacao::tryFrom((string) ($orcamento['padrao_ligacao'] ?? ''));
$padraoEstimado = (int) ($orcamento['padrao_estimado'] ?? 0) === 1;
$podeAlterarStatus = userCan('alterar_status_orcamentos');

$variant = 'account';
$wide = true;
$pageTitle = 'EcoVolts - ' . $orcamento['num_orcamento'];
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="file-text"></i></span> Histórico comercial
</div>

<?php
$flashOk = $_SESSION['flash_success'] ?? null;
$flashErro = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<?php if ($flashOk): ?>
    <p class="mb-5 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"><?= htmlspecialchars($flashOk) ?></p>
<?php endif; ?>
<?php if ($flashErro): ?>
    <p class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($flashErro) ?></p>
<?php endif; ?>

<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="font-serif text-[34px] font-medium"><?= htmlspecialchars($orcamento['num_orcamento']) ?></h1>
            <?= StatusOrcamento::selo($orcamento['status'] ?? null) ?>
        </div>
        <p class="mt-1 text-sm text-eco-muted">Detalhes do orçamento e produtos utilizados.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="<?= route('orcamentos') ?>"
           class="inline-flex items-center gap-2 rounded-md border border-eco-line bg-white px-4 py-2.5 text-[14px] font-semibold text-eco-green hover:border-eco-green">
            ← Orçamentos
        </a>
        <a href="orcamento-pdf.php?id=<?= (int) $orcamento['id_orcamento'] ?>"
           class="inline-flex items-center gap-2 rounded-md border border-eco-line bg-white px-4 py-2.5 text-[14px] font-semibold text-eco-green hover:border-eco-green">
            <i data-lucide="file-down" width="17" height="17"></i> Baixar PDF
        </a>
        <?php if (userCan('editar_orcamentos')): ?>
            <a href="orcamento-form.php?id=<?= (int) $orcamento['id_orcamento'] ?>"
               class="inline-flex items-center gap-2 rounded-md bg-eco-amber px-4 py-2.5 text-[14px] font-semibold text-eco-ink hover:bg-eco-amber/75">
                <i data-lucide="pencil" width="17" height="17"></i> Editar
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($padrao && $padraoEstimado): ?>
    <div class="mb-5 flex items-start gap-3 rounded-[10px] border border-eco-amber/60 bg-eco-amber/15 px-5 py-4 text-[14px]" role="alert">
        <i data-lucide="triangle-alert" width="20" height="20" class="mt-0.5 shrink-0"></i>
        <p class="m-0">
            <strong>Padrão de ligação estimado.</strong> O cliente não informou o padrão; foi considerado
            <strong><?= htmlspecialchars(mb_strtolower($padrao->label())) ?></strong>
            (mínimo de R$ <?= number_format($padrao->custoMinimo(), 2, ',', '.') ?>). Confirme na visita técnica.
        </p>
    </div>
<?php endif; ?>

<?php if ($podeAlterarStatus): ?>
<section class="mb-5 rounded-[10px] border border-eco-line bg-white p-6">
    <h2 class="mb-3 font-serif text-xl">Status do orçamento</h2>
    <form action="orcamento-status-handler.php" method="post" class="flex flex-wrap items-center gap-3">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) $orcamento['id_orcamento'] ?>">
        <select name="status" aria-label="Status do orçamento"
                class="rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14.5px] outline-none focus-visible:border-eco-green">
            <?php foreach (StatusOrcamento::cases() as $opcao): ?>
                <option value="<?= htmlspecialchars($opcao->value) ?>" <?= $opcao === $statusAtual ? 'selected' : '' ?>>
                    <?= htmlspecialchars($opcao->label()) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="rounded-md bg-eco-ink px-[18px] py-2.5 text-[14.5px] font-semibold text-white hover:bg-eco-dark">
            Atualizar status
        </button>
    </form>
</section>
<?php endif; ?>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <section class="rounded-[10px] border border-eco-line bg-white p-6">
        <h2 class="mb-4 font-serif text-xl">Cliente</h2>
        <dl class="grid grid-cols-1 gap-3 text-[14px] md:grid-cols-2">
            <div>
                <dt class="text-[12.5px] text-eco-muted">Nome</dt>
                <dd class="font-medium"><?= htmlspecialchars($orcamento['cliente_nome']) ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Login</dt>
                <dd><?= htmlspecialchars($orcamento['cliente_login']) ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">E-mail</dt>
                <dd><?= htmlspecialchars($orcamento['cliente_email'] ?: '—') ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Documento</dt>
                <dd><?= htmlspecialchars($orcamento['cpf_usuario'] ?: ($orcamento['cnpj_usuario'] ?: '—')) ?></dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[10px] border border-eco-line bg-white p-6">
        <h2 class="mb-4 font-serif text-xl">Resumo comercial</h2>
        <dl class="grid grid-cols-1 gap-3 text-[14px] md:grid-cols-2">
            <div>
                <dt class="text-[12.5px] text-eco-muted">Status</dt>
                <dd><?= StatusOrcamento::selo($orcamento['status'] ?? null) ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Vendedor</dt>
                <dd class="font-medium"><?= htmlspecialchars($orcamento['vendedor_nome']) ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Emissão</dt>
                <dd><?= $orcamento['data_emissao'] ? date('d/m/Y', strtotime($orcamento['data_emissao'])) : '—' ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Investimento</dt>
                <dd class="font-semibold"><?= $orcamento['investimento'] !== null ? 'R$ ' . number_format((float) $orcamento['investimento'], 2, ',', '.') : '—' ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Payback</dt>
                <dd><?= $orcamento['payback'] !== null ? number_format((float) $orcamento['payback'], 1, ',', '.') . ' meses' : '—' ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Total CE (conta atual)</dt>
                <dd><?= $orcamento['valor_totalCE'] !== null ? 'R$ ' . number_format((float) $orcamento['valor_totalCE'], 2, ',', '.') : '—' ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Total CEPI (conta pós-instalação)</dt>
                <dd><?= $orcamento['valor_totalCEPI'] !== null ? 'R$ ' . number_format((float) $orcamento['valor_totalCEPI'], 2, ',', '.') : '—' ?></dd>
            </div>
            <?php if ($padrao): ?>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Padrão de ligação</dt>
                <dd>
                    <?= htmlspecialchars($padrao->label()) ?><?= $padraoEstimado ? ' (estimado, cliente não informou)' : '' ?>
                    · mín. R$ <?= number_format($padrao->custoMinimo(), 2, ',', '.') ?>
                </dd>
            </div>
            <?php endif; ?>
        </dl>
    </section>
</div>

<section class="mt-5 rounded-[10px] border border-eco-line bg-white p-6">
    <h2 class="mb-4 font-serif text-xl">Itens do orçamento</h2>
    <?php if (empty($itens)): ?>
        <p class="text-sm text-eco-muted">Nenhum item registrado.</p>
    <?php else: ?>
        <?php $totalItens = 0.0; ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-[14px]">
                <thead>
                    <tr class="text-eco-muted">
                        <th class="pb-3 font-medium">Produto</th>
                        <th class="pb-3 text-right font-medium">Qtd.</th>
                        <th class="pb-3 text-right font-medium">Valor unit.</th>
                        <th class="pb-3 text-right font-medium">Desconto</th>
                        <th class="pb-3 text-right font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($itens as $item):
                    $subtotal = (int) $item['qtd'] * (float) $item['valor_unitario'] - (float) $item['desconto'];
                    $totalItens += $subtotal;
                ?>
                    <tr class="border-t border-eco-line">
                        <td class="py-3">
                            <div class="font-medium"><?= htmlspecialchars($item['nome_produto']) ?></div>
                            <div class="text-[12.5px] text-eco-muted"><?= htmlspecialchars($item['marca_produto'] ?? '') ?></div>
                        </td>
                        <td class="py-3 text-right"><?= number_format((int) $item['qtd'], 0, ',', '.') ?></td>
                        <td class="py-3 text-right"><?= $item['valor_unitario'] !== null ? 'R$ ' . number_format((float) $item['valor_unitario'], 2, ',', '.') : '—' ?></td>
                        <td class="py-3 text-right"><?= $item['desconto'] !== null ? 'R$ ' . number_format((float) $item['desconto'], 2, ',', '.') : '—' ?></td>
                        <td class="py-3 text-right font-semibold">R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="border-t border-eco-line">
                        <td colspan="4" class="pt-3 text-right font-medium text-eco-muted">Total dos itens</td>
                        <td class="pt-3 text-right font-semibold">R$ <?= number_format($totalItens, 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php if ($orcamento['id_telhado']): ?>
<section class="mt-5 rounded-[10px] border border-eco-line bg-white p-6">
    <h2 class="mb-4 font-serif text-xl">Dados do telhado</h2>
    <dl class="grid grid-cols-1 gap-3 text-[14px] md:grid-cols-3">
        <div>
            <dt class="text-[12.5px] text-eco-muted">Área</dt>
            <dd><?= $orcamento['area_telhado'] !== null ? number_format((float) $orcamento['area_telhado'], 2, ',', '.') . ' m²' : '—' ?></dd>
        </div>
        <div>
            <dt class="text-[12.5px] text-eco-muted">Direção</dt>
            <dd><?= htmlspecialchars($orcamento['direcao_telhado'] ?: '—') ?><?= $orcamento['direcao_estimada'] ? ' (estimada)' : '' ?></dd>
        </div>
        <div>
            <dt class="text-[12.5px] text-eco-muted">Comporta placas</dt>
            <dd><?= (int) $orcamento['comporta_placas'] === 1 ? 'Sim' : 'Não' ?></dd>
        </div>
    </dl>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
