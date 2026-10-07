<?php
require __DIR__ . '/includes/require-admin.php';

$db = getDb();

$q            = trim($_GET['q'] ?? '');
$perfilFiltro = (int) ($_GET['perfil'] ?? 0);
$pagina       = max(1, (int) ($_GET['page'] ?? 1));
$porPagina    = 15;

$where  = [];
$params = [];

if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(u.nome_usuario LIKE :q1 OR u.login LIKE :q2)';
    $params['q1'] = $like;
    $params['q2'] = $like;
}
if ($perfilFiltro > 0) {
    $where[] = 'u.id_perfil = :perfil';
    $params['perfil'] = $perfilFiltro;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmtTotal = $db->prepare("SELECT COUNT(*) FROM Usuario u $whereSql");
$stmtTotal->execute($params);
$total        = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$pagina       = min($pagina, $totalPaginas);
$offset       = ($pagina - 1) * $porPagina;

$stmt = $db->prepare(
    "SELECT u.id_usuario, u.nome_usuario, u.login, u.ativo, u.is_system, p.nome_perfil,
            (SELECT COUNT(*) FROM Orcamento o WHERE o.id_usuario_cliente = u.id_usuario) AS qtd_orcamentos
     FROM Usuario u
     LEFT JOIN Perfil p ON p.id_perfil = u.id_perfil
     $whereSql
     ORDER BY u.nome_usuario
     LIMIT $porPagina OFFSET $offset"
);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

$perfis = $db->query('SELECT id_perfil, nome_perfil FROM Perfil ORDER BY id_perfil')->fetchAll();

$flashOk   = $_SESSION['flash_success'] ?? null;
$flashErro = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$meuId = (int) $auth['user']['id'];

// Parâmetros que ficam na URL ao trocar de página / voltar de uma ação.
$filtros = array_filter(['q' => $q, 'perfil' => $perfilFiltro ?: null], fn ($v) => $v !== '' && $v !== null);

$variant   = 'account';
$pageTitle = 'EcoVolts - Usuários';
require __DIR__ . '/includes/header.php';

$inputClass = 'rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[14.5px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="check"></i></span> Administração
</div>
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <h1 class="font-serif text-[34px] font-medium">Usuários</h1>
    <div class="flex flex-wrap items-center gap-2.5">
        <a href="usuario-form.php?perfil=vendedor"
           class="inline-flex w-fit items-center gap-2 rounded-md border border-eco-line bg-white px-[18px] py-2.5 text-[14.5px] font-semibold text-eco-green hover:border-eco-green">
            <i data-lucide="badge-check" width="18" height="18"></i> Novo vendedor
        </a>
        <a href="usuario-form.php"
           class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <i data-lucide="plus" width="18" height="18"></i> Novo usuário
        </a>
    </div>
</div>

<?php if ($flashOk): ?>
    <p class="mb-5 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"><?= htmlspecialchars($flashOk) ?></p>
<?php endif; ?>
<?php if ($flashErro): ?>
    <p class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($flashErro) ?></p>
<?php endif; ?>

<form method="get" class="mb-5 flex flex-wrap items-center gap-3">
    <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nome ou e-mail"
           class="<?= $inputClass ?> min-w-[220px] flex-1">
    <select name="perfil" class="<?= $inputClass ?>">
        <option value="0">Todos os perfis</option>
        <?php foreach ($perfis as $p): ?>
            <option value="<?= (int) $p['id_perfil'] ?>" <?= $perfilFiltro === (int) $p['id_perfil'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['nome_perfil']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="rounded-md bg-eco-ink px-[18px] py-2.5 text-[14.5px] font-semibold text-white hover:bg-eco-dark">Filtrar</button>
    <?php if ($filtros): ?>
        <a href="usuarios.php" class="text-[14px] text-eco-muted hover:text-eco-ink">Limpar</a>
    <?php endif; ?>
</form>

<?php if (empty($usuarios)): ?>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] text-eco-muted">
        Nenhum usuário encontrado.
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-[10px] border border-eco-line">
        <table class="w-full border-collapse text-left text-[14.5px]">
            <thead>
                <tr class="bg-eco-surface text-eco-muted">
                    <th class="px-4 py-3.5 font-medium">Usuário</th>
                    <th class="px-4 py-3.5 font-medium">Perfil</th>
                    <th class="px-4 py-3.5 font-medium">Status</th>
                    <th class="px-4 py-3.5 font-medium">Orç.</th>
                    <th class="px-4 py-3.5 text-right font-medium">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $i => $u):
                $ehEu      = (int) $u['id_usuario'] === $meuId;
                $ehSistema = (int) $u['is_system'] === 1;
            ?>
                <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-eco-surface/60' ?> border-t border-eco-line align-middle">
                    <td class="px-4 py-3.5">
                        <div class="font-medium">
                            <?= htmlspecialchars($u['nome_usuario']) ?>
                            <?php if ($ehEu): ?><span class="ml-1 text-[12px] font-normal text-eco-muted">(você)</span><?php endif; ?>
                        </div>
                        <div class="text-[13px] text-eco-muted"><?= htmlspecialchars($u['login']) ?></div>
                    </td>
                    <td class="px-4 py-3.5"><?= htmlspecialchars($u['nome_perfil'] ?? '—') ?></td>
                    <td class="px-4 py-3.5">
                        <?php if ($ehSistema): ?>
                            <span class="text-[13px] text-eco-muted">Conta de sistema</span>
                        <?php elseif ((int) $u['ativo'] === 1): ?>
                            <span class="inline-flex items-center gap-1.5 text-[13px] text-[#2f6b4f]"><span class="size-[7px] rounded-full bg-[#5FCE93]"></span> Ativo</span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 text-[13px] text-[#b5502f]"><span class="size-[7px] rounded-full bg-[#b5502f]"></span> Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3.5"><?= (int) $u['qtd_orcamentos'] ?></td>
                    <td class="px-4 py-3.5">
                        <?php if (!$ehSistema): ?>
                        <div class="flex items-center justify-end gap-3 text-[13.5px]">
                            <a href="usuario-form.php?id=<?= (int) $u['id_usuario'] ?>" class="font-semibold text-eco-green hover:underline">Editar</a>

                            <?php if (!$ehEu): ?>
                                <form action="usuario-handler.php" method="post" class="m-0">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                                    <input type="hidden" name="back" value="<?= htmlspecialchars(http_build_query($filtros + ['page' => $pagina])) ?>">
                                    <button type="submit" class="text-eco-muted hover:text-eco-ink hover:underline">
                                        <?= (int) $u['ativo'] === 1 ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                </form>

                                <form action="usuario-handler.php" method="post" class="m-0"
                                      onsubmit="return confirm('Excluir definitivamente <?= htmlspecialchars(addslashes($u['nome_usuario']), ENT_QUOTES) ?>? Essa ação não pode ser desfeita.');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                                    <input type="hidden" name="back" value="<?= htmlspecialchars(http_build_query($filtros + ['page' => $pagina])) ?>">
                                    <button type="submit" class="text-red-600 hover:underline">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-[13.5px] text-eco-muted">
        <span><?= $total ?> usuário<?= $total === 1 ? '' : 's' ?> — página <?= $pagina ?> de <?= $totalPaginas ?></span>
        <div class="flex gap-2">
            <?php if ($pagina > 1): ?>
                <a class="rounded-md border border-eco-line px-3 py-1.5 hover:border-eco-ink" href="usuarios.php?<?= htmlspecialchars(http_build_query($filtros + ['page' => $pagina - 1])) ?>">← Anterior</a>
            <?php endif; ?>
            <?php if ($pagina < $totalPaginas): ?>
                <a class="rounded-md border border-eco-line px-3 py-1.5 hover:border-eco-ink" href="usuarios.php?<?= htmlspecialchars(http_build_query($filtros + ['page' => $pagina + 1])) ?>">Próxima →</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>