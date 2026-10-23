<?php
require __DIR__ . '/includes/config.php';

// Proteção de rota: visitantes voltam para o login.
if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/auth-helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/relatorios.php'; // ESTOQUE_BAIXO_LIMITE (mesmo limite de placas.php)

if (!function_exists('brl')) {
    function brl(float $valor, int $casas = 0): string
    {
        return 'R$ ' . number_format($valor, $casas, ',', '.');
    }
}

$nomeUsuario = $auth['user']['name'] ?? $auth['user']['email'] ?? 'visitante';

// Equipe comercial (Vendedor e Administrador) vê o painel operacional;
// o Cliente continua vendo apenas os atalhos da própria conta.
$ehEquipe = userCan('consultar_orcamentos') || userCan('consultar_clientes') || userCan('consultar_estoque');

$variant   = 'account';
$pageTitle = 'EcoVolts - Menu';

// ---------------------------------------------------------------------------
// Dados do painel (somente equipe) — cada bloco respeita a permissão da área.
// ---------------------------------------------------------------------------
if ($ehEquipe) {
    $db = getDb();

    $meses = [1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
              'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    $nomeMesAtual = $meses[(int) date('n')];

    $pode = [
        'orcamentos' => userCan('consultar_orcamentos'),
        'clientes'   => userCan('consultar_clientes'),
        'estoque'    => userCan('consultar_estoque'),
    ];

    $orcamentosMes = ['qtd' => 0, 'total' => 0.0];
    $orcamentosMesAnterior = ['qtd' => 0, 'total' => 0.0];
    $orcamentosRecentes = [];
    $qtdClientes = 0;
    $qtdEstoqueBaixo = 0;
    $alertasEstoque = [];

    if ($pode['orcamentos']) {
        $somarPeriodo = $db->prepare(
            'SELECT COUNT(*) AS qtd, COALESCE(SUM(investimento), 0) AS total
             FROM Orcamento
             WHERE data_emissao BETWEEN :inicio AND :fim'
        );

        $somarPeriodo->execute(['inicio' => date('Y-m-01'), 'fim' => date('Y-m-t')]);
        $orcamentosMes = $somarPeriodo->fetch();

        $primeiroDiaAnterior = date('Y-m-01', strtotime('first day of last month'));
        $somarPeriodo->execute(['inicio' => $primeiroDiaAnterior, 'fim' => date('Y-m-t', strtotime($primeiroDiaAnterior))]);
        $orcamentosMesAnterior = $somarPeriodo->fetch();

        $orcamentosRecentes = $db->query(
            'SELECT o.id_orcamento, o.num_orcamento, o.data_emissao, o.investimento,
                    c.nome_usuario AS cliente_nome, t.comporta_placas
             FROM Orcamento o
             JOIN Usuario c ON c.id_usuario = o.id_usuario_cliente
             LEFT JOIN Telhado t ON t.id_telhado = o.id_telhado
             ORDER BY o.data_emissao DESC, o.id_orcamento DESC
             LIMIT 6'
        )->fetchAll();
    }

    if ($pode['clientes']) {
        $qtdClientes = (int) $db->query(
            "SELECT COUNT(*)
             FROM Usuario u JOIN Perfil p ON p.id_perfil = u.id_perfil
             WHERE p.nome_perfil = 'Cliente' AND u.is_system = 0"
        )->fetchColumn();
    }

    if ($pode['estoque']) {
        $consultaBaixo = $db->prepare('SELECT COUNT(*) FROM Estoque WHERE quantidade <= :limite');
        $consultaBaixo->execute(['limite' => ESTOQUE_BAIXO_LIMITE]);
        $qtdEstoqueBaixo = (int) $consultaBaixo->fetchColumn();

        $consultaAlertas = $db->prepare(
            'SELECT p.nome_produto, e.quantidade
             FROM Estoque e JOIN Produto p ON p.id_produto = e.id_produto
             WHERE e.quantidade <= :limite
             ORDER BY e.quantidade, p.nome_produto
             LIMIT 5'
        );
        $consultaAlertas->execute(['limite' => ESTOQUE_BAIXO_LIMITE]);
        $alertasEstoque = $consultaAlertas->fetchAll();
    }

    // Indicadores exibidos (só os das áreas que o usuário pode consultar).
    $indicadores = [];
    if ($pode['orcamentos']) {
        $indicadores[] = [
            'rotulo' => 'Orçamentos em ' . $nomeMesAtual,
            'valor'  => number_format((int) $orcamentosMes['qtd'], 0, ',', '.'),
            'apoio'  => 'Mês anterior: ' . number_format((int) $orcamentosMesAnterior['qtd'], 0, ',', '.'),
            'alerta' => false,
        ];
        $indicadores[] = [
            'rotulo' => 'Valor orçado no mês',
            'valor'  => brl((float) $orcamentosMes['total']),
            'apoio'  => 'Mês anterior: ' . brl((float) $orcamentosMesAnterior['total']),
            'alerta' => false,
        ];
    }
    if ($pode['clientes']) {
        $indicadores[] = [
            'rotulo' => 'Clientes cadastrados',
            'valor'  => number_format($qtdClientes, 0, ',', '.'),
            'apoio'  => 'Total na base',
            'alerta' => false,
        ];
    }
    if ($pode['estoque']) {
        $indicadores[] = [
            'rotulo' => 'Produtos com estoque baixo',
            'valor'  => number_format($qtdEstoqueBaixo, 0, ',', '.'),
            'apoio'  => ESTOQUE_BAIXO_LIMITE . ' unidades ou menos',
            'alerta' => $qtdEstoqueBaixo > 0,
        ];
    }

    // Acesso rápido: cartões compactos (ícone, título e seta).
    $acessosRapidos = [
        ['rota' => 'clientes',             'icone' => 'users-round',     'titulo' => 'Consultar clientes',    'permissao' => 'consultar_clientes'],
        ['rota' => 'orcamentos',           'icone' => 'files',           'titulo' => 'Todos os orçamentos',   'permissao' => 'consultar_orcamentos'],
        ['rota' => 'placas',               'icone' => 'panels-top-left', 'titulo' => 'Placas / Estoque',      'permissao' => 'consultar_estoque'],
        ['rota' => 'movimentacao_estoque', 'icone' => 'arrow-left-right', 'titulo' => 'Movimentações',        'permissao' => 'consultar_movimentacao_estoque'],
        ['rota' => 'historico',            'icone' => 'history',         'titulo' => 'Meus orçamentos',       'permissao' => null],
        ['rota' => 'perfil',               'icone' => 'user-round',      'titulo' => 'Meu perfil',            'permissao' => null],
    ];
    $acessosRapidos = array_values(array_filter(
        $acessosRapidos,
        fn (array $acesso) => $acesso['permissao'] === null || userCan($acesso['permissao'])
    ));
}

require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Área da conta
</div>

<?php if (!$ehEquipe): ?>
    <?php /* ===================== MENU DO CLIENTE (inalterado) ===================== */ ?>
    <h1 class="mb-1.5 font-serif text-[34px] font-medium">Olá, <?= htmlspecialchars($nomeUsuario) ?></h1>
    <p class="text-eco-muted mb-8">Comece um novo orçamento ou revise seu último relatório.</p>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <a href="<?= route('orcamento') ?>"
           class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
            <i data-lucide="sun-medium" class="text-eco-amber mb-3" width="28" height="28"></i>
            <h2 class="font-serif text-xl mb-1">Novo orçamento</h2>
            <p class="text-sm text-eco-muted">Informe sua conta de energia e os dados do telhado.</p>
        </a>

        <a href="<?= route('historico') ?>"
           class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
            <i data-lucide="history" class="text-eco-amber mb-3" width="28" height="28"></i>
            <h2 class="font-serif text-xl mb-1">Meus orçamentos</h2>
            <p class="text-sm text-eco-muted">Veja o payback e a economia estimada de cada orçamento já simulado.</p>
        </a>

        <a href="<?= route('perfil') ?>"
           class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px] hover:border-eco-green transition-colors">
            <i data-lucide="user-round" class="text-eco-amber mb-3" width="28" height="28"></i>
            <h2 class="font-serif text-xl mb-1">Meu perfil</h2>
            <p class="text-sm text-eco-muted">Atualize seus dados, endereço e senha.</p>
        </a>
    </div>

