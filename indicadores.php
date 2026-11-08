<?php
/**
 * Dashboards da área comercial:  indicadores.php?area=orcamentos|clientes|estoque
 *
 *   orcamentos  Orçamentos x status (quantidade, valor, ticket médio), com filtro de período
 *   clientes    Clientes por região (gráfico de pizza)
 *   estoque     Situação do estoque por produto
 *
 * Os dados vêm de includes/relatorios.php (a mesma definição usada nas planilhas),
 * então o dashboard e o arquivo exportado sempre mostram os mesmos números.
 * Cada aba só aparece para quem tem a permissão de consulta da área.
 * Os gráficos são SVG gerados no servidor (sem biblioteca de gráficos).
 */
require __DIR__ . '/includes/config.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/auth-helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/relatorios.php';
require_once __DIR__ . '/includes/StatusOrcamento.php';

$areas = [
    'orcamentos' => ['titulo' => 'Orçamentos', 'permissao' => 'consultar_orcamentos', 'icone' => 'chart-column'],
    'clientes'   => ['titulo' => 'Clientes',   'permissao' => 'consultar_clientes',   'icone' => 'chart-pie'],
    'estoque'    => ['titulo' => 'Estoque',    'permissao' => 'consultar_estoque',    'icone' => 'boxes'],
];
$permitidas = array_filter($areas, fn (array $a) => userCan($a['permissao']));

if (!$permitidas) {
    // Mostra a página padrão de "Acesso negado".
    $requiredPermission = 'consultar_orcamentos';
    require __DIR__ . '/includes/require-permission.php';
    exit;
}

$area = (string) ($_GET['area'] ?? '');
if (!isset($permitidas[$area])) {
    $area = (string) array_key_first($permitidas);
}

$db = getDb();

function brl(float $valor, int $casas = 2): string
{
    return 'R$ ' . number_format($valor, $casas, ',', '.');
}

function inteiro(int|float $valor): string
{
    return number_format((int) $valor, 0, ',', '.');
}

function percentual(float $parte, float $total): string
{
    return $total > 0 ? number_format($parte / $total * 100, 1, ',', '.') . '%' : '0,0%';
}

/**
 * Gráfico de pizza em SVG. $fatias = [['rotulo' => , 'valor' => , 'cor' => '#hex'], ...]
 */
function graficoPizza(array $fatias, int $tamanho = 240): string
{
    $fatias = array_values(array_filter($fatias, fn (array $f) => $f['valor'] > 0));
    $total  = array_sum(array_column($fatias, 'valor'));

    if ($total <= 0) {
        return '<p class="text-sm text-eco-muted">Sem dados para exibir.</p>';
    }

    $centro = $tamanho / 2;
    $raio   = $centro - 2;
    $svg    = sprintf(
        '<svg viewBox="0 0 %1$d %1$d" width="%1$d" height="%1$d" role="img" aria-label="Gráfico de pizza" class="max-w-full">',
        $tamanho
    );

    if (count($fatias) === 1) {
        $svg .= sprintf(
            '<circle cx="%.2F" cy="%.2F" r="%.2F" fill="%s" stroke="#fff" stroke-width="2"><title>%s: %s</title></circle>',
            $centro, $centro, $raio, $fatias[0]['cor'],
            htmlspecialchars($fatias[0]['rotulo']), inteiro($fatias[0]['valor'])
        );
    } else {
        $angulo = -M_PI / 2;
        foreach ($fatias as $fatia) {
            $fracao = $fatia['valor'] / $total;
            $fim    = $angulo + 2 * M_PI * $fracao;
            $svg .= sprintf(
                '<path d="M %.2F %.2F L %.2F %.2F A %.2F %.2F 0 %d 1 %.2F %.2F Z" fill="%s" stroke="#fff" stroke-width="2"><title>%s: %s (%s)</title></path>',
                $centro, $centro,
                $centro + $raio * cos($angulo), $centro + $raio * sin($angulo),
                $raio, $raio, $fracao > 0.5 ? 1 : 0,
                $centro + $raio * cos($fim), $centro + $raio * sin($fim),
                $fatia['cor'],
                htmlspecialchars($fatia['rotulo']), inteiro($fatia['valor']), percentual($fatia['valor'], $total)
            );
            $angulo = $fim;
        }
    }

    return $svg . '</svg>';
}

/** Executa um relatório definido em includes/relatorios.php e devolve as linhas. */
function linhasDoRelatorio(PDO $db, array $relatorio): array
{
    $consulta = $db->prepare($relatorio['sql']);
    $consulta->execute($relatorio['params']);
    return $consulta->fetchAll();
}

$variant   = 'account';
$wide      = true;
$pageTitle = 'EcoVolts - Dashboard de ' . mb_strtolower($permitidas[$area]['titulo']);
require __DIR__ . '/includes/header.php';

$classeCartao = 'rounded-[10px] border border-eco-line bg-eco-surface p-5';
$classeBotao  = 'inline-flex items-center gap-2 rounded-md border border-eco-line bg-white px-4 py-2.5 text-[14px] font-semibold text-eco-green hover:border-eco-green';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="chart-column"></i></span> Área comercial
</div>
<h1 class="mb-4 font-serif text-[34px] font-medium">Dashboards</h1>

<nav class="mb-6 flex flex-wrap gap-2" aria-label="Áreas dos dashboards">
    <?php foreach ($permitidas as $chave => $info): ?>
        <a href="indicadores.php?area=<?= $chave ?>"
           class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-[14px] font-medium <?= $chave === $area ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line bg-white text-eco-muted hover:border-eco-green' ?>">
            <i data-lucide="<?= htmlspecialchars($info['icone']) ?>" width="16" height="16"></i>
            <?= htmlspecialchars($info['titulo']) ?>
        </a>
    <?php endforeach; ?>
</nav>

<?php if ($area === 'orcamentos'): ?>
<?php
    // ----------------------------------------------------------- ORÇAMENTOS x STATUS
    $filtros = [
        'data_inicial' => dataValida($_GET['data_inicial'] ?? null),
        'data_final'   => dataValida($_GET['data_final'] ?? null),
    ];
    $relatorio = relatorioOrcamentosStatus($filtros);
    $linhas    = [];
    foreach (linhasDoRelatorio($db, $relatorio) as $linha) {
        $linhas[$linha['status']] = $linha;
    }

    // Completa com zero os status sem orçamentos, na ordem do fluxo comercial.
    $tabela = [];
    $totalQtd = 0;
    $totalValor = 0.0;
    foreach (StatusOrcamento::cases() as $status) {
        $linha = $linhas[$status->value] ?? null;
        $qtd   = $linha ? (int) $linha['qtd_orcamentos'] : 0;
        $valor = $linha ? (float) $linha['valor_total_orcado'] : 0.0;
        $tabela[] = [
            'status' => $status,
            'qtd'    => $qtd,
            'valor'  => $valor,
            'ticket' => $qtd > 0 ? $valor / $qtd : null,
        ];
        $totalQtd   += $qtd;
        $totalValor += $valor;
    }
    $maiorQtd = max(array_column($tabela, 'qtd')) ?: 1;

    $aprovado = array_values(array_filter($tabela, fn (array $l) => $l['status'] === StatusOrcamento::Aprovado))[0];
    $filtrosUrl = http_build_query(array_filter($filtros));
