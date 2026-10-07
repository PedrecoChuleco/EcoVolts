<?php
require __DIR__ . '/includes/config.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/includes/db.php';

$db = getDb();
$stmt = $db->prepare(
    'SELECT o.id_orcamento, o.num_orcamento, o.data_emissao, o.investimento,
            o.payback, o.valor_totalCE, t.comporta_placas
     FROM Orcamento o
     LEFT JOIN Telhado t ON t.id_telhado = o.id_telhado
     WHERE o.id_usuario_cliente = :uid
     ORDER BY o.data_emissao DESC, o.id_orcamento DESC'
);
$stmt->execute(['uid' => $auth['user']['id']]);
$orcamentos = $stmt->fetchAll();

function brl(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

$variant   = 'account';
$pageTitle = 'EcoVolts - Histórico de orçamentos';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Histórico
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium">Meus orçamentos</h1>

<?php if (empty($orcamentos)): ?>
    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <p class="mb-4 text-eco-muted">Você ainda não simulou nenhum orçamento.</p>
        <a href="<?= route('orcamento') ?>"
           class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            Simular agora
        </a>
    </div>
<?php else: ?>
    <div class="overflow-hidden rounded-[10px] border border-eco-line">
        <table class="w-full border-collapse text-left text-[14.5px]">
            <thead>
                <tr class="bg-eco-surface text-eco-muted">
                    <th class="px-5 py-3.5 font-medium">Nº</th>
                    <th class="px-5 py-3.5 font-medium">Data</th>
                    <th class="px-5 py-3.5 font-medium">Conta atual</th>
                    <th class="px-5 py-3.5 font-medium">Investimento</th>
                    <th class="px-5 py-3.5 font-medium">Payback</th>
                    <th class="px-5 py-3.5 font-medium">Telhado</th>
                    <th class="px-5 py-3.5"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orcamentos as $i => $o): ?>
                    <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-eco-surface/60' ?> border-t border-eco-line">
                        <td class="px-5 py-3.5 font-medium"><?= htmlspecialchars($o['num_orcamento']) ?></td>
                        <td class="px-5 py-3.5"><?= htmlspecialchars(date('d/m/Y', strtotime($o['data_emissao']))) ?></td>
                        <td class="px-5 py-3.5"><?= brl((float) $o['valor_totalCE']) ?></td>
                        <td class="px-5 py-3.5"><?= brl((float) $o['investimento']) ?></td>
                        <td class="px-5 py-3.5"><?= $o['payback'] !== null ? htmlspecialchars((string) $o['payback']) . ' meses' : '—' ?></td>
                        <td class="px-5 py-3.5">
                            <?php if ((int) $o['comporta_placas'] === 1): ?>
                                <span class="inline-flex items-center gap-1.5 text-[13px] text-[#2f6b4f]">
                                    <span class="size-[7px] rounded-full bg-[#5FCE93]"></span> Comporta
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 text-[13px] text-[#b5502f]">
                                    <span class="size-[7px] rounded-full bg-eco-amber"></span> Verificar
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="relatorio.php?id=<?= (int) $o['id_orcamento'] ?>" class="font-semibold text-eco-green hover:underline">Ver relatório</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>