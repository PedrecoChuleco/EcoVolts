<?php
/**
 * Baixa o orçamento em PDF:  orcamento-pdf.php?id=N
 *
 * Quem pode baixar:
 *   - o próprio cliente dono do orçamento;
 *   - a equipe com a permissão `consultar_orcamentos` (vendedor/administrador).
 * Para os demais, responde 404 (não revela que o orçamento existe).
 */
require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth-helpers.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/PadraoLigacao.php';
require __DIR__ . '/includes/PdfSimples.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

function naoEncontrado(): never
{
    http_response_code(404);
    exit('Orçamento não encontrado.');
}

function brl(?float $valor): string
{
    return $valor === null ? '—' : 'R$ ' . number_format($valor, 2, ',', '.');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    naoEncontrado();
}

$db = getDb();

$consulta = $db->prepare(
    'SELECT o.*,
            c.nome_usuario AS cliente_nome, c.login AS cliente_login,
            c.email_usuario AS cliente_email, c.cpf_usuario, c.cnpj_usuario,
            v.nome_usuario AS vendedor_nome,
            t.area_telhado, t.direcao_telhado, t.area_estimada, t.direcao_estimada, t.comporta_placas
     FROM Orcamento o
     JOIN Usuario c ON c.id_usuario = o.id_usuario_cliente
     JOIN Usuario v ON v.id_usuario = o.id_usuario_vendedor
     LEFT JOIN Telhado t ON t.id_telhado = o.id_telhado
     WHERE o.id_orcamento = :id
     LIMIT 1'
);
$consulta->execute(['id' => $id]);
$orcamento = $consulta->fetch();

$ehDono = $orcamento && (int) $orcamento['id_usuario_cliente'] === (int) $auth['user']['id'];
if (!$orcamento || (!$ehDono && !userCan('consultar_orcamentos'))) {
    naoEncontrado();
}

$consultaItens = $db->prepare(
    'SELECT op.qtd, op.valor_unitario, op.desconto, p.nome_produto, p.marca_produto
     FROM Orcamento_Prod op
     JOIN Produto p ON p.id_produto = op.id_produto
     WHERE op.id_orcamento = :id
     ORDER BY p.nome_produto'
);
$consultaItens->execute(['id' => $id]);
$itens = $consultaItens->fetchAll();

// ------------------------------------------------------------------ valores
$valorContaAtual = $orcamento['valor_totalCE'] !== null ? (float) $orcamento['valor_totalCE'] : null;
$valorContaFinal = $orcamento['valor_totalCEPI'] !== null ? (float) $orcamento['valor_totalCEPI'] : null;
$economiaMensal  = ($valorContaAtual !== null && $valorContaFinal !== null) ? $valorContaAtual - $valorContaFinal : null;
$padrao          = PadraoLigacao::tryFrom((string) ($orcamento['padrao_ligacao'] ?? ''));
$padraoEstimado  = (int) ($orcamento['padrao_estimado'] ?? 0) === 1;
$documento       = $orcamento['cpf_usuario'] ?: ($orcamento['cnpj_usuario'] ?: '');
$dataEmissao     = $orcamento['data_emissao'] ? date('d/m/Y', strtotime($orcamento['data_emissao'])) : '—';

// ---------------------------------------------------------------- desenho
const COR_ESCURO = [33, 33, 33];
const COR_VERDE  = [64, 78, 68];
const COR_AMBAR  = [255, 218, 33];
const COR_MUDO   = [110, 120, 114];
const MARGEM     = 40.0;

$pdf = new PdfSimples();
$y   = 0.0;

// Cabeçalho
$pdf->retangulo(0, 0, PdfSimples::LARGURA, 70, COR_ESCURO);
$pdf->retangulo(0, 70, PdfSimples::LARGURA, 4, COR_AMBAR);
$pdf->texto(MARGEM, 45, 'Eco', 22, true, [255, 255, 255]);
$pdf->texto(MARGEM + $pdf->larguraTexto('Eco', 22, true), 45, 'Volts', 22, true, COR_AMBAR);
$pdf->textoDireita(PdfSimples::LARGURA - MARGEM, 36, 'Orçamento ' . $orcamento['num_orcamento'], 14, true, [255, 255, 255]);
$pdf->textoDireita(PdfSimples::LARGURA - MARGEM, 52, 'Emitido em ' . $dataEmissao, 9, false, [200, 210, 204]);
$y = 105.0;

