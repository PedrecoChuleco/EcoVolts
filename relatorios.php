<?php
/**
 * Definição dos relatórios tabulares (Orçamentos, Clientes, Estoque).
 *
 * Cada relatório é um conjunto "objetivo" de dados: UMA LINHA POR REGISTRO,
 * colunas planas em snake_case, valores numéricos sem símbolo de moeda, datas
 * em ISO (AAAA-MM-DD), indicadores como 0/1 e nada de células mescladas ou
 * totais embutidos. É o formato esperado por ferramentas de dashboard
 * (Power BI, Looker Studio, Metabase, planilhas dinâmicas).
 *
 * Esta é a ÚNICA fonte da definição dos relatórios: a exportação em planilha
 * (exportar.php) e, no futuro, as telas de dashboard devem usar estas funções
 * em vez de repetir SQL.
 *
 * Cada função recebe os filtros e devolve:
 *   'arquivo'   => nome-base do arquivo exportado
 *   'permissao' => permissão necessária (tabela Permissao)
 *   'colunas'   => lista de colunas: ['campo' => alias no SQL, 'titulo' => cabeçalho,
 *                  'tipo' => texto|inteiro|decimal|data, 'casas' => casas decimais (opcional)]
 *   'sql'       => consulta com parâmetros nomeados
 *   'params'    => valores dos parâmetros
 *
 * CATÁLOGO
 * ---------------------------------------------------------------------------
 * orcamentos  (1 linha por orçamento)   filtros: cliente, vendedor, data_inicial, data_final
 * clientes    (1 linha por cliente)     filtros: q (nome/e-mail)
 *             Não exporta CPF/CNPJ (dado pessoal desnecessário para dashboards):
 *             traz apenas tipo_pessoa (PF/PJ).
 * estoque     (1 linha por produto)     sem filtros
 */

/** Tipos de relatório disponíveis e a função que monta cada um. */
function relatoriosDisponiveis(): array
{
    return [
        'orcamentos' => 'relatorioOrcamentos',
        'clientes'   => 'relatorioClientes',
        'estoque'    => 'relatorioEstoque',
    ];
}

function dataValida(?string $data): ?string
{
    return ($data !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) && strtotime($data) !== false) ? $data : null;
}

