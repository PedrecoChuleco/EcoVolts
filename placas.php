<?php
$requiredPermission = 'consultar_estoque';
require __DIR__ . '/includes/require-permission.php';

$db = getDb();

$produtos = $db->query(
    "SELECT p.id_produto, p.nome_produto, p.marca_produto, p.voltagem_produto,
            p.valorUn_produto, p.tam_produto, p.descricao_produto, p.fornecedor,
            e.id_estoque, e.quantidade,
            (SELECT MAX(me.data_movimentacao)
             FROM MovimentacaoEstoque me
             WHERE me.id_estoque = e.id_estoque) AS ultima_movimentacao
     FROM Estoque e
     JOIN Produto p ON p.id_produto = e.id_produto
     ORDER BY p.nome_produto"
)->fetchAll();

$totalUnidades = 0;
$baixoEstoque = 0;
$flashOk = $_SESSION['flash_success'] ?? null;
$flashErro = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

foreach ($produtos as $produto) {
    $qtd = (int) $produto['quantidade'];
    $totalUnidades += $qtd;
    if ($qtd <= 10) {
        $baixoEstoque++;
    }
}

$variant = 'account';
$wide = true;
$pageTitle = 'EcoVolts - Placas / Estoque';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="panels-top-left"></i></span> Área comercial
</div>

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-serif text-[34px] font-medium">Placas / Estoque</h1>
        <p class="mt-1 text-sm text-eco-muted">Consulta de disponibilidade dos produtos cadastrados.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php if (userCan('gerenciar_estoque')): ?>
        <a href="<?= route('produto_form') ?>" class="inline-flex items-center gap-2 rounded-md bg-eco-amber px-4 py-2.5 text-[14px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <i data-lucide="plus" width="17" height="17"></i> Nova placa
        </a>
        <?php endif; ?>
        <a href="<?= route('movimentacao_estoque') ?>"
       class="inline-flex items-center gap-2 rounded-md border border-eco-line bg-white px-4 py-2.5 text-[14px] font-semibold text-eco-green hover:border-eco-green">
        <i data-lucide="arrow-left-right" width="17" height="17"></i> Ver movimentações
    </a>
    </div>
</div>

<?php if ($flashOk): ?>
    <p class="mb-5 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"><?= htmlspecialchars($flashOk) ?></p>
<?php endif; ?>
<?php if ($flashErro): ?>
    <p class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($flashErro) ?></p>
<?php endif; ?>

<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-5">
        <div class="text-[13px] text-eco-muted">Itens cadastrados</div>
        <div class="mt-1 font-serif text-3xl"><?= count($produtos) ?></div>
    </div>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-5">
        <div class="text-[13px] text-eco-muted">Unidades em estoque</div>
        <div class="mt-1 font-serif text-3xl"><?= number_format($totalUnidades, 0, ',', '.') ?></div>
    </div>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-5">
        <div class="text-[13px] text-eco-muted">Estoque baixo</div>
        <div class="mt-1 font-serif text-3xl"><?= $baixoEstoque ?></div>
        <div class="mt-1 text-xs text-eco-muted">10 unidades ou menos</div>
    </div>
</div>

<?php if (empty($produtos)): ?>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] text-eco-muted">
        Nenhum item de estoque cadastrado.
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-[10px] border border-eco-line bg-white">
        <table class="w-full border-collapse text-left text-[14px]">
            <thead>
                <tr class="bg-eco-surface text-eco-muted">
                    <th class="px-4 py-3.5 font-medium">Produto</th>
                    <th class="px-4 py-3.5 font-medium">Marca</th>
                    <th class="px-4 py-3.5 font-medium">Especificação</th>
                    <th class="px-4 py-3.5 font-medium">Fornecedor</th>
                    <th class="px-4 py-3.5 text-right font-medium">Preço referência</th>
                    <th class="px-4 py-3.5 text-right font-medium">Quantidade</th>
                    <th class="px-4 py-3.5 font-medium">Última mov.</th>
                    <?php if (userCan('gerenciar_estoque')): ?><th class="px-4 py-3.5 text-right font-medium">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($produtos as $i => $produto): ?>
                <?php
                    $qtd = (int) $produto['quantidade'];
                    $especificacao = [];
                    if ($produto['tam_produto'] !== null && $produto['tam_produto'] !== '') {
                        $especificacao[] = $produto['tam_produto'] . ' W';
                    }
                    if ($produto['voltagem_produto'] !== null && $produto['voltagem_produto'] !== '') {
                        $especificacao[] = $produto['voltagem_produto'] . ' V';
                    }
                ?>
                <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-eco-surface/60' ?> border-t border-eco-line align-middle">
                    <td class="px-4 py-3.5">
                        <div class="font-medium"><?= htmlspecialchars($produto['nome_produto']) ?></div>
                        <?php if (!empty($produto['descricao_produto'])): ?>
                            <div class="max-w-[360px] text-[12.5px] text-eco-muted"><?= htmlspecialchars($produto['descricao_produto']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3.5"><?= htmlspecialchars($produto['marca_produto'] ?? '—') ?></td>
                    <td class="px-4 py-3.5"><?= htmlspecialchars($especificacao ? implode(' · ', $especificacao) : '—') ?></td>
                    <td class="px-4 py-3.5"><?= htmlspecialchars($produto['fornecedor'] ?? '—') ?></td>
                    <td class="px-4 py-3.5 text-right">R$ <?= number_format((float) ($produto['valorUn_produto'] ?? 0), 2, ',', '.') ?></td>
                    <td class="px-4 py-3.5 text-right">
                        <span class="<?= $qtd <= 10 ? 'font-semibold text-[#b5502f]' : 'font-semibold text-eco-ink' ?>">
                            <?= number_format($qtd, 0, ',', '.') ?>
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <?= $produto['ultima_movimentacao'] ? date('d/m/Y', strtotime($produto['ultima_movimentacao'])) : '—' ?>
                    </td>
                    <?php if (userCan('gerenciar_estoque')): ?>
                    <td class="px-4 py-3.5 text-right">
                        <a href="<?= route('produto_form') ?>?id=<?= (int) $produto['id_produto'] ?>" class="inline-flex items-center gap-1.5 rounded-md border border-eco-line px-3 py-2 text-[13px] font-semibold text-eco-green hover:border-eco-green">
                            <i data-lucide="pencil" width="15" height="15"></i> Editar
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