<?php else: ?>
    <?php /* ============ PAINEL DA EQUIPE (Vendedor e Administrador) ============ */ ?>

    <!-- CABEÇALHO -->
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="mb-1 font-serif text-[34px] font-medium">Olá, <?= htmlspecialchars($nomeUsuario) ?></h1>
            <p class="m-0 text-eco-muted">Resumo da sua operação hoje, <?= date('d/m/Y') ?>.</p>
        </div>
        <?php if (userCan('simular_orcamento')): ?>
            <a href="<?= route('orcamento') ?>"
               class="inline-flex items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
                <i data-lucide="plus" width="18" height="18"></i> Novo orçamento
            </a>
        <?php endif; ?>
    </div>

    <!-- INDICADORES -->
    <?php if ($indicadores): ?>
    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <?php foreach ($indicadores as $indicador): ?>
            <div class="rounded-[10px] border border-eco-line bg-eco-surface p-5">
                <div class="text-[13px] text-eco-muted"><?= htmlspecialchars($indicador['rotulo']) ?></div>
                <div class="mt-1 font-serif text-3xl <?= $indicador['alerta'] ? 'text-[#b5502f]' : '' ?>">
                    <?= htmlspecialchars($indicador['valor']) ?>
                </div>
                <div class="mt-1 text-xs text-eco-muted"><?= htmlspecialchars($indicador['apoio']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ACESSO RÁPIDO -->
    <h2 class="mb-3 font-serif text-xl">Acesso rápido</h2>
    <div class="mb-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($acessosRapidos as $acesso): ?>
            <a href="<?= route($acesso['rota']) ?>"
               class="flex items-center gap-3 rounded-[10px] border border-eco-line bg-eco-surface px-4 py-3.5 transition-colors hover:border-eco-green">
                <i data-lucide="<?= htmlspecialchars($acesso['icone']) ?>" class="shrink-0 text-eco-amber" width="22" height="22"></i>
                <span class="flex-1 text-[14.5px] font-medium"><?= htmlspecialchars($acesso['titulo']) ?></span>
                <i data-lucide="chevron-right" class="shrink-0 text-eco-muted" width="18" height="18"></i>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ACOMPANHAMENTO -->
    <?php if ($pode['orcamentos'] || $pode['estoque']): ?>
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        <?php if ($pode['orcamentos']): ?>
        <section class="rounded-[10px] border border-eco-line bg-white p-6 <?= $pode['estoque'] ? 'lg:col-span-2' : 'lg:col-span-3' ?>">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="font-serif text-xl">Orçamentos recentes</h2>
                <a href="<?= route('orcamentos') ?>" class="text-[13.5px] font-semibold text-eco-green hover:underline">Ver todos</a>
            </div>

            <?php if (empty($orcamentosRecentes)): ?>
                <p class="text-sm text-eco-muted">Nenhum orçamento registrado ainda.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-[14px]">
                        <thead>
                            <tr class="text-eco-muted">
                                <th class="pb-3 font-medium">Orçamento</th>
                                <th class="pb-3 font-medium">Cliente</th>
                                <th class="pb-3 font-medium">Data</th>
                                <th class="pb-3 text-right font-medium">Investimento</th>
                                <th class="pb-3 pl-4 font-medium">Telhado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orcamentosRecentes as $orcamento): ?>
                            <tr class="border-t border-eco-line">
                                <td class="py-3 font-medium">
                                    <a href="orcamento-detalhe.php?id=<?= (int) $orcamento['id_orcamento'] ?>" class="text-eco-green hover:underline">
                                        <?= htmlspecialchars($orcamento['num_orcamento']) ?>
                                    </a>
                                </td>
                                <td class="py-3"><?= htmlspecialchars($orcamento['cliente_nome']) ?></td>
                                <td class="py-3"><?= $orcamento['data_emissao'] ? date('d/m/Y', strtotime($orcamento['data_emissao'])) : '—' ?></td>
                                <td class="py-3 text-right"><?= $orcamento['investimento'] !== null ? brl((float) $orcamento['investimento']) : '—' ?></td>
                                <td class="py-3 pl-4">
                                    <?php if ((int) $orcamento['comporta_placas'] === 1): ?>
                                        <span class="inline-flex items-center gap-1.5 text-[13px] text-[#2f6b4f]"><span class="size-[7px] rounded-full bg-[#5FCE93]"></span> Comporta</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 text-[13px] text-[#b5502f]"><span class="size-[7px] rounded-full bg-eco-amber"></span> Verificar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <?php if ($pode['estoque']): ?>
        <section class="rounded-[10px] border border-eco-line bg-white p-6 <?= $pode['orcamentos'] ? '' : 'lg:col-span-3' ?>">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="font-serif text-xl">Alertas de estoque</h2>
                <a href="<?= route('placas') ?>" class="text-[13.5px] font-semibold text-eco-green hover:underline">Ver estoque</a>
            </div>

            <?php if (empty($alertasEstoque)): ?>
                <p class="flex items-center gap-2 text-sm text-[#2f6b4f]">
                    <span class="size-[7px] rounded-full bg-[#5FCE93]"></span> Nenhum produto com estoque baixo.
                </p>
            <?php else: ?>
                <ul class="m-0 list-none p-0">
                    <?php foreach ($alertasEstoque as $alerta): ?>
                        <li class="flex items-center justify-between gap-3 border-t border-eco-line py-3 first:border-t-0 first:pt-0 text-[14px]">
                            <span class="font-medium"><?= htmlspecialchars($alerta['nome_produto']) ?></span>
                            <span class="whitespace-nowrap font-semibold text-[#b5502f]">
                                <?= number_format((int) $alerta['quantidade'], 0, ',', '.') ?> un.
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($qtdEstoqueBaixo > count($alertasEstoque)): ?>
                    <p class="mt-3 text-[13px] text-eco-muted">
                        e mais <?= $qtdEstoqueBaixo - count($alertasEstoque) ?> produto(s) com estoque baixo.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        <?php endif; ?>

    </div>
    <?php endif; ?>
<?php endif; ?>

<a href="auth/logout.php" class="mt-10 inline-flex items-center gap-2 text-sm text-eco-muted hover:text-eco-ink">
    <i data-lucide="log-out" width="16" height="16"></i> Sair
</a>

<?php require __DIR__ . '/includes/footer.php'; ?>