function relatorioOrcamentos(array $filtros = []): array
{
    $where  = [];
    $params = [];

    if ((int) ($filtros['cliente'] ?? 0) > 0) {
        $where[] = 'o.id_usuario_cliente = :cliente';
        $params['cliente'] = (int) $filtros['cliente'];
    }
    if ((int) ($filtros['vendedor'] ?? 0) > 0) {
        $where[] = 'o.id_usuario_vendedor = :vendedor';
        $params['vendedor'] = (int) $filtros['vendedor'];
    }
    if ($inicial = dataValida($filtros['data_inicial'] ?? null)) {
        $where[] = 'o.data_emissao >= :data_inicial';
        $params['data_inicial'] = $inicial;
    }
    if ($final = dataValida($filtros['data_final'] ?? null)) {
        $where[] = 'o.data_emissao <= :data_final';
        $params['data_final'] = $final;
    }

    $sql = "SELECT o.id_orcamento,
                   o.num_orcamento,
                   o.data_emissao,
                   DATE_FORMAT(o.data_emissao, '%Y-%m') AS ano_mes,
                   c.id_usuario   AS id_cliente,
                   c.nome_usuario AS cliente,
                   v.id_usuario   AS id_vendedor,
                   v.nome_usuario AS vendedor,
                   o.valor_totalCE   AS valor_conta_atual,
                   o.valor_totalCEPI AS valor_conta_pos_solar,
                   (o.valor_totalCE - o.valor_totalCEPI) AS economia_mensal,
                   o.investimento,
                   o.payback AS payback_meses,
                   o.padrao_ligacao,
                   o.padrao_estimado,
                   t.area_telhado AS area_telhado_m2,
                   t.direcao_telhado,
                   t.area_estimada,
                   t.direcao_estimada,
                   t.comporta_placas,
                   (SELECT COUNT(*) FROM Orcamento_Prod op
                     WHERE op.id_orcamento = o.id_orcamento) AS qtd_itens,
                   (SELECT COALESCE(SUM(op.qtd), 0) FROM Orcamento_Prod op
                      JOIN Produto p ON p.id_produto = op.id_produto
                     WHERE op.id_orcamento = o.id_orcamento
                       AND p.nome_produto LIKE 'Painel Solar%') AS qtd_placas,
                   (SELECT COALESCE(SUM(op.qtd * op.valor_unitario - COALESCE(op.desconto, 0)), 0)
                      FROM Orcamento_Prod op
                     WHERE op.id_orcamento = o.id_orcamento) AS valor_total_itens
            FROM Orcamento o
            JOIN Usuario c ON c.id_usuario = o.id_usuario_cliente
            JOIN Usuario v ON v.id_usuario = o.id_usuario_vendedor
            LEFT JOIN Telhado t ON t.id_telhado = o.id_telhado"
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . ' ORDER BY o.data_emissao, o.id_orcamento';

    return [
        'arquivo'   => 'orcamentos',
        'permissao' => 'consultar_orcamentos',
        'colunas'   => [
            ['campo' => 'id_orcamento',          'titulo' => 'id_orcamento',          'tipo' => 'inteiro'],
            ['campo' => 'num_orcamento',         'titulo' => 'num_orcamento',         'tipo' => 'texto'],
            ['campo' => 'data_emissao',          'titulo' => 'data_emissao',          'tipo' => 'data'],
            ['campo' => 'ano_mes',               'titulo' => 'ano_mes',               'tipo' => 'texto'],
            ['campo' => 'id_cliente',            'titulo' => 'id_cliente',            'tipo' => 'inteiro'],
            ['campo' => 'cliente',               'titulo' => 'cliente',               'tipo' => 'texto'],
            ['campo' => 'id_vendedor',           'titulo' => 'id_vendedor',           'tipo' => 'inteiro'],
            ['campo' => 'vendedor',              'titulo' => 'vendedor',              'tipo' => 'texto'],
            ['campo' => 'valor_conta_atual',     'titulo' => 'valor_conta_atual',     'tipo' => 'decimal'],
            ['campo' => 'valor_conta_pos_solar', 'titulo' => 'valor_conta_pos_solar', 'tipo' => 'decimal'],
            ['campo' => 'economia_mensal',       'titulo' => 'economia_mensal',       'tipo' => 'decimal'],
            ['campo' => 'investimento',          'titulo' => 'investimento',          'tipo' => 'decimal'],
            ['campo' => 'payback_meses',         'titulo' => 'payback_meses',         'tipo' => 'decimal', 'casas' => 1],
            ['campo' => 'padrao_ligacao',        'titulo' => 'padrao_ligacao',        'tipo' => 'texto'],
            ['campo' => 'padrao_estimado',       'titulo' => 'padrao_estimado',       'tipo' => 'inteiro'],
            ['campo' => 'area_telhado_m2',       'titulo' => 'area_telhado_m2',       'tipo' => 'decimal'],
            ['campo' => 'direcao_telhado',       'titulo' => 'direcao_telhado',       'tipo' => 'texto'],
            ['campo' => 'area_estimada',         'titulo' => 'area_estimada',         'tipo' => 'inteiro'],
            ['campo' => 'direcao_estimada',      'titulo' => 'direcao_estimada',      'tipo' => 'inteiro'],
            ['campo' => 'comporta_placas',       'titulo' => 'comporta_placas',       'tipo' => 'inteiro'],
            ['campo' => 'qtd_itens',             'titulo' => 'qtd_itens',             'tipo' => 'inteiro'],
            ['campo' => 'qtd_placas',            'titulo' => 'qtd_placas',            'tipo' => 'inteiro'],
            ['campo' => 'valor_total_itens',     'titulo' => 'valor_total_itens',     'tipo' => 'decimal'],
        ],
        'sql'    => $sql,
        'params' => $params,
    ];
}

function relatorioClientes(array $filtros = []): array
{
    $where  = ["p.nome_perfil = 'Cliente'", 'u.is_system = 0'];
    $params = [];

    $busca = trim((string) ($filtros['q'] ?? ''));
    if ($busca !== '') {
        $like = '%' . addcslashes($busca, '%_\\') . '%';
        $where[] = '(u.nome_usuario LIKE :q1 OR u.login LIKE :q2 OR u.email_usuario LIKE :q3)';
        $params += ['q1' => $like, 'q2' => $like, 'q3' => $like];
    }

    $sql = "SELECT u.id_usuario AS id_cliente,
                   u.nome_usuario AS nome,
                   u.login AS email_login,
                   u.email_usuario AS email_contato,
                   CASE WHEN u.cnpj_usuario IS NOT NULL AND u.cnpj_usuario <> '' THEN 'PJ'
                        WHEN u.cpf_usuario  IS NOT NULL AND u.cpf_usuario  <> '' THEN 'PF'
                        ELSE '' END AS tipo_pessoa,
                   u.ativo,
                   DATE(u.criado_em) AS data_cadastro,
                   es.sigla_estado AS estado,
                   ci.nome_cidade  AS cidade,
                   b.nome_bairro   AS bairro,
                   (SELECT COUNT(*) FROM Orcamento o
                     WHERE o.id_usuario_cliente = u.id_usuario) AS qtd_orcamentos,
                   (SELECT MAX(o.data_emissao) FROM Orcamento o
                     WHERE o.id_usuario_cliente = u.id_usuario) AS data_ultimo_orcamento,
                   (SELECT COALESCE(SUM(o.investimento), 0) FROM Orcamento o
                     WHERE o.id_usuario_cliente = u.id_usuario) AS investimento_total_orcado
            FROM Usuario u
            JOIN Perfil p ON p.id_perfil = u.id_perfil
            LEFT JOIN usuario_endereco ue ON ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial'
            LEFT JOIN Endereco en ON en.id_endereco = ue.id_endereco
            LEFT JOIN Bairro b    ON b.id_bairro    = en.id_bairro
            LEFT JOIN Cidade ci   ON ci.id_cidade   = b.id_cidade
            LEFT JOIN Estado es   ON es.id_estado   = ci.id_estado
            WHERE " . implode(' AND ', $where) . '
            ORDER BY u.id_usuario';

    return [
        'arquivo'   => 'clientes',
        'permissao' => 'consultar_clientes',
        'colunas'   => [
            ['campo' => 'id_cliente',                'titulo' => 'id_cliente',                'tipo' => 'inteiro'],
            ['campo' => 'nome',                      'titulo' => 'nome',                      'tipo' => 'texto'],
            ['campo' => 'email_login',               'titulo' => 'email_login',               'tipo' => 'texto'],
            ['campo' => 'email_contato',             'titulo' => 'email_contato',             'tipo' => 'texto'],
            ['campo' => 'tipo_pessoa',               'titulo' => 'tipo_pessoa',               'tipo' => 'texto'],
            ['campo' => 'ativo',                     'titulo' => 'ativo',                     'tipo' => 'inteiro'],
            ['campo' => 'data_cadastro',             'titulo' => 'data_cadastro',             'tipo' => 'data'],
            ['campo' => 'estado',                    'titulo' => 'estado',                    'tipo' => 'texto'],
            ['campo' => 'cidade',                    'titulo' => 'cidade',                    'tipo' => 'texto'],
            ['campo' => 'bairro',                    'titulo' => 'bairro',                    'tipo' => 'texto'],
            ['campo' => 'qtd_orcamentos',            'titulo' => 'qtd_orcamentos',            'tipo' => 'inteiro'],
            ['campo' => 'data_ultimo_orcamento',     'titulo' => 'data_ultimo_orcamento',     'tipo' => 'data'],
            ['campo' => 'investimento_total_orcado', 'titulo' => 'investimento_total_orcado', 'tipo' => 'decimal'],
        ],
        'sql'    => $sql,
        'params' => $params,
    ];
}

