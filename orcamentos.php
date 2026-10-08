<?php
$requiredPermission = 'consultar_orcamentos';
require __DIR__ . '/includes/require-permission.php';

$db = getDb();

$q = trim($_GET['q'] ?? '');
$clienteFiltro = (int) ($_GET['cliente'] ?? 0);
$vendedorFiltro = (int) ($_GET['vendedor'] ?? 0);

$where = [];
$params = [];

if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(o.num_orcamento LIKE :q1 OR c.nome_usuario LIKE :q2 OR c.login LIKE :q3)';
    $params += ['q1' => $like, 'q2' => $like, 'q3' => $like];
}
if ($clienteFiltro > 0) {
    $where[] = 'o.id_usuario_cliente = :cliente';
    $params['cliente'] = $clienteFiltro;
}
if ($vendedorFiltro > 0) {
    $where[] = 'o.id_usuario_vendedor = :vendedor';
    $params['vendedor'] = $vendedorFiltro;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare(
    "SELECT o.id_orcamento, o.num_orcamento, o.payback, o.valor_totalCE,
            o.valor_totalCEPI, o.data_emissao, o.investimento,
            o.id_usuario_cliente, o.id_usuario_vendedor,
            c.nome_usuario AS cliente_nome, c.login AS cliente_login,
            v.nome_usuario AS vendedor_nome,
            (SELECT COUNT(*) FROM Orcamento_Prod op WHERE op.id_orcamento = o.id_orcamento) AS qtd_itens
     FROM Orcamento o
     JOIN Usuario c ON c.id_usuario = o.id_usuario_cliente
     JOIN Usuario v ON v.id_usuario = o.id_usuario_vendedor
     $whereSql
     ORDER BY o.data_emissao DESC, o.id_orcamento DESC"
);
$stmt->execute($params);
$orcamentos = $stmt->fetchAll();

$clientes = $db->query(
    "SELECT u.id_usuario, u.nome_usuario
     FROM Usuario u
     JOIN Perfil p ON p.id_perfil = u.id_perfil
     WHERE p.nome_perfil = 'Cliente' AND u.is_system = 0
     ORDER BY u.nome_usuario"
)->fetchAll();

$vendedores = $db->query(
    "SELECT u.id_usuario, u.nome_usuario, u.is_system
     FROM Usuario u
     JOIN Perfil p ON p.id_perfil = u.id_perfil
     WHERE p.nome_perfil = 'Vendedor'
     ORDER BY u.is_system DESC, u.nome_usuario"
)->fetchAll();

$variant = 'account';
$wide = true;
$pageTitle = 'EcoVolts - Orçamentos';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="files"></i></span> Área comercial
</div>

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-serif text-[34px] font-medium">Orçamentos</h1>
        <p class="mt-1 text-sm text-eco-muted">Histórico comercial de todos os clientes.</p>
    </div>
    <a href="<?= route('clientes') ?>"
       class="inline-flex items-center gap-2 rounded-md border border-eco-line bg-white px-4 py-2.5 text-[14px] font-semibold text-eco-green hover:border-eco-green">
        <i data-lucide="users-round" width="17" height="17"></i> Ver clientes
    </a>
</div>

<form method="get" class="mb-5 grid grid-cols-1 gap-3 rounded-[10px] border border-eco-line bg-white p-4 md:grid-cols-4">
    <div class="md:col-span-2">
        <label for="q" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Busca</label>
        <input id="q" type="search" name="q" value="<?= htmlspecialchars($q) ?>"
               placeholder="Nº do orçamento, nome ou e-mail do cliente"
               class="w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] outline-none focus-visible:border-eco-green">
    </div>
    <div>
        <label for="cliente" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Cliente</label>
        <select id="cliente" name="cliente"
                class="w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] outline-none focus-visible:border-eco-green">
            <option value="0">Todos</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= (int) $cliente['id_usuario'] ?>" <?= $clienteFiltro === (int) $cliente['id_usuario'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cliente['nome_usuario']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="vendedor" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Vendedor</label>
        <select id="vendedor" name="vendedor"
                class="w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] outline-none focus-visible:border-eco-green">
            <option value="0">Todos</option>
            <?php foreach ($vendedores as $vendedor): ?>
                <option value="<?= (int) $vendedor['id_usuario'] ?>" <?= $vendedorFiltro === (int) $vendedor['id_usuario'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($vendedor['nome_usuario']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="md:col-span-full flex items-center gap-3">
        <button type="submit" class="rounded-md bg-eco-ink px-[18px] py-2.5 text-[14.5px] font-semibold text-white hover:bg-eco-dark">Filtrar</button>
        <?php if ($q !== '' || $clienteFiltro || $vendedorFiltro): ?>
            <a href="<?= route('orcamentos') ?>" class="text-[14px] text-eco-muted hover:text-eco-ink">Limpar filtros</a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($orcamentos)): ?>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] text-eco-muted">
        Nenhum orçamento encontrado.
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-[10px] border border-eco-line bg-white">
        <table class="w-full border-collapse text-left text-[14px]">
            <thead>
                <tr class="bg-eco-surface text-eco-muted">
                    <th class="px-4 py-3.5 font-medium">Orçamento</th>
                    <th class="px-4 py-3.5 font-medium">Cliente</th>
                    <th class="px-4 py-3.5 font-medium">Vendedor</th>
                    <th class="px-4 py-3.5 font-medium">Data</th>
                    <th class="px-4 py-3.5 text-right font-medium">Investimento</th>
                    <th class="px-4 py-3.5 text-right font-medium">Payback</th>
                    <th class="px-4 py-3.5 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orcamentos as $i => $orcamento): ?>
                <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-eco-surface/60' ?> border-t border-eco-line align-middle">
                    <td class="px-4 py-3.5">
                        <div class="font-medium"><?= htmlspecialchars($orcamento['num_orcamento']) ?></div>
                        <div class="text-[12.5px] text-eco-muted"><?= (int) $orcamento['qtd_itens'] ?> item<?= (int) $orcamento['qtd_itens'] === 1 ? '' : 'ns' ?></div>
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="font-medium"><?= htmlspecialchars($orcamento['cliente_nome']) ?></div>
                        <div class="text-[12.5px] text-eco-muted"><?= htmlspecialchars($orcamento['cliente_login']) ?></div>
                    </td>
                    <td class="px-4 py-3.5"><?= htmlspecialchars($orcamento['vendedor_nome']) ?></td>
                    <td class="px-4 py-3.5"><?= $orcamento['data_emissao'] ? date('d/m/Y', strtotime($orcamento['data_emissao'])) : '—' ?></td>
                    <td class="px-4 py-3.5 text-right">
                        <?= $orcamento['investimento'] !== null ? 'R$ ' . number_format((float) $orcamento['investimento'], 2, ',', '.') : '—' ?>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <?= $orcamento['payback'] !== null ? number_format((float) $orcamento['payback'], 2, ',', '.') . ' anos' : '—' ?>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-3 text-[13.5px]">
                            <a href="orcamento-detalhe.php?id=<?= (int) $orcamento['id_orcamento'] ?>"
                               class="font-semibold text-eco-green hover:underline">Consultar</a>
                            <?php if (userCan('editar_orcamentos')): ?>
                                <a href="orcamento-form.php?id=<?= (int) $orcamento['id_orcamento'] ?>"
                                   class="font-semibold text-eco-green hover:underline">Editar</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-[13.5px] text-eco-muted">
        <?= count($orcamentos) ?> orçamento<?= count($orcamentos) === 1 ? '' : 's' ?> encontrado<?= count($orcamentos) === 1 ? '' : 's' ?>.
    </div>
<?php endif; ?>