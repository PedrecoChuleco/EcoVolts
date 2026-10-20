<?php
/**
 * Menu lateral da área logada (variante 'account').
 *
 * A logo mora aqui, no topo do menu, e rola junto com a página — não existe
 * mais uma barra fixa no topo da área do usuário. No celular o menu vira uma
 * faixa horizontal (logo + links rolando para o lado).
 *
 * Para adicionar um link: acrescente uma linha em $secoes.
 *   'rota'      nome da rota (ver route() em includes/config.php)
 *   'texto'     rótulo exibido
 *   'ativo'     true quando a página atual pertence a este item
 *   'permissao' (opcional) só aparece para quem tem essa permissão
 * Seções sem nenhum item visível não são exibidas.
 */
$secoes = [
    [
        'titulo' => 'Área da conta',
        'itens'  => [
            ['rota' => 'dashboard', 'texto' => 'Menu',      'ativo' => isActive('dashboard')],
            ['rota' => 'perfil',    'texto' => 'Perfil',    'ativo' => isActive('perfil')],
            // 'orcamento' é substring de 'orcamentos'/'orcamento-detalhe'/'orcamento-form':
            // sem essas exclusões, os dois itens "Orçamento" e "Orçamentos" acendiam juntos.
            ['rota' => 'orcamento', 'texto' => 'Orçamento', 'ativo' => isActive('orcamento')
                && !isActive('orcamentos') && !isActive('orcamento-detalhe') && !isActive('orcamento-form')],
            ['rota' => 'relatorio', 'texto' => 'Relatório', 'ativo' => isActive('relatorio')],
        ],
    ],
    [
        'titulo' => 'Comercial',
        'itens'  => [
            ['rota' => 'placas',               'texto' => 'Placas / Estoque', 'ativo' => isActive('placas'),               'permissao' => 'consultar_estoque'],
            ['rota' => 'movimentacao_estoque', 'texto' => 'Movimentações',    'ativo' => isActive('movimentacao-estoque'), 'permissao' => 'consultar_movimentacao_estoque'],
            ['rota' => 'clientes',             'texto' => 'Clientes',         'ativo' => isActive('clientes'),             'permissao' => 'consultar_clientes'],
            ['rota' => 'orcamentos',           'texto' => 'Orçamentos',       'ativo' => isActive('orcamentos') || isActive('orcamento-detalhe') || isActive('orcamento-form'), 'permissao' => 'consultar_orcamentos'],
        ],
    ],
    [
        'titulo' => 'Administração',
        'itens'  => [
            ['rota' => 'usuarios', 'texto' => 'Usuários', 'ativo' => isActive('usuario'), 'permissao' => 'administrar_usuarios'],
        ],
    ],
];

// Filtra pelos itens que este usuário pode ver e descarta seções vazias.
$secoes = array_values(array_filter(array_map(function (array $secao): array {
    $secao['itens'] = array_values(array_filter(
        $secao['itens'],
        fn (array $item) => !isset($item['permissao']) || userCan($item['permissao'])
    ));
    return $secao;
}, $secoes), fn (array $secao) => $secao['itens'] !== []));

$classeLink = fn (bool $ativo) => 'flex items-baseline gap-2.5 rounded-md border-l-2 px-2.5 py-2.5 text-[14.5px] '
    . ($ativo
        ? 'border-eco-amber bg-eco-amber/14 text-white'
        : 'border-transparent text-[#C7D8D1] hover:bg-white/5 hover:text-white');
?>
<aside class="flex flex-row items-center gap-6 overflow-x-auto bg-eco-ink px-5 py-4 text-eco-line md:flex-col md:items-stretch md:gap-8 md:py-6">
    <?php require __DIR__ . '/brand.php'; ?>

    <nav class="flex flex-row gap-0.5 md:flex-col">
        <?php foreach ($secoes as $i => $secao): ?>
            <p class="mb-2.5 ml-0.5 hidden text-xs text-[#9FB4AE] md:block<?= $i > 0 ? ' mt-5' : '' ?>"><?= htmlspecialchars($secao['titulo']) ?></p>
            <?php foreach ($secao['itens'] as $item): ?>
                <a href="<?= route($item['rota']) ?>" class="<?= $classeLink($item['ativo']) ?>"><?= htmlspecialchars($item['texto']) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
</aside>