/** Limite de unidades para um produto ser considerado "estoque baixo" (o mesmo de placas.php). */
const ESTOQUE_BAIXO_LIMITE = 10;

function relatorioEstoque(array $filtros = []): array
{
    $sql = "SELECT p.id_produto,
                   p.nome_produto AS produto,
                   p.marca_produto AS marca,
                   p.fornecedor,
                   p.tam_produto AS potencia_wp,
                   p.voltagem_produto AS voltagem_v,
                   p.area_m2,
                   p.valorUn_produto AS valor_unitario,
                   COALESCE(e.quantidade, 0) AS quantidade_estoque,
                   COALESCE(e.quantidade, 0) * COALESCE(p.valorUn_produto, 0) AS valor_em_estoque,
                   CASE WHEN COALESCE(e.quantidade, 0) <= " . ESTOQUE_BAIXO_LIMITE . " THEN 1 ELSE 0 END AS estoque_baixo,
                   (SELECT MAX(m.data_movimentacao) FROM MovimentacaoEstoque m
                     WHERE m.id_estoque = e.id_estoque) AS data_ultima_movimentacao,
                   (SELECT COALESCE(SUM(m.quantidade), 0) FROM MovimentacaoEstoque m
                     WHERE m.id_estoque = e.id_estoque AND m.tipo = 'entrada') AS total_entradas,
                   (SELECT COALESCE(SUM(m.quantidade), 0) FROM MovimentacaoEstoque m
                     WHERE m.id_estoque = e.id_estoque AND m.tipo = 'saida') AS total_saidas
            FROM Produto p
            LEFT JOIN Estoque e ON e.id_produto = p.id_produto
            ORDER BY p.id_produto";

    return [
        'arquivo'   => 'estoque',
        'permissao' => 'consultar_estoque',
        'colunas'   => [
            ['campo' => 'id_produto',               'titulo' => 'id_produto',               'tipo' => 'inteiro'],
            ['campo' => 'produto',                  'titulo' => 'produto',                  'tipo' => 'texto'],
            ['campo' => 'marca',                    'titulo' => 'marca',                    'tipo' => 'texto'],
            ['campo' => 'fornecedor',               'titulo' => 'fornecedor',               'tipo' => 'texto'],
            ['campo' => 'potencia_wp',              'titulo' => 'potencia_wp',              'tipo' => 'inteiro'],
            ['campo' => 'voltagem_v',               'titulo' => 'voltagem_v',               'tipo' => 'inteiro'],
            ['campo' => 'area_m2',                  'titulo' => 'area_m2',                  'tipo' => 'decimal'],
            ['campo' => 'valor_unitario',           'titulo' => 'valor_unitario',           'tipo' => 'decimal'],
            ['campo' => 'quantidade_estoque',       'titulo' => 'quantidade_estoque',       'tipo' => 'inteiro'],
            ['campo' => 'valor_em_estoque',         'titulo' => 'valor_em_estoque',         'tipo' => 'decimal'],
            ['campo' => 'estoque_baixo',            'titulo' => 'estoque_baixo',            'tipo' => 'inteiro'],
            ['campo' => 'data_ultima_movimentacao', 'titulo' => 'data_ultima_movimentacao', 'tipo' => 'data'],
            ['campo' => 'total_entradas',           'titulo' => 'total_entradas',           'tipo' => 'inteiro'],
            ['campo' => 'total_saidas',             'titulo' => 'total_saidas',             'tipo' => 'inteiro'],
        ],
        'sql'    => $sql,
        'params' => [],
    ];
}
