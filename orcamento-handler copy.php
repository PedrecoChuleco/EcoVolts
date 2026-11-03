<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/RoofDirection.php';
require __DIR__ . '/includes/PadraoLigacao.php';
require __DIR__ . '/includes/StatusOrcamento.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/business-lookups.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orcamento.php');
    exit;
}

require_once __DIR__ . '/includes/auth-helpers.php';
$db = getDb();

$idCliente   = (int) $auth['user']['id'];   // fluxo normal: o próprio usuário
$idVendedor  = null;                        // null = "Vendedor Online"
$clienteAlvo = (int) ($_POST['cliente_id'] ?? 0);

if ($clienteAlvo > 0) {
    if (!userCan('consultar_clientes') || !userCan('simular_orcamento') || !getClienteParaOrcamento($db, $clienteAlvo)) {
        http_response_code(403);
        exit('Acesso negado.');
    }
    $idCliente  = $clienteAlvo;
    $idVendedor = (int) $auth['user']['id'];  // quem criou fica como vendedor responsável
}

// Para onde voltar em caso de erro (mantém o cliente escolhido pela equipe comercial).
$voltar = 'orcamento.php' . ($clienteAlvo ? '?cliente=' . $clienteAlvo : '');

/*
 * =====================================================================
 *  MODELO DE CÁLCULO
 * =====================================================================
 *
 *  VARIÁVEIS
 *  ---------------------------------------------------------------------
 *  Símbolo  Significado                          Variável no código / origem
 *  Ci       Valor inicial da conta (R$)          $valorConta  - formulário (valor_conta)
 *  Cf       Valor final da conta com solar (R$)  $contaFinal  - calculado
 *  Cmin     Valor mínimo da conta (R$)           $custoMinimo - PadraoLigacao::custoMinimo()
 *                                                  Monofásico 23,68 | Bifásico 39,46 | Trifásico 78,93
 *  T        Tarifa de energia (R$/kWh)           TARIFA_KWH
 *  P        Potência de cada placa (Wp)          $potenciaWp  - Produto.tam_produto (padrão: PAINEL_WP_PADRAO)
 *  Ap       Área ocupada por cada placa (m²)     $areaPlacaM2 - Produto.area_m2     (padrão: PAINEL_AREA_M2_PADRAO)
 *  At       Área disponível do telhado (m²)      $tamanhoTelhadoM2 - formulário (tamanho_telhado_m2), opcional
 *  H        Horas de sol pleno por dia (h/dia)   HORAS_SOL_PLENO
 *  D        Dias do mês                          DIAS_MES
 *  L        Perdas do sistema (fração)           PERDAS_SISTEMA
 *  ηs       Fator de desempenho = 1 - L          $etaS - calculado
 *  ηo       Fator de orientação                  $etaO - RoofDirection::efficiency()
 *                                                  Norte = 1,00 | Leste/Oeste = 0,85 | Sul = 0,70
 *  N        Quantidade de placas instaladas      $qtdPlacas - calculado
 *  K        Consumo mensal estimado (kWh)        $consumoMensalKwh - calculado
 *  Kc       Consumo compensável pela solar (kWh) $consumoCompensavelKwh - calculado
 *  Gm       Geração solar mensal (kWh/mês)       $geracaoMensalKwh - calculado
 *  S        Economia mensal (R$)                 $economiaMensal - calculado
 *  Pinv     Capacidade de 1 inversor (W)         $capacidadeInversorW - Produto.tam_produto do inversor
 *                                                  (padrão: INVERSOR_CAPACIDADE_W_PADRAO)
 *  Mp       Metros de cabo por placa (m)         CABO_METROS_POR_PLACA
 *
 *  FÓRMULAS
 *  ---------------------------------------------------------------------
 *  1) Consumo mensal ............ K  = Ci / T
 *  2) Geração de 1 placa ........ g1 = (P / 1000) * H * D * ηo * ηs
 *  3) Consumo compensável ....... Kc = max(0, K - Cmin / T)
 *       (a parte equivalente ao valor mínimo da conta é paga de qualquer forma)
 *  4) Placas que cabem no telhado  Nmax = floor(At / Ap)        (só se At for conhecida)
 *  5) Placas para cobrir Kc ..... Nnec = ceil(Kc / g1)
 *  6) Placas instaladas ......... N  = min(Nnec, Nmax)          (sem At: N = Nnec)
 *  7) Geração mensal ............ Gm = N * g1
 *  8) Energia não compensada .... Krestante = max(0, K - Gm)
 *  9) Conta final estimada ...... Cf = max(Cmin, Krestante * T)
 * 10) Economia mensal ........... S  = Ci - Cf
 *
 *  ITENS DO ORÇAMENTO (cada um vira uma linha em Orcamento_Prod)
 *  ---------------------------------------------------------------------
 *  Item                Quantidade                         Valor unitário (Produto)
 *  Placas solares      N                                  PRODUTO_PAINEL
 *  Inversor            ceil(N * P / Pinv)  (mín. 1)       PRODUTO_INVERSOR
 *  Cabos de instalação N * Mp metros (arredondado p/ cima) PRODUTO_CABO (por metro)
 *  Mão de obra         N  (cobrada por placa instalada)   PRODUTO_MAO_DE_OBRA
 *
 * 11) Investimento .............. soma de (quantidade * valor unitário) de todos os itens
 * 12) Payback (MESES) ........... Investimento / S
 *
 *  Os preços ficam no cadastro de Produto (tela Placas / Estoque), não no código.
 *  Serviços (mão de obra) e consumíveis não precisam de linha em Estoque.
 *
 *  PADRÃO DE LIGAÇÃO (define Cmin)
 *  ---------------------------------------------------------------------
 *  - Se o cliente não souber, assume-se Bifásico (R$ 39,46 / 50 kWh), o orçamento
 *    é gravado com padrao_estimado = 1 e o relatório exibe um aviso.
 *  - Se Ci <= Cmin a simulação é recusada: a conta já está no mínimo e a energia
 *    solar não geraria economia.
 *
 *  STATUS
 *  ---------------------------------------------------------------------
 *  Todo orçamento nasce com status "aberto" (StatusOrcamento::PADRAO).
 *
 *  OBSERVAÇÕES
 *  ---------------------------------------------------------------------
 *  - Gm usa P em Wp (potência instantânea) convertida para kW e multiplicada
 *    pelo tempo (H * D) para virar energia (kWh).
 *  - K = Ci / T só é exato se T for o custo efetivo por kWh da conta inteira;
 *    taxas fixas (iluminação pública, impostos) não são separadas.
 *  - Cf (passo 9) é uma aproximação: não considera outros custos fixos nem
 *    regras específicas de compensação/faturamento.
 *  - Se At for desconhecida, assume-se que o telhado comporta (o vendedor
 *    confirma na visita técnica) e N = Nnec.
 *  - Se a direção for desconhecida, usa-se o cenário conservador (Sul, 0,70).
 *  - O telhado "comporta" quando Nmax >= Nnec.
 *  - Premissas de geração (H, D, L) dependem da localização, inclinação e
 *    sombras reais do imóvel.
 *
 *  CAMPOS DO FORMULÁRIO (orcamento.php)
 *  ---------------------------------------------------------------------
 *  valor_conta, sabe_tamanho_telhado, tamanho_telhado_m2,
 *  sabe_direcao, direcao_telhado, sabe_padrao, padrao_ligacao,
 *  cliente_id (somente equipe comercial)
 */
