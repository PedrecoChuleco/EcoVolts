<?php
$requiredPermission = 'gerenciar_estoque';
require __DIR__ . '/includes/require-permission.php';

$db = getDb();
$id = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;
$movimentacao = null;

if ($editando) {
    $stmt = $db->prepare(
        'SELECT me.id_movimentacao, me.tipo, me.quantidade, me.data_movimentacao,
                me.id_estoque, p.id_produto, p.nome_produto
         FROM MovimentacaoEstoque me
         JOIN Estoque e ON e.id_estoque = me.id_estoque
         JOIN Produto p ON p.id_produto = e.id_produto
         WHERE me.id_movimentacao = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $movimentacao = $stmt->fetch();

    if (!$movimentacao) {
        $_SESSION['flash_error'] = 'Movimentação não encontrada.';
        header('Location: movimentacao-estoque.php');
        exit;
    }
}

$old = $_SESSION['movement_old'] ?? null;
$error = $_SESSION['movement_error'] ?? null;
unset($_SESSION['movement_old'], $_SESSION['movement_error']);

if ($old !== null) {
    $form = [
        'id_estoque' => (int) ($old['id_estoque'] ?? 0),
        'tipo' => $old['tipo'] ?? 'entrada',
        'quantidade' => $old['quantidade'] ?? 1,
        'data' => $old['data'] ?? date('Y-m-d'),
    ];
} elseif ($editando) {
    $form = [
        'id_estoque' => (int) $movimentacao['id_estoque'],
        'tipo' => $movimentacao['tipo'],
        'quantidade' => (int) $movimentacao['quantidade'],
        'data' => $movimentacao['data_movimentacao'] ?: date('Y-m-d'),
    ];
} else {
    $form = [
        'id_estoque' => 0,
        'tipo' => 'entrada',
        'quantidade' => 1,
        'data' => date('Y-m-d'),
    ];
}

$estoques = $db->query(
    'SELECT e.id_estoque, e.quantidade, p.nome_produto
     FROM Estoque e
     JOIN Produto p ON p.id_produto = e.id_produto
     ORDER BY p.nome_produto'
)->fetchAll();

$variant = 'account';
$pageTitle = $editando ? 'EcoVolts - Editar movimentação' : 'EcoVolts - Nova movimentação';
require __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="arrow-left-right"></i></span> Administração de estoque
</div>
<h1 class="mb-2 font-serif text-[34px] font-medium"><?= $editando ? 'Editar movimentação' : 'Nova movimentação' ?></h1>
<p class="mb-6 text-sm text-eco-muted">O sistema ajusta o saldo do estoque junto com a movimentação para manter os dois históricos consistentes.</p>

<?php if ($error): ?>
    <p class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="movimentacao-estoque-handler.php" method="post" class="flex flex-col gap-6">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="<?= $editando ? 'update' : 'create' ?>">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="col-span-full flex flex-col gap-1.5">
                <label for="id_estoque" class="text-[13px] font-medium text-eco-muted">Produto</label>
                <select id="id_estoque" name="id_estoque" required class="<?= $inputClass ?>">
                    <option value="0">Selecione...</option>
                    <?php foreach ($estoques as $estoque): ?>
                        <option value="<?= (int) $estoque['id_estoque'] ?>" <?= $form['id_estoque'] === (int) $estoque['id_estoque'] ? 'selected' : '' ?> data-estoque="<?= (int) $estoque['quantidade'] ?>">
                            <?= htmlspecialchars($estoque['nome_produto']) ?> — saldo atual: <?= number_format((int) $estoque['quantidade'], 0, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="tipo" class="text-[13px] font-medium text-eco-muted">Tipo</label>
                <select id="tipo" name="tipo" class="<?= $inputClass ?>">
                    <option value="entrada" <?= $form['tipo'] === 'entrada' ? 'selected' : '' ?>>Entrada</option>
                    <option value="saida" <?= $form['tipo'] === 'saida' ? 'selected' : '' ?>>Saída</option>
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="quantidade" class="text-[13px] font-medium text-eco-muted">Quantidade</label>
                <input id="quantidade" name="quantidade" type="number" min="1" step="1" required value="<?= htmlspecialchars((string) $form['quantidade']) ?>" class="<?= $inputClass ?>">
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="data" class="text-[13px] font-medium text-eco-muted">Data</label>
                <input id="data" name="data" type="date" required value="<?= htmlspecialchars($form['data']) ?>" class="<?= $inputClass ?>">
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <i data-lucide="save" width="17" height="17"></i> Salvar movimentação
        </button>
        <a href="movimentacao-estoque.php" class="inline-flex w-fit items-center gap-2 rounded-md border border-eco-line bg-white px-[22px] py-3 text-[15px] font-semibold text-eco-green hover:border-eco-green">
            Cancelar
        </a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