?>
    <form method="get" class="mb-6 flex flex-wrap items-end gap-3 rounded-[10px] border border-eco-line bg-white p-4">
        <input type="hidden" name="area" value="orcamentos">
        <div>
            <label for="data_inicial" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Emitidos a partir de</label>
            <input id="data_inicial" type="date" name="data_inicial" value="<?= htmlspecialchars((string) $filtros['data_inicial']) ?>"
                   class="rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] outline-none focus-visible:border-eco-green">
        </div>
        <div>
            <label for="data_final" class="mb-1.5 block text-[13px] font-medium text-eco-muted">até</label>
            <input id="data_final" type="date" name="data_final" value="<?= htmlspecialchars((string) $filtros['data_final']) ?>"
                   class="rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 text-[14px] outline-none focus-visible:border-eco-green">
        </div>
        <button type="submit" class="rounded-md bg-eco-ink px-[18px] py-2.5 text-[14.5px] font-semibold text-white hover:bg-eco-dark">Filtrar</button>
        <?php if ($filtrosUrl): ?>
            <a href="indicadores.php?area=orcamentos" class="px-1 text-[14px] text-eco-muted hover:text-eco-ink">Limpar</a>
        <?php endif; ?>
        <a href="exportar.php?tipo=orcamentos_status<?= $filtrosUrl ? '&' . htmlspecialchars($filtrosUrl) : '' ?>"
           class="<?= $classeBotao ?> ml-auto">
            <i data-lucide="file-spreadsheet" width="17" height="17"></i> Exportar planilha
        </a>
    </form>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Orçamentos</div>
            <div class="mt-1 font-serif text-3xl"><?= inteiro($totalQtd) ?></div>
            <div class="mt-1 text-xs text-eco-muted">no período</div>
        </div>
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Valor orçado</div>
            <div class="mt-1 font-serif text-3xl"><?= brl($totalValor, 0) ?></div>
            <div class="mt-1 text-xs text-eco-muted">soma dos investimentos</div>
        </div>
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Valor aprovado</div>
            <div class="mt-1 font-serif text-3xl"><?= brl($aprovado['valor'], 0) ?></div>
            <div class="mt-1 text-xs text-eco-muted"><?= inteiro($aprovado['qtd']) ?> orçamento(s) aprovado(s)</div>
        </div>
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Taxa de aprovação</div>
            <div class="mt-1 font-serif text-3xl"><?= percentual($aprovado['qtd'], $totalQtd) ?></div>
            <div class="mt-1 text-xs text-eco-muted">aprovados ÷ total</div>
        </div>
    </div>

    <section class="rounded-[10px] border border-eco-line bg-white p-6">
        <h2 class="mb-1 font-serif text-xl">Orçamentos × status</h2>
        <p class="mb-5 text-[13px] text-eco-muted">Quantidade, valor orçado e ticket médio em cada etapa do funil.</p>

        <?php if ($totalQtd === 0): ?>
            <p class="text-sm text-eco-muted">Nenhum orçamento no período selecionado.</p>
        <?php else: ?>
            <div class="mb-6 flex flex-col gap-3">
                <?php foreach ($tabela as $linha): ?>
                    <div class="grid grid-cols-[130px_1fr_auto] items-center gap-3 text-[14px]">
                        <span><?= StatusOrcamento::selo($linha['status']->value) ?></span>
                        <div class="h-5 overflow-hidden rounded bg-eco-surface" role="img"
                             aria-label="<?= htmlspecialchars($linha['status']->label()) ?>: <?= $linha['qtd'] ?>">
                            <div class="h-full rounded" style="width: <?= $linha['qtd'] / $maiorQtd * 100 ?>%; background: <?= $linha['status']->corGrafico() ?>"></div>
                        </div>
                        <span class="min-w-[90px] text-right font-semibold">
                            <?= inteiro($linha['qtd']) ?> <span class="font-normal text-eco-muted">(<?= percentual($linha['qtd'], $totalQtd) ?>)</span>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-[14px]">
                    <thead>
                        <tr class="text-eco-muted">
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 text-right font-medium">Orçamentos</th>
                            <th class="pb-3 text-right font-medium">% do total</th>
                            <th class="pb-3 text-right font-medium">Valor orçado</th>
                            <th class="pb-3 text-right font-medium">Ticket médio</th>
                            <th class="pb-3 text-right font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tabela as $linha): ?>
                        <tr class="border-t border-eco-line">
                            <td class="py-3"><?= StatusOrcamento::selo($linha['status']->value) ?></td>
                            <td class="py-3 text-right"><?= inteiro($linha['qtd']) ?></td>
                            <td class="py-3 text-right"><?= percentual($linha['qtd'], $totalQtd) ?></td>
                            <td class="py-3 text-right"><?= brl($linha['valor']) ?></td>
                            <td class="py-3 text-right"><?= $linha['ticket'] !== null ? brl($linha['ticket']) : '—' ?></td>
                            <td class="py-3 text-right">
                                <?php if ($linha['qtd'] > 0): ?>
                                    <a href="orcamentos.php?status=<?= $linha['status']->value ?>" class="text-[13.5px] font-semibold text-eco-green hover:underline">Ver</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-eco-line font-semibold">
                            <td class="pt-3">Total</td>
                            <td class="pt-3 text-right"><?= inteiro($totalQtd) ?></td>
                            <td class="pt-3 text-right">100,0%</td>
                            <td class="pt-3 text-right"><?= brl($totalValor) ?></td>
                            <td class="pt-3 text-right"><?= $totalQtd > 0 ? brl($totalValor / $totalQtd) : '—' ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </section>

