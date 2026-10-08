<?php
$requiredPermission = 'consultar_clientes';
require __DIR__ . '/includes/require-permission.php';

$db = getDb();

$q = trim($_GET['q'] ?? '');
$where = [
    "p.nome_perfil = 'Cliente'",
    "u.is_system = 0"
];
$params = [];

if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(u.nome_usuario LIKE :q1 OR u.login LIKE :q2 OR u.email_usuario LIKE :q3 OR u.cpf_usuario LIKE :q4 OR u.cnpj_usuario LIKE :q5)';
    $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => $like];
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$stmt = $db->prepare(
    "SELECT u.id_usuario, u.nome_usuario, u.login, u.email_usuario, u.cpf_usuario, u.cnpj_usuario,
            u.ativo,
            (SELECT COUNT(*) FROM Orcamento o WHERE o.id_usuario_cliente = u.id_usuario) AS qtd_orcamentos,
            (SELECT MAX(o.data_emissao) FROM Orcamento o WHERE o.id_usuario_cliente = u.id_usuario) AS ultimo_orcamento,
            (SELECT o.num_orcamento
             FROM Orcamento o
             WHERE o.id_usuario_cliente = u.id_usuario
             ORDER BY o.data_emissao DESC, o.id_orcamento DESC
             LIMIT 1) AS ultimo_num_orcamento
     FROM Usuario u
     JOIN Perfil p ON p.id_perfil = u.id_perfil
     $whereSql
     ORDER BY u.nome_usuario"
);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

$variant = 'account';
$wide = true;
$pageTitle = 'EcoVolts - Clientes';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="users-round"></i></span> Área comercial
</div>

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-serif text-[34px] font-medium">Clientes</h1>
        <p class="mt-1 text-sm text-eco-muted">Consulta cadastral e histórico de orçamentos por cliente.</p>
    </div>
</div>

<form method="get" class="mb-5 flex flex-wrap items-center gap-3">
    <input type="search" name="q" value="<?= htmlspecialchars($q) ?>"
           placeholder="Buscar por nome, e-mail, CPF ou CNPJ"
           class="min-w-[260px] flex-1 rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14.5px] outline-none focus-visible:border-eco-green">
    <button type="submit" class="rounded-md bg-eco-ink px-[18px] py-2.5 text-[14.5px] font-semibold text-white hover:bg-eco-dark">Buscar</button>
    <?php if ($q !== ''): ?>
        <a href="<?= route('clientes') ?>" class="text-[14px] text-eco-muted hover:text-eco-ink">Limpar</a>
    <?php endif; ?>
</form>

<?php if (empty($clientes)): ?>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] text-eco-muted">
        Nenhum cliente encontrado.
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-[10px] border border-eco-line bg-white">
        <table class="w-full border-collapse text-left text-[14px]">
            <thead>
                <tr class="bg-eco-surface text-eco-muted">
                    <th class="px-4 py-3.5 font-medium">Cliente</th>
                    <th class="px-4 py-3.5 font-medium">Contato</th>
                    <th class="px-4 py-3.5 font-medium">Documento</th>
                    <th class="px-4 py-3.5 font-medium">Último orçamento</th>
                    <th class="px-4 py-3.5 text-right font-medium">Histórico</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clientes as $i => $cliente): ?>
                <?php $doc = $cliente['cpf_usuario'] ?: $cliente['cnpj_usuario']; ?>
                <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-eco-surface/60' ?> border-t border-eco-line align-middle">
                    <td class="px-4 py-3.5">
                        <div class="font-medium"><?= htmlspecialchars($cliente['nome_usuario']) ?></div>
                        <div class="text-[12.5px] text-eco-muted"><?= htmlspecialchars($cliente['login']) ?></div>
                    </td>
                    <td class="px-4 py-3.5"><?= htmlspecialchars($cliente['email_usuario'] ?: '—') ?></td>
                    <td class="px-4 py-3.5"><?= $doc ? htmlspecialchars($doc) : '—' ?></td>
                    <td class="px-4 py-3.5">
                        <?php if ($cliente['ultimo_orcamento']): ?>
                            <?= htmlspecialchars($cliente['ultimo_num_orcamento'] ?: 'Orçamento') ?>
                            <div class="text-[12.5px] text-eco-muted"><?= date('d/m/Y', strtotime($cliente['ultimo_orcamento'])) ?></div>
                        <?php else: ?>
                            <span class="text-eco-muted">Nenhum orçamento</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <?php if ((int) $cliente['qtd_orcamentos'] > 0): ?>
                            <a href="<?= route('orcamentos') ?>?cliente=<?= (int) $cliente['id_usuario'] ?>"
                               class="font-semibold text-eco-green hover:underline">
                                Ver <?= (int) $cliente['qtd_orcamentos'] ?> orçamento<?= (int) $cliente['qtd_orcamentos'] === 1 ? '' : 's' ?>
                            </a>
                        <?php else: ?>
                            <span class="text-eco-muted">Sem histórico</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>