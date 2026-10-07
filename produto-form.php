<?php
$requiredPermission = 'gerenciar_estoque';
require __DIR__ . '/includes/require-permission.php';

$db = getDb();
$id = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;

$produto = null;
$estoque = null;

if ($editando) {
    $stmt = $db->prepare(
        'SELECT p.id_produto, p.nome_produto, p.marca_produto, p.voltagem_produto,
                p.valorUn_produto, p.tam_produto, p.descricao_produto, p.fornecedor,
                e.id_estoque, e.quantidade
         FROM Produto p
         LEFT JOIN Estoque e ON e.id_produto = p.id_produto
         WHERE p.id_produto = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        $_SESSION['flash_error'] = 'Produto não encontrado.';
        header('Location: placas.php');
        exit;
    }

    $estoque = $produto;
}

$errors = $_SESSION['stock_errors'] ?? [];
$old = $_SESSION['stock_old'] ?? null;
unset($_SESSION['stock_errors'], $_SESSION['stock_old']);

if ($old !== null) {
    $form = [
        'name'        => $old['name'] ?? '',
        'brand'       => $old['brand'] ?? '',
        'voltagem'    => $old['voltagem'] ?? '',
        'valor'       => $old['valor'] ?? '',
        'tam'         => $old['tam'] ?? '',
        'descricao'   => $old['descricao'] ?? '',
        'fornecedor'  => $old['fornecedor'] ?? '',
        'quantidade'  => $old['quantidade'] ?? '0',
    ];
} elseif ($editando) {
    $form = [
        'name'       => $produto['nome_produto'],
        'brand'      => $produto['marca_produto'] ?? '',
        'voltagem'   => $produto['voltagem_produto'] ?? '',
        'valor'      => $produto['valorUn_produto'] ?? '',
        'tam'        => $produto['tam_produto'] ?? '',
        'descricao'  => $produto['descricao_produto'] ?? '',
        'fornecedor' => $produto['fornecedor'] ?? '',
        'quantidade' => $produto['quantidade'] ?? '0',
    ];
} else {
    $form = [
        'name' => '', 'brand' => '', 'voltagem' => '', 'valor' => '', 'tam' => '',
        'descricao' => '', 'fornecedor' => '', 'quantidade' => '0',
    ];
}

$variant = 'account';
$wide = false;
$pageTitle = $editando ? 'EcoVolts - Editar placa' : 'EcoVolts - Nova placa';
require __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
$erro = fn (string $campo) => !empty($errors[$campo])
    ? '<p class="text-[13px] text-red-600">' . htmlspecialchars($errors[$campo]) . '</p>'
    : '';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="panels-top-left"></i></span> Administração de estoque
</div>
<h1 class="mb-2 font-serif text-[34px] font-medium"><?= $editando ? 'Editar placa / produto' : 'Nova placa / produto' ?></h1>
<p class="mb-6 text-sm text-eco-muted">O administrador pode alterar os dados do produto e a quantidade disponível. Alterações de quantidade são registradas no histórico.</p>

<form action="produto-handler.php" method="post" class="flex flex-col gap-6">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="col-span-full flex flex-col gap-1.5">
                <label for="name" class="text-[13px] font-medium text-eco-muted">Nome do produto</label>
                <input id="name" name="name" type="text" required value="<?= htmlspecialchars($form['name']) ?>" class="<?= $inputClass ?>">
                <?= $erro('name') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="brand" class="text-[13px] font-medium text-eco-muted">Marca</label>
                <input id="brand" name="brand" type="text" value="<?= htmlspecialchars($form['brand']) ?>" class="<?= $inputClass ?>">
                <?= $erro('brand') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="fornecedor" class="text-[13px] font-medium text-eco-muted">Fornecedor</label>
                <input id="fornecedor" name="fornecedor" type="text" value="<?= htmlspecialchars($form['fornecedor']) ?>" class="<?= $inputClass ?>">
                <?= $erro('fornecedor') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="tam" class="text-[13px] font-medium text-eco-muted">Potência / tamanho (W)</label>
                <input id="tam" name="tam" type="number" min="0" step="1" value="<?= htmlspecialchars($form['tam']) ?>" class="<?= $inputClass ?>">
                <?= $erro('tam') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="voltagem" class="text-[13px] font-medium text-eco-muted">Voltagem (V)</label>
                <input id="voltagem" name="voltagem" type="number" min="0" step="1" value="<?= htmlspecialchars($form['voltagem']) ?>" class="<?= $inputClass ?>">
                <?= $erro('voltagem') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="valor" class="text-[13px] font-medium text-eco-muted">Valor unitário (R$)</label>
                <input id="valor" name="valor" type="number" min="0" step="0.01" value="<?= htmlspecialchars($form['valor']) ?>" class="<?= $inputClass ?>">
                <?= $erro('valor') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="quantidade" class="text-[13px] font-medium text-eco-muted">Quantidade em estoque</label>
                <input id="quantidade" name="quantidade" type="number" min="0" step="1" required value="<?= htmlspecialchars($form['quantidade']) ?>" class="<?= $inputClass ?>">
                <?= $erro('quantidade') ?>
            </div>

            <div class="col-span-full flex flex-col gap-1.5">
                <label for="descricao" class="text-[13px] font-medium text-eco-muted">Descrição</label>
                <textarea id="descricao" name="descricao" rows="4" class="<?= $inputClass ?>"><?= htmlspecialchars($form['descricao']) ?></textarea>
                <?= $erro('descricao') ?>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <i data-lucide="save" width="17" height="17"></i> Salvar
        </button>
        <a href="placas.php" class="inline-flex w-fit items-center gap-2 rounded-md border border-eco-line bg-white px-[22px] py-3 text-[15px] font-semibold text-eco-green hover:border-eco-green">
            Cancelar
        </a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
