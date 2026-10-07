<?php
$requiredPermission = 'consultar_orcamentos';
require __DIR__ . '/includes/require-permission.php';

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
        <h1 class="font-serif text-[34px] font-medium"><?= htmlspecialchars($orcamento['num_orcamento']) ?></h1>
        <p class="mt-1 text-sm text-eco-muted">Detalhes do orçamento e produtos utilizados.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="<?= route('orcamentos') ?>"
           class="inline-flex items-center gap-2 rounded-md border border-eco-line bg-white px-4 py-2.5 text-[14px] font-semibold text-eco-green hover:border-eco-green">
            ← Orçamentos
        </a>
        <?php if (userCan('editar_orcamentos')): ?>
            <a href="orcamento-form.php?id=<?= (int) $orcamento['id_orcamento'] ?>"
               class="inline-flex items-center gap-2 rounded-md bg-eco-amber px-4 py-2.5 text-[14px] font-semibold text-eco-ink hover:bg-eco-amber/75">
                <i data-lucide="pencil" width="17" height="17"></i> Editar
            </a>
        <?php endif; ?>
    </div>
</div>

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
                <dd><?= $orcamento['payback'] !== null ? number_format((float) $orcamento['payback'], 2, ',', '.') . ' anos' : '—' ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Total CE</dt>
                <dd><?= $orcamento['valor_totalCE'] !== null ? 'R$ ' . number_format((float) $orcamento['valor_totalCE'], 2, ',', '.') : '—' ?></dd>
            </div>
            <div>
                <dt class="text-[12.5px] text-eco-muted">Total CEPI</dt>
                <dd><?= $orcamento['valor_totalCEPI'] !== null ? 'R$ ' . number_format((float) $orcamento['valor_totalCEPI'], 2, ',', '.') : '—' ?></dd>
            </div>
        </dl>
    </section>
</div>

<section class="mt-5 rounded-[10px] border border-eco-line bg-white p-6">
    <h2 class="mb-4 font-serif text-xl">Itens do orçamento</h2>
    <?php if (empty($itens)): ?>
        <p class="text-sm text-eco-muted">Nenhum item registrado.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-[14px]">
                <thead>
                    <tr class="text-eco-muted">
                        <th class="pb-3 font-medium">Produto</th>
                        <th class="pb-3 text-right font-medium">Qtd.</th>
                        <th class="pb-3 text-right font-medium">Valor unit.</th>
                        <th class="pb-3 text-right font-medium">Desconto</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr class="border-t border-eco-line">
                        <td class="py-3">
                            <div class="font-medium"><?= htmlspecialchars($item['nome_produto']) ?></div>
                            <div class="text-[12.5px] text-eco-muted"><?= htmlspecialchars($item['marca_produto'] ?? '') ?></div>
                        </td>
                        <td class="py-3 text-right"><?= number_format((int) $item['qtd'], 0, ',', '.') ?></td>
                        <td class="py-3 text-right"><?= $item['valor_unitario'] !== null ? 'R$ ' . number_format((float) $item['valor_unitario'], 2, ',', '.') : '—' ?></td>
                        <td class="py-3 text-right"><?= $item['desconto'] !== null ? 'R$ ' . number_format((float) $item['desconto'], 2, ',', '.') : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
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
            <dd><?= htmlspecialchars($orcamento['direcao_telhado'] ?: '—') ?></dd>
        </div>
        <div>
            <dt class="text-[12.5px] text-eco-muted">Comporta placas</dt>
            <dd><?= (int) $orcamento['comporta_placas'] === 1 ? 'Sim' : 'Não' ?></dd>
        </div>
    </dl>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