const TARIFA_KWH                   = 0.85;   // T    - R$ por kWh
const HORAS_SOL_PLENO              = 5.0;    // H    - h/dia
const DIAS_MES                     = 30;     // D    - dias
const PERDAS_SISTEMA               = 0.20;   // L    - 20% => ηs = 0,80
const PAINEL_WP_PADRAO             = 550.0;  // P    - usado se Produto.tam_produto da placa estiver vazio
const PAINEL_AREA_M2_PADRAO        = 2.5;    // Ap   - usado se Produto.area_m2 da placa estiver vazio
const INVERSOR_CAPACIDADE_W_PADRAO = 5000.0; // Pinv - usado se Produto.tam_produto do inversor estiver vazio
const CABO_METROS_POR_PLACA        = 10.0;   // Mp   - metros de cabo por placa instalada

$erros  = [];
$antigo = $_POST;

// --- Padrão de ligação (define o valor mínimo da conta) ---
$padrao      = PadraoLigacao::tryFrom($_POST['padrao_ligacao'] ?? '');
$sabePadrao  = ($_POST['sabe_padrao'] ?? '1') === '1';

if ($sabePadrao && !$padrao) {
    $erros['padrao_ligacao'] = 'Selecione um padrão de ligação válido.';
}
// Se o cliente não sabe o padrão, assume Bifásico e marca o orçamento como estimado.
$padraoCalculo = ($sabePadrao && $padrao) ? $padrao : PadraoLigacao::PADRAO_QUANDO_NAO_SABE;
$custoMinimo   = $padraoCalculo->custoMinimo();

