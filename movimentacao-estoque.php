<?php
$requiredPermission = 'consultar_movimentacao_estoque';
require __DIR__ . '/includes/require-permission.php';

$db = getDb();

$flashOk = $_SESSION['flash_success'] ?? null;
$flashErro = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$produtoFiltro = (int) ($_GET['produto'] ?? 0);
$tipoFiltro = trim($_GET['tipo'] ?? '');

$where = [];
$params = [];

if ($produtoFiltro > 0) {
    $where[] = 'p.id_produto = :produto';
    $params['produto'] = $produtoFiltro;
}
if ($tipoFiltro !== '') {
    $where[] = 'me.tipo = :tipo';
    $params['tipo'] = $tipoFiltro;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare(
    "SELECT me.id_movimentacao, me.tipo, me.quantidade, me.data_movimentacao,
            p.id_produto, p.nome_produto, e.id_estoque
     FROM MovimentacaoEstoque me
     JOIN Estoque e ON e.id_estoque = me.id_estoque
     JOIN Produto p ON p.id_produto = e.id_produto
     $whereSql
     ORDER BY me.data_movimentacao DESC, me.id_movimentacao DESC"
);
$stmt->execute($params);
$movimentacoes = $stmt->fetchAll();

$produtos = $db->query('SELECT id_produto, nome_produto FROM Produto ORDER BY nome_produto')->fetchAll();

$variant = 'account';
$wide = true;
$pageTitle = 'EcoVolts - Movimentações do estoque';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="arrow-left-right"></i></span> Área comercial
</div>

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-serif text-[34px] font-medium">Movimentações do estoque</h1>
        <p class="mt-1 text-sm text-eco-muted">Histórico de entradas e saídas registradas.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php if (userCan('gerenciar_estoque')): ?>
        <a href="<?= route('movimentacao_estoque_form') ?>" class="inline-flex items-center gap-2 rounded-md bg-eco-amber px-4 py-2.5 text-[14px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <i data-lucide="plus" width="17" height="17"></i> Nova movimentação
        </a>
        <?php endif; ?>
        <a href="<?= route('placas') ?>"
       class="inline-flex items-center gap-2 rounded-md border border-eco-line bg-white px-4 py-2.5 text-[14px] font-semibold text-eco-green hover:border-eco-green">
        <i data-lucide="panels-top-left" width="17" height="17"></i> Ver estoque
    </a>
    </div>
</div>

<?php if ($flashOk): ?>
    <p class="mb-5 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"><?= htmlspecialchars($flashOk) ?></p>
<?php endif; ?>
<?php if ($flashErro): ?>
    <p class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($flashErro) ?></p>
<?php endif; ?>

<form method="get" class="mb-5 flex flex-wrap items-end gap-3 rounded-[10px] border border-eco-line bg-white p-4">
    <div class="min-w-[230px] flex-1">
        <label for="produto" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Produto</label>
        <select id="produto" name="produto"
                class="w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] outline-none focus-visible:border-eco-green">
            <option value="0">Todos os produtos</option>
            <?php foreach ($produtos as $produto): ?>
                <option value="<?= (int) $produto['id_produto'] ?>" <?= $produtoFiltro === (int) $produto['id_produto'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($produto['nome_produto']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="tipo" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Tipo</label>
        <select id="tipo" name="tipo"
                class="rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] outline-none focus-visible:border-eco-green">
            <option value="">Todos</option>
            <option value="entrada" <?= $tipoFiltro === 'entrada' ? 'selected' : '' ?>>Entrada</option>
            <option value="saida" <?= $tipoFiltro === 'saida' ? 'selected' : '' ?>>Saída</option>
        </select>
    </div>
    <button type="submit" class="rounded-md bg-eco-ink px-[18px] py-2.5 text-[14.5px] font-semibold text-white hover:bg-eco-dark">Filtrar</button>
    <?php if ($produtoFiltro || $tipoFiltro): ?>
        <a href="<?= route('movimentacao_estoque') ?>" class="px-1 text-[14px] text-eco-muted hover:text-eco-ink">Limpar</a>
    <?php endif; ?>
</form>

<?php if (empty($movimentacoes)): ?>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] text-eco-muted">
        Nenhuma movimentação encontrada para os filtros selecionados.
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-[10px] border border-eco-line bg-white">
        <table class="w-full border-collapse text-left text-[14px]">
            <thead>
                <tr class="bg-eco-surface text-eco-muted">
                    <th class="px-4 py-3.5 font-medium">Data</th>
                    <th class="px-4 py-3.5 font-medium">Produto</th>
                    <th class="px-4 py-3.5 font-medium">Tipo</th>
                    <th class="px-4 py-3.5 text-right font-medium">Quantidade</th>
                    <?php if (userCan('gerenciar_estoque')): ?><th class="px-4 py-3.5 text-right font-medium">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($movimentacoes as $i => $movimentacao): ?>
                <?php $entrada = strtolower($movimentacao['tipo']) === 'entrada'; ?>
                <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-eco-surface/60' ?> border-t border-eco-line">
                    <td class="px-4 py-3.5"><?= $movimentacao['data_movimentacao'] ? date('d/m/Y', strtotime($movimentacao['data_movimentacao'])) : '—' ?></td>
                    <td class="px-4 py-3.5 font-medium"><?= htmlspecialchars($movimentacao['nome_produto']) ?></td>
                    <td class="px-4 py-3.5">
                        <span class="<?= $entrada ? 'text-[#2f6b4f]' : 'text-[#b5502f]' ?>">
                            <?= $entrada ? 'Entrada' : 'Saída' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-semibold">
                        <?= $entrada ? '+' : '-' ?><?= number_format((int) $movimentacao['quantidade'], 0, ',', '.') ?>
                    </td>
                    <?php if (userCan('gerenciar_estoque')): ?>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="<?= route('movimentacao_estoque_form') ?>?id=<?= (int) $movimentacao['id_movimentacao'] ?>" class="inline-flex items-center gap-1.5 rounded-md border border-eco-line px-3 py-2 text-[13px] font-semibold text-eco-green hover:border-eco-green">
                                <i data-lucide="pencil" width="15" height="15"></i> Editar
                            </a>
                            <form action="movimentacao-estoque-handler.php" method="post" onsubmit="return confirm('Excluir esta movimentação e recalcular o estoque?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $movimentacao['id_movimentacao'] ?>">
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-md border border-red-200 px-3 py-2 text-[13px] font-semibold text-red-700 hover:border-red-400">
                                    <i data-lucide="trash-2" width="15" height="15"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