/** Começa uma nova página se faltar espaço. */
$garantirEspaco = function (float $altura) use ($pdf, &$y): void {
    if ($y + $altura > 790) {
        $pdf->novaPagina();
        $y = 50.0;
    }
};

$secao = function (string $titulo) use ($pdf, &$y, $garantirEspaco): void {
    $garantirEspaco(60);
    $pdf->texto(MARGEM, $y, $titulo, 12, true, COR_VERDE);
    $pdf->linha(MARGEM, $y + 6, PdfSimples::LARGURA - MARGEM, $y + 6, COR_VERDE, 0.8);
    $y += 26;
};

/** Um par rótulo/valor; $coluna 0 = esquerda, 1 = direita. */
$campo = function (int $coluna, string $rotulo, string $valor) use ($pdf, &$y): void {
    $x = $coluna === 0 ? MARGEM : 310.0;
    $pdf->texto($x, $y, $rotulo, 8.5, false, COR_MUDO);
    $pdf->texto($x, $y + 14, $pdf->ajustar($valor, 235, 10.5, true), 10.5, true, COR_ESCURO);
};

// Cliente
$secao('Cliente');
$campo(0, 'Nome', $orcamento['cliente_nome']);
$campo(1, 'E-mail', $orcamento['cliente_email'] ?: $orcamento['cliente_login']);
$y += 38;
$campo(0, 'Documento', $documento !== '' ? $documento : '—');
$campo(1, 'Vendedor responsável', $orcamento['vendedor_nome']);
$y += 44;

// Resumo financeiro
$secao('Resumo financeiro');
$campo(0, 'Conta de energia atual', brl($valorContaAtual));
$campo(1, 'Conta após a instalação (estimada)', brl($valorContaFinal));
$y += 38;
$campo(0, 'Economia mensal estimada', brl($economiaMensal));
$campo(1, 'Investimento', brl($orcamento['investimento'] !== null ? (float) $orcamento['investimento'] : null));
$y += 38;
$campo(0, 'Payback', $orcamento['payback'] !== null ? number_format((float) $orcamento['payback'], 1, ',', '.') . ' meses' : '—');
if ($padrao) {
    $campo(1, 'Padrão de ligação' . ($padraoEstimado ? ' (estimado)' : ''), $padrao->label() . ' - mínimo ' . brl($padrao->custoMinimo()));
}
$y += 44;

// Telhado
if ($orcamento['id_telhado']) {
    $secao('Telhado');
    $campo(0, 'Área', $orcamento['area_telhado'] !== null
        ? number_format((float) $orcamento['area_telhado'], 2, ',', '.') . ' m²'
        : 'Não informada (estimativa)');
    $campo(1, 'Direção', ($orcamento['direcao_telhado'] ?: '—') . ($orcamento['direcao_estimada'] ? ' (estimada)' : ''));
    $y += 38;
    $campo(0, 'O telhado comporta as placas?', (int) $orcamento['comporta_placas'] === 1 ? 'Sim' : 'Pode não comportar');
    $y += 44;
}

// Itens
$secao('Itens do orçamento');

// Colunas da tabela: posição X do canto direito de cada coluna numérica.
const COL_QTD = 340.0;
const COL_UNIT = 425.0;
const COL_DESC = 490.0;
const COL_SUB = 555.0;

$cabecalhoTabela = function () use ($pdf, &$y): void {
    $pdf->retangulo(MARGEM, $y - 12, PdfSimples::LARGURA - 2 * MARGEM, 20, [238, 241, 239]);
    $pdf->texto(MARGEM + 6, $y, 'Produto', 8.5, true, COR_VERDE);
    $pdf->textoDireita(COL_QTD, $y, 'Qtd.', 8.5, true, COR_VERDE);
    $pdf->textoDireita(COL_UNIT, $y, 'Valor unit.', 8.5, true, COR_VERDE);
    $pdf->textoDireita(COL_DESC, $y, 'Desconto', 8.5, true, COR_VERDE);
    $pdf->textoDireita(COL_SUB, $y, 'Subtotal', 8.5, true, COR_VERDE);
    $y += 22;
};
$cabecalhoTabela();