// --- Conta de energia (aceita "380,00" ou "380.00") ---
$contaTexto = str_replace(['.', ','], ['', '.'], trim($_POST['valor_conta'] ?? ''));
// Se veio só "380,00" -> "380.00" acima também converteria pontos de milhar incorretamente
// em valores grandes; para valores de conta residencial (sem milhar) isso é seguro.
$valorConta = is_numeric($contaTexto) ? (float) $contaTexto : null;

if ($valorConta === null || $valorConta <= 0) {
    $erros['valor_conta'] = 'Informe um valor de conta válido.';
} elseif ($valorConta <= $custoMinimo) {
    $erros['valor_conta'] = 'A conta informada é igual ou menor que o valor mínimo ('
        . 'R$ ' . number_format($custoMinimo, 2, ',', '.')
        . ') do padrão ' . mb_strtolower($padraoCalculo->label())
        . '. Nesse caso a energia solar não gera economia.';
}

// --- Direção do telhado ---
$direcao      = RoofDirection::tryFrom($_POST['direcao_telhado'] ?? '');
$sabeDirecao  = ($_POST['sabe_direcao'] ?? '1') === '1';

if ($sabeDirecao && !$direcao) {
    $erros['direcao_telhado'] = 'Selecione uma direção válida.';
}
// Se o usuário não sabe a direção, assumimos o cenário conservador (pior eficiência).
$direcaoCalculo = $direcao ?? RoofDirection::Sul;

// --- Tamanho do telhado ---
$sabeTamanho      = ($_POST['sabe_tamanho_telhado'] ?? '1') === '1';
$tamanhoTelhadoM2 = null;

if ($sabeTamanho) {
    $tamanhoTexto     = trim($_POST['tamanho_telhado_m2'] ?? '');
    $tamanhoTelhadoM2 = is_numeric($tamanhoTexto) ? (float) $tamanhoTexto : null;

    if ($tamanhoTelhadoM2 === null || $tamanhoTelhadoM2 <= 0) {
        $erros['tamanho_telhado_m2'] = 'Informe um tamanho de telhado válido.';
    }
}

if (!empty($erros)) {
    $_SESSION['erros']  = $erros;
    $_SESSION['antigo'] = $antigo;
    header('Location: ' . $voltar);
    exit;
}

