<?php
$requiredPermission = 'editar_orcamentos';
require __DIR__ . '/includes/require-permission.php';

$db = getDb();
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ' . route('orcamentos'));
    exit;
}

$stmt = $db->prepare(
    "SELECT o.*,
            c.nome_usuario AS cliente_nome,
            v.nome_usuario AS vendedor_nome
     FROM Orcamento o
     JOIN Usuario c ON c.id_usuario = o.id_usuario_cliente
     JOIN Usuario v ON v.id_usuario = o.id_usuario_vendedor
     WHERE o.id_orcamento = :id
     LIMIT 1"
);
$stmt->execute(['id' => $id]);
$orcamento = $stmt->fetch();

if (!$orcamento) {
    header('Location: ' . route('orcamentos'));
    exit;
}

$itensStmt = $db->prepare(
    "SELECT op.id_produto, op.qtd, op.valor_unitario, op.desconto
     FROM Orcamento_Prod op
     WHERE op.id_orcamento = :id
     ORDER BY op.id_produto"
);
$itensStmt->execute(['id' => $id]);
$itensAtuais = $itensStmt->fetchAll();

$produtos = $db->query(
    'SELECT id_produto, nome_produto, marca_produto, valorUn_produto
     FROM Produto ORDER BY nome_produto'
)->fetchAll();

$vendedores = $db->query(
    "SELECT u.id_usuario, u.nome_usuario, u.is_system
     FROM Usuario u
     JOIN Perfil p ON p.id_perfil = u.id_perfil
     WHERE p.nome_perfil = 'Vendedor' AND (u.is_system = 1 OR u.ativo = 1)
     ORDER BY u.is_system DESC, u.nome_usuario"
)->fetchAll();

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? null;
unset($_SESSION['errors'], $_SESSION['old']);

function oldValue(string $key, mixed $fallback = ''): mixed {
    global $old, $orcamento;
    return $old !== null && array_key_exists($key, $old) ? $old[$key] : ($orcamento[$key] ?? $fallback);
}

$itensForm = $old['itens'] ?? null;
if (!is_array($itensForm)) {
    $itensForm = [];
    foreach ($itensAtuais as $item) {
        $itensForm[] = [
            'id_produto' => (int) $item['id_produto'],
            'qtd' => (int) $item['qtd'],
            'valor_unitario' => $item['valor_unitario'],
            'desconto' => $item['desconto'],
        ];
    }
    // Mantém duas linhas vazias para inclusão de novos itens.
    $itensForm[] = ['id_produto' => '', 'qtd' => '', 'valor_unitario' => '', 'desconto' => '0'];
    $itensForm[] = ['id_produto' => '', 'qtd' => '', 'valor_unitario' => '', 'desconto' => '0'];
}

$variant = 'account';
$wide = true;
$pageTitle = 'EcoVolts - Editar ' . $orcamento['num_orcamento'];
require __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
$erro = fn (string $campo) => !empty($errors[$campo])
    ? '<p class="text-[13px] text-red-600">' . htmlspecialchars($errors[$campo]) . '</p>' : '';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="pencil"></i></span> Administração · Histórico comercial
</div>

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-serif text-[34px] font-medium">Editar orçamento</h1>
        <p class="mt-1 text-sm text-eco-muted"><?= htmlspecialchars($orcamento['num_orcamento']) ?> · Cliente: <?= htmlspecialchars($orcamento['cliente_nome']) ?></p>
    </div>
    <a href="orcamento-detalhe.php?id=<?= (int) $id ?>"
       class="text-[14px] font-semibold text-eco-green hover:underline">← Voltar aos detalhes</a>
</div>

<?php if ($errors): ?>
    <div class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
        Revise os campos destacados e tente novamente.
    </div>
<?php endif; ?>