<?php elseif ($area === 'clientes'): ?>
<?php
    // ------------------------------------------------------- CLIENTES POR REGIÃO
    $coresRegiao = [
        'Sudeste'      => '#FFDA21',
        'Sul'          => '#404E44',
        'Nordeste'     => '#5B8DEF',
        'Centro-Oeste' => '#5FCE93',
        'Norte'        => '#b5502f',
        'Sem endereço' => '#94A89A',
    ];

    $regioes = [];
    $totalClientes = 0;
    foreach (linhasDoRelatorio($db, relatorioClientesRegiao()) as $linha) {
        $qtd = (int) $linha['qtd_clientes'];
        $regioes[] = [
            'rotulo' => $linha['regiao'],
            'valor'  => $qtd,
            'ativos' => (int) $linha['qtd_clientes_ativos'],
            'cor'    => $coresRegiao[$linha['regiao']] ?? '#212121',
        ];
        $totalClientes += $qtd;
    }

    $semEndereco = 0;
    foreach ($regioes as $regiao) {
        if ($regiao['rotulo'] === 'Sem endereço') {
            $semEndereco = $regiao['valor'];
        }
    }

    $comOrcamento = (int) $db->query('SELECT COUNT(DISTINCT id_usuario_cliente) FROM Orcamento')->fetchColumn();