$totalItens = 0.0;
if (empty($itens)) {
    $pdf->texto(MARGEM + 6, $y, 'Nenhum item registrado.', 10, false, COR_MUDO);
    $y += 24;
}
foreach ($itens as $item) {
    if ($y + 34 > 790) {
        $pdf->novaPagina();
        $y = 60.0;
        $cabecalhoTabela();
    }

    $qtd      = (int) $item['qtd'];
    $unitario = $item['valor_unitario'] !== null ? (float) $item['valor_unitario'] : 0.0;
    $desconto = $item['desconto'] !== null ? (float) $item['desconto'] : 0.0;
    $subtotal = $qtd * $unitario - $desconto;
    $totalItens += $subtotal;

    $pdf->texto(MARGEM + 6, $y, $pdf->ajustar($item['nome_produto'], 230, 10, true), 10, true, COR_ESCURO);
    if (!empty($item['marca_produto'])) {
        $pdf->texto(MARGEM + 6, $y + 11, $pdf->ajustar($item['marca_produto'], 230, 8), 8, false, COR_MUDO);
    }
    $pdf->textoDireita(COL_QTD, $y, number_format($qtd, 0, ',', '.'), 10);
    $pdf->textoDireita(COL_UNIT, $y, brl($unitario), 10);
    $pdf->textoDireita(COL_DESC, $y, brl($desconto), 10);
    $pdf->textoDireita(COL_SUB, $y, brl($subtotal), 10, true);
    $pdf->linha(MARGEM, $y + 18, PdfSimples::LARGURA - MARGEM, $y + 18);
    $y += 30;
}

if (!empty($itens)) {
    $pdf->textoDireita(COL_DESC, $y + 2, 'Total dos itens', 10, true, COR_VERDE);
    $pdf->textoDireita(COL_SUB, $y + 2, brl($totalItens), 11, true, COR_ESCURO);
    $y += 34;
}

// Avisos (padrão de ligação assumido, dados estimados do telhado)
$avisos = [];
if ($padrao && $padraoEstimado) {
    $avisos[] = 'Padrão de ligação estimado: o cliente não informou o padrão e foi considerado o padrão '
        . mb_strtolower($padrao->label()) . ', com valor mínimo de conta de ' . brl($padrao->custoMinimo())
        . ' (' . $padrao->consumoMinimoKwh() . ' kWh). A conta após a instalação e o payback podem variar se o padrão for diferente.';
}
if ($orcamento['id_telhado'] && ($orcamento['area_estimada'] || $orcamento['direcao_estimada'])) {
    $avisos[] = 'Dados do telhado estimados: ' . ($orcamento['area_estimada'] ? 'a área não foi informada' : '')
        . ($orcamento['area_estimada'] && $orcamento['direcao_estimada'] ? ' e ' : '')
        . ($orcamento['direcao_estimada'] ? 'a direção não foi informada (considerado o cenário mais conservador)' : '')
        . '. Os valores serão confirmados na visita técnica.';
}

foreach ($avisos as $aviso) {
    $linhas = $pdf->quebrar($aviso, PdfSimples::LARGURA - 2 * MARGEM - 24, 9);
    $altura = count($linhas) * 12.6 + 22;
    $garantirEspaco($altura + 10);
    $pdf->retangulo(MARGEM, $y, PdfSimples::LARGURA - 2 * MARGEM, $altura, [255, 246, 200], [232, 200, 60]);
    $pdf->texto(MARGEM + 12, $y + 15, 'ATENÇÃO', 8, true, [140, 100, 0]);
    $yTexto = $y + 28;
    foreach ($linhas as $linha) {
        $pdf->texto(MARGEM + 12, $yTexto, $linha, 9, false, COR_ESCURO);
        $yTexto += 12.6;
    }
    $y += $altura + 12;
}

// Nota final
$garantirEspaco(40);
$y += 6;
$pdf->paragrafo(
    MARGEM,
    $y,
    PdfSimples::LARGURA - 2 * MARGEM,
    'Os valores são estimativas calculadas a partir das informações fornecidas e podem variar após a visita técnica e o projeto final. '
    . 'EcoVolts Energia Solar - Av. Dr. Maximiliano Baruto, 500, Jardim Universitário, Araras - SP - ecovolts@ecovolts.com.br',
    8,
    false,
    COR_MUDO
);

$pdf->numerarPaginas();

// ------------------------------------------------------------------ saída
$conteudo    = $pdf->saida();
$nomeArquivo = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $orcamento['num_orcamento']) ?: 'orcamento';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '.pdf"');
header('Content-Length: ' . strlen($conteudo));
header('Cache-Control: private, no-store');
echo $conteudo;
exit;