// --- Produtos de referência (preços e especificações vêm do cadastro de Produto) ---
try {
    $painel    = getProdutoPorNome($db, PRODUTO_PAINEL);
    $inversor  = getProdutoPorNome($db, PRODUTO_INVERSOR);
    $cabo      = getProdutoPorNome($db, PRODUTO_CABO);
    $maoDeObra = getProdutoPorNome($db, PRODUTO_MAO_DE_OBRA);
} catch (RuntimeException $excecao) {
    error_log('Orçamento: ' . $excecao->getMessage());
    $_SESSION['erros']  = ['valor_conta' => 'Cadastro de produtos incompleto para gerar o orçamento. Avise a equipe EcoVolts.'];
    $_SESSION['antigo'] = $antigo;
    header('Location: ' . $voltar);
    exit;
}

$potenciaWp = (float) ($painel['tam_produto'] ?? 0);
if ($potenciaWp <= 0) {
    $potenciaWp = PAINEL_WP_PADRAO;
}

$areaPlacaM2 = (float) ($painel['area_m2'] ?? 0);
if ($areaPlacaM2 <= 0) {
    $areaPlacaM2 = PAINEL_AREA_M2_PADRAO;
}

$capacidadeInversorW = (float) ($inversor['tam_produto'] ?? 0);
if ($capacidadeInversorW <= 0) {
    $capacidadeInversorW = INVERSOR_CAPACIDADE_W_PADRAO;
}

// --- Cálculo (ver fórmulas no cabeçalho) ---
$etaO = $direcaoCalculo->efficiency();   // ηo
$etaS = 1 - PERDAS_SISTEMA;              // ηs

// 1) Consumo mensal estimado (kWh)
$consumoMensalKwh = $valorConta / TARIFA_KWH;

// 2) Geração mensal de uma placa (kWh/mês)
$geracaoPorPlacaKwh = ($potenciaWp / 1000) * HORAS_SOL_PLENO * DIAS_MES * $etaO * $etaS;

// 3) Consumo que a energia solar consegue compensar (o mínimo da conta é pago de qualquer forma)
$consumoCompensavelKwh = max(0, $consumoMensalKwh - $custoMinimo / TARIFA_KWH);

// 4) e 5) Placas que cabem no telhado e placas necessárias
$placasMax = $tamanhoTelhadoM2 !== null ? (int) floor($tamanhoTelhadoM2 / $areaPlacaM2) : null;
$placasNec = (int) ceil($consumoCompensavelKwh / $geracaoPorPlacaKwh);

// 6) Placas instaladas
$qtdPlacas = $placasMax !== null ? min($placasNec, $placasMax) : $placasNec;

// Sem o tamanho do telhado não há como dizer que ele "não comporta":
// assumimos que comporta e o vendedor confirma na visita técnica.
$telhadoComporta = $placasMax === null ? true : ($placasMax >= $placasNec);

// 7) Geração mensal do sistema (kWh/mês)
$geracaoMensalKwh = $qtdPlacas * $geracaoPorPlacaKwh;

// 8) e 9) Energia não compensada e conta final estimada (nunca abaixo do mínimo)
$consumoRestanteKwh = max(0, $consumoMensalKwh - $geracaoMensalKwh);
$contaFinal         = round(max($custoMinimo, $consumoRestanteKwh * TARIFA_KWH), 2);

// 10) Economia mensal (R$)
$economiaMensal = $valorConta - $contaFinal;

// --- Itens do orçamento: placas, inversor, cabos de instalação e mão de obra ---
$qtdInversores = $qtdPlacas > 0 ? max(1, (int) ceil(($qtdPlacas * $potenciaWp) / $capacidadeInversorW)) : 0;
$metrosCabo    = (int) ceil($qtdPlacas * CABO_METROS_POR_PLACA);
$qtdMaoDeObra  = $qtdPlacas;

$itensOrcamento = [
    ['produto' => $painel,    'qtd' => $qtdPlacas],
    ['produto' => $inversor,  'qtd' => $qtdInversores],
    ['produto' => $cabo,      'qtd' => $metrosCabo],
    ['produto' => $maoDeObra, 'qtd' => $qtdMaoDeObra],
];