?>
    <div class="mb-6 flex flex-wrap items-center justify-end gap-3">
        <a href="exportar.php?tipo=clientes_regiao" class="<?= $classeBotao ?>">
            <i data-lucide="file-spreadsheet" width="17" height="17"></i> Exportar por região
        </a>
        <a href="exportar.php?tipo=clientes" class="<?= $classeBotao ?>">
            <i data-lucide="file-spreadsheet" width="17" height="17"></i> Exportar clientes
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Clientes cadastrados</div>
            <div class="mt-1 font-serif text-3xl"><?= inteiro($totalClientes) ?></div>
        </div>
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Com orçamento</div>
            <div class="mt-1 font-serif text-3xl"><?= inteiro($comOrcamento) ?></div>
            <div class="mt-1 text-xs text-eco-muted"><?= percentual($comOrcamento, $totalClientes) ?> da base</div>
        </div>
        <div class="<?= $classeCartao ?> col-span-2 lg:col-span-1">
            <div class="text-[13px] text-eco-muted">Sem endereço cadastrado</div>
            <div class="mt-1 font-serif text-3xl"><?= inteiro($semEndereco) ?></div>
            <div class="mt-1 text-xs text-eco-muted">não entram em nenhuma região</div>
        </div>
    </div>

    <section class="rounded-[10px] border border-eco-line bg-white p-6">
        <h2 class="mb-1 font-serif text-xl">Clientes por região</h2>
        <p class="mb-5 text-[13px] text-eco-muted">Macrorregião do Brasil, definida pelo estado do endereço residencial.</p>

        <?php if ($totalClientes === 0): ?>
            <p class="text-sm text-eco-muted">Nenhum cliente cadastrado ainda.</p>
        <?php else: ?>
            <div class="flex flex-wrap items-center gap-10">
                <div class="mx-auto sm:mx-0"><?= graficoPizza($regioes) ?></div>

                <div class="min-w-[260px] flex-1 overflow-x-auto">
                    <table class="w-full border-collapse text-left text-[14px]">
                        <thead>
                            <tr class="text-eco-muted">
                                <th class="pb-3 font-medium">Região</th>
                                <th class="pb-3 text-right font-medium">Clientes</th>
                                <th class="pb-3 text-right font-medium">%</th>
                                <th class="pb-3 text-right font-medium">Ativos</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($regioes as $regiao): ?>
                            <tr class="border-t border-eco-line">
                                <td class="py-3">
                                    <span class="mr-2 inline-block size-3 rounded-sm align-middle" style="background: <?= $regiao['cor'] ?>"></span>
                                    <?= htmlspecialchars($regiao['rotulo']) ?>
                                </td>
                                <td class="py-3 text-right font-semibold"><?= inteiro($regiao['valor']) ?></td>
                                <td class="py-3 text-right"><?= percentual($regiao['valor'], $totalClientes) ?></td>
                                <td class="py-3 text-right"><?= inteiro($regiao['ativos']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </section>

<?php else: ?>
<?php
    // ------------------------------------------------------------------- ESTOQUE
    $produtos = linhasDoRelatorio($db, relatorioEstoque());
    usort($produtos, fn (array $a, array $b) => (int) $b['quantidade_estoque'] <=> (int) $a['quantidade_estoque']);

    $totalUnidades = array_sum(array_map(fn (array $p) => (int) $p['quantidade_estoque'], $produtos));
    $valorEmEstoque = array_sum(array_map(fn (array $p) => (float) $p['valor_em_estoque'], $produtos));
    $qtdBaixo = count(array_filter($produtos, fn (array $p) => (int) $p['estoque_baixo'] === 1));
    $maiorQuantidade = max(array_map(fn (array $p) => (int) $p['quantidade_estoque'], $produtos) ?: [1]) ?: 1;
?>
    <div class="mb-6 flex justify-end">
        <a href="exportar.php?tipo=estoque" class="<?= $classeBotao ?>">
            <i data-lucide="file-spreadsheet" width="17" height="17"></i> Exportar planilha
        </a>
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Itens em estoque</div>
            <div class="mt-1 font-serif text-3xl"><?= inteiro(count($produtos)) ?></div>
        </div>
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Unidades</div>
            <div class="mt-1 font-serif text-3xl"><?= inteiro($totalUnidades) ?></div>
        </div>
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Valor em estoque</div>
            <div class="mt-1 font-serif text-3xl"><?= brl($valorEmEstoque, 0) ?></div>
        </div>
        <div class="<?= $classeCartao ?>">
            <div class="text-[13px] text-eco-muted">Estoque baixo</div>
            <div class="mt-1 font-serif text-3xl <?= $qtdBaixo > 0 ? 'text-[#b5502f]' : '' ?>"><?= inteiro($qtdBaixo) ?></div>
            <div class="mt-1 text-xs text-eco-muted"><?= ESTOQUE_BAIXO_LIMITE ?> unidades ou menos</div>
        </div>
    </div>

    <section class="rounded-[10px] border border-eco-line bg-white p-6">
        <h2 class="mb-5 font-serif text-xl">Quantidade por produto</h2>
        <?php if (empty($produtos)): ?>
            <p class="text-sm text-eco-muted">Nenhum item de estoque cadastrado.</p>
        <?php else: ?>
            <div class="flex flex-col gap-3">
                <?php foreach ($produtos as $produto):
                    $quantidade = (int) $produto['quantidade_estoque'];
                    $baixo = (int) $produto['estoque_baixo'] === 1;
                ?>
                    <div class="grid grid-cols-1 items-center gap-1 text-[14px] md:grid-cols-[260px_1fr_auto] md:gap-3">
                        <span class="font-medium"><?= htmlspecialchars($produto['produto']) ?></span>
                        <div class="h-5 overflow-hidden rounded bg-eco-surface" role="img"
                             aria-label="<?= htmlspecialchars($produto['produto']) ?>: <?= $quantidade ?> unidades">
                            <div class="h-full rounded" style="width: <?= $quantidade / $maiorQuantidade * 100 ?>%; background: <?= $baixo ? '#b5502f' : '#404E44' ?>"></div>
                        </div>
                        <span class="min-w-[120px] text-right font-semibold <?= $baixo ? 'text-[#b5502f]' : '' ?>">
                            <?= inteiro($quantidade) ?> un.
                            <span class="font-normal text-eco-muted">· <?= brl((float) $produto['valor_em_estoque'], 0) ?></span>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