<form action="orcamento-admin-handler.php" method="post" class="flex flex-col gap-5">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <section class="rounded-[10px] border border-eco-line bg-white p-6">
        <h2 class="mb-4 font-serif text-xl">Dados do orçamento</h2>
        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-3">
            <div class="flex flex-col gap-1.5">
                <label for="num_orcamento" class="text-[13px] font-medium text-eco-muted">Número</label>
                <input id="num_orcamento" name="num_orcamento" required
                       value="<?= htmlspecialchars((string) oldValue('num_orcamento', '')) ?>"
                       class="<?= $inputClass ?>">
                <?= $erro('num_orcamento') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="data_emissao" class="text-[13px] font-medium text-eco-muted">Data de emissão</label>
                <input id="data_emissao" name="data_emissao" type="date"
                       value="<?= htmlspecialchars((string) oldValue('data_emissao', '')) ?>"
                       class="<?= $inputClass ?>">
                <?= $erro('data_emissao') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="id_usuario_vendedor" class="text-[13px] font-medium text-eco-muted">Vendedor responsável</label>
                <select id="id_usuario_vendedor" name="id_usuario_vendedor" class="<?= $inputClass ?>">
                    <?php foreach ($vendedores as $vendedor): ?>
                        <option value="<?= (int) $vendedor['id_usuario'] ?>"
                            <?= (int) oldValue('id_usuario_vendedor', $orcamento['id_usuario_vendedor']) === (int) $vendedor['id_usuario'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($vendedor['nome_usuario']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= $erro('id_usuario_vendedor') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="investimento" class="text-[13px] font-medium text-eco-muted">Investimento</label>
                <input id="investimento" name="investimento" inputmode="decimal"
                       value="<?= htmlspecialchars((string) oldValue('investimento', '')) ?>"
                       placeholder="Ex.: 25000,00" class="<?= $inputClass ?>">
                <?= $erro('investimento') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="payback" class="text-[13px] font-medium text-eco-muted">Payback (anos)</label>
                <input id="payback" name="payback" inputmode="decimal"
                       value="<?= htmlspecialchars((string) oldValue('payback', '')) ?>"
                       placeholder="Ex.: 4,50" class="<?= $inputClass ?>">
                <?= $erro('payback') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="valor_totalCE" class="text-[13px] font-medium text-eco-muted">Valor total CE</label>
                <input id="valor_totalCE" name="valor_totalCE" inputmode="decimal"
                       value="<?= htmlspecialchars((string) oldValue('valor_totalCE', '')) ?>"
                       class="<?= $inputClass ?>">
                <?= $erro('valor_totalCE') ?>
            </div>

            <div class="flex flex-col gap-1.5 md:col-span-2">
                <label for="valor_totalCEPI" class="text-[13px] font-medium text-eco-muted">Valor total CEPI</label>
                <input id="valor_totalCEPI" name="valor_totalCEPI" inputmode="decimal"
                       value="<?= htmlspecialchars((string) oldValue('valor_totalCEPI', '')) ?>"
                       class="<?= $inputClass ?>">
                <?= $erro('valor_totalCEPI') ?>
            </div>
        </div>
    </section>

    <section class="rounded-[10px] border border-eco-line bg-white p-6">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="font-serif text-xl">Itens</h2>
                <p class="mt-1 text-[13px] text-eco-muted">Edite quantidades, valores e descontos. Linhas sem produto serão ignoradas.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse text-[13.5px]">
                <thead>
                    <tr class="text-left text-eco-muted">
                        <th class="pb-3 pr-3 font-medium">Produto</th>
                        <th class="pb-3 px-2 font-medium">Qtd.</th>
                        <th class="pb-3 px-2 font-medium">Valor unit.</th>
                        <th class="pb-3 px-2 font-medium">Desconto</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($itensForm as $idx => $item): ?>
                    <tr class="border-t border-eco-line">
                        <td class="py-3 pr-3">
                            <select name="itens[<?= $idx ?>][id_produto]" class="<?= $inputClass ?>">
                                <option value="">Selecione um produto</option>
                                <?php foreach ($produtos as $produto): ?>
                                    <option value="<?= (int) $produto['id_produto'] ?>"
                                        <?= (int) ($item['id_produto'] ?? 0) === (int) $produto['id_produto'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($produto['nome_produto']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="px-2 py-3">
                            <input name="itens[<?= $idx ?>][qtd]" type="number" min="0" step="1"
                                   value="<?= htmlspecialchars((string) ($item['qtd'] ?? '')) ?>"
                                   class="<?= $inputClass ?>">
                        </td>
                        <td class="px-2 py-3">
                            <input name="itens[<?= $idx ?>][valor_unitario]" inputmode="decimal"
                                   value="<?= htmlspecialchars((string) ($item['valor_unitario'] ?? '')) ?>"
                                   class="<?= $inputClass ?>">
                        </td>
                        <td class="px-2 py-3">
                            <input name="itens[<?= $idx ?>][desconto]" inputmode="decimal"
                                   value="<?= htmlspecialchars((string) ($item['desconto'] ?? '0')) ?>"
                                   class="<?= $inputClass ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= $erro('itens') ?>
    </section>

    <div class="flex items-center gap-3.5">
        <button type="submit"
                class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <i data-lucide="save" width="17" height="17"></i> Salvar alterações
        </button>
        <a href="orcamento-detalhe.php?id=<?= $id ?>"
           class="inline-flex w-fit items-center gap-2 rounded-md px-[22px] py-3 text-[15px] font-semibold text-eco-muted hover:text-eco-ink">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