// 11) Investimento = soma de todos os itens; 12) payback em meses
$investimento = 0.0;
foreach ($itensOrcamento as $item) {
    $investimento += $item['qtd'] * (float) $item['produto']['valorUn_produto'];
}
$paybackMeses = $economiaMensal > 0 ? $investimento / $economiaMensal : null;

// --- Persiste o orçamento (Telhado + Orcamento + Orcamento_Prod) ---
try {
    $db->beginTransaction();

    $db->prepare(
        'INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
         VALUES (:area, :direcao, :area_estimada, :direcao_estimada, :comporta_placas)'
    )->execute([
        'area'             => $tamanhoTelhadoM2,
        'direcao'          => $direcaoCalculo->value,
        'area_estimada'    => $sabeTamanho ? 0 : 1,
        'direcao_estimada' => $sabeDirecao ? 0 : 1,
        'comporta_placas'  => $telhadoComporta ? 1 : 0,
    ]);
    $idTelhado = (int) $db->lastInsertId();

    $db->prepare(
        'INSERT INTO Orcamento
            (num_orcamento, payback, valor_totalCE, valor_totalCEPI, data_emissao,
             investimento, padrao_ligacao, padrao_estimado, status,
             id_telhado, id_usuario_cliente, id_usuario_vendedor)
         VALUES
            ("", :payback, :valor_totalCE, :valor_totalCEPI, CURDATE(),
             :investimento, :padrao_ligacao, :padrao_estimado, :status,
             :id_telhado, :id_cliente, :id_vendedor)'
    )->execute([
        'payback'         => $paybackMeses !== null ? round($paybackMeses, 1) : null,
        'valor_totalCE'   => round($valorConta, 2),
        'valor_totalCEPI' => $contaFinal,
        'investimento'    => round($investimento, 2),
        'padrao_ligacao'  => $padraoCalculo->value,
        'padrao_estimado' => $sabePadrao ? 0 : 1,
        'status'          => StatusOrcamento::PADRAO->value,
        'id_telhado'      => $idTelhado,
        'id_cliente'      => $idCliente,
        'id_vendedor'     => $idVendedor ?? getOnlineVendorId($db),
    ]);
    $idOrcamento = (int) $db->lastInsertId();

    // Código legível do orçamento, derivado do id (ex.: ORC-000123).
    $numOrcamento = 'ORC-' . str_pad((string) $idOrcamento, 6, '0', STR_PAD_LEFT);
    $db->prepare('UPDATE Orcamento SET num_orcamento = :num WHERE id_orcamento = :id')
        ->execute(['num' => $numOrcamento, 'id' => $idOrcamento]);

    // Itens: telhado menor que uma placa => nenhuma quantidade > 0, nada a registrar.
    $inserirItem = $db->prepare(
        'INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
         VALUES (:id_orcamento, :id_produto, :qtd, :valor_unitario, 0.00)'
    );
    foreach ($itensOrcamento as $item) {
        if ($item['qtd'] > 0) {
            $inserirItem->execute([
                'id_orcamento'   => $idOrcamento,
                'id_produto'     => $item['produto']['id_produto'],
                'qtd'            => $item['qtd'],
                'valor_unitario' => $item['produto']['valorUn_produto'],
            ]);
        }
    }

    $db->commit();
} catch (Throwable $excecao) {
    $db->rollBack();
    error_log('Falha ao salvar orçamento: ' . $excecao->getMessage());
    $_SESSION['erros']  = ['valor_conta' => 'Não foi possível salvar o orçamento. Tente novamente.'];
    $_SESSION['antigo'] = $antigo;
    header('Location: ' . $voltar);
    exit;
}

header('Location: ' . ($clienteAlvo
    ? 'orcamentos.php?cliente=' . $clienteAlvo   // equipe comercial volta para a lista do cliente
    : 'relatorio.php?id=' . $idOrcamento));
exit;
