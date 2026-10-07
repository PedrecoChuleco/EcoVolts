<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/RoofDirection.php';
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

// --- Parâmetros do modelo de cálculo (ajuste conforme sua realidade de negócio) ---
const KWH_PRICE       = 0.85;   // R$ por kWh cobrado na conta
const GERACAO_POR_KWP = 120.0;  // kWh/mês gerados por 1 kWp instalado, a 100% de eficiência (direção Norte)
const AREA_POR_KWP    = 7.0;    // m² de telhado necessários por kWp de painel instalado
const CUSTO_POR_KWP   = 4500.0; // R$ investidos por kWp instalado
const PANEL_WATTAGE   = 550.0;  // W por placa (usado só para estimar a quantidade de placas)

$errors = [];
$old    = $_POST;

// --- Conta de energia (aceita "380,00" ou "380.00") ---
$billRaw = str_replace(['.', ','], ['', '.'], trim($_POST['bill_amount'] ?? ''));
// Se veio só "380,00" -> "380.00" acima também converteria pontos de milhar incorretamente
// em valores grandes; para valores de conta residencial (sem milhar) isso é seguro.
$billAmount = is_numeric($billRaw) ? (float) $billRaw : null;

if ($billAmount === null || $billAmount <= 0) {
    $errors['bill_amount'] = 'Informe um valor de conta válido.';
}

// --- Direção do telhado ---
$direction = RoofDirection::tryFrom($_POST['roof_direction'] ?? '');
$knowsDirection = ($_POST['knows_direction'] ?? '1') === '1';

if ($knowsDirection && !$direction) {
    $errors['roof_direction'] = 'Selecione uma direção válida.';
}
// Se o usuário não sabe a direção, assumimos o cenário conservador (pior eficiência).
$directionForCalc = $direction ?? RoofDirection::Sul;

// --- Tamanho do telhado ---
$knowsRoofSize = ($_POST['knows_roof_size'] ?? '1') === '1';
$roofSizeM2    = null;

if ($knowsRoofSize) {
    $roofSizeRaw = trim($_POST['roof_size_m2'] ?? '');
    $roofSizeM2  = is_numeric($roofSizeRaw) ? (float) $roofSizeRaw : null;

    if ($roofSizeM2 === null || $roofSizeM2 <= 0) {
        $errors['roof_size_m2'] = 'Informe um tamanho de telhado válido.';
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $old;
    header('Location: orcamento.php');
    exit;
}

// --- Cálculo ---
$efficiency = $directionForCalc->efficiency();

$consumoMensalKwh = $billAmount / KWH_PRICE;

// kWp necessário para cobrir todo o consumo, já considerando a perda por direção.
$kwpNecessario = $consumoMensalKwh / (GERACAO_POR_KWP * $efficiency);

// Se soubermos o telhado, ele pode limitar o quanto dá pra instalar.
$kwpMaxTelhado = $roofSizeM2 !== null ? $roofSizeM2 / AREA_POR_KWP : null;
$kwpInstalado  = $kwpMaxTelhado !== null ? min($kwpNecessario, $kwpMaxTelhado) : $kwpNecessario;

$geracaoMensalKwh = $kwpInstalado * GERACAO_POR_KWP * $efficiency;
$economiaMensal   = min($geracaoMensalKwh, $consumoMensalKwh) * KWH_PRICE;
$coberturaPercent = $consumoMensalKwh > 0 ? min(100, ($geracaoMensalKwh / $consumoMensalKwh) * 100) : 0;

$investimento  = $kwpInstalado * CUSTO_POR_KWP;
$paybackMeses  = $economiaMensal > 0 ? $investimento / $economiaMensal : null;

// Quantidade de placas necessárias para o kWp instalado.
$panelCount = (int) ceil(($kwpInstalado * 1000) / PANEL_WATTAGE);

// Se não sabemos o tamanho do telhado, não há como dizer que ele "não comporta" -
// assumimos que comporta e deixamos o vendedor confirmar na visita técnica.
$roofFits = $kwpMaxTelhado === null ? true : ($kwpMaxTelhado >= $kwpNecessario);

$billAfter = round(max(0, $billAmount - $economiaMensal), 2);

// --- Persiste o orçamento (Telhado + Orcamento + Orcamento_Prod) ---
$db = getDb();

try {
    $db->beginTransaction();

    $db->prepare(
        'INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
         VALUES (:area, :direcao, :area_estimada, :direcao_estimada, :comporta_placas)'
    )->execute([
        'area'             => $roofSizeM2,
        'direcao'          => $directionForCalc->value,
        'area_estimada'    => $knowsRoofSize ? 0 : 1,
        'direcao_estimada' => $knowsDirection ? 0 : 1,
        'comporta_placas'  => $roofFits ? 1 : 0,
    ]);
    $idTelhado = (int) $db->lastInsertId();

    $vendedorId = getOnlineVendorId($db);

    $db->prepare(
        'INSERT INTO Orcamento
            (num_orcamento, payback, valor_totalCE, valor_totalCEPI, data_emissao,
             investimento, id_telhado, id_usuario_cliente, id_usuario_vendedor)
         VALUES
            ("", :payback, :valor_totalCE, :valor_totalCEPI, CURDATE(),
             :investimento, :id_telhado, :id_cliente, :id_vendedor)'
    )->execute([
        'payback'         => $paybackMeses !== null ? round($paybackMeses, 1) : null,
        'valor_totalCE'   => round($billAmount, 2),
        'valor_totalCEPI' => $billAfter,
        'investimento'    => round($investimento, 2),
        'id_telhado'      => $idTelhado,
        'id_cliente'      => $auth['user']['id'],
        'id_vendedor'     => $vendedorId,
    ]);
    $idOrcamento = (int) $db->lastInsertId();

    // Código legível do orçamento, derivado do id (ex.: ORC-000123).
    $numOrcamento = 'ORC-' . str_pad((string) $idOrcamento, 6, '0', STR_PAD_LEFT);
    $db->prepare('UPDATE Orcamento SET num_orcamento = :num WHERE id_orcamento = :id')
        ->execute(['num' => $numOrcamento, 'id' => $idOrcamento]);

    $painel = getPainelSolarProduto($db);
    $db->prepare(
        'INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
         VALUES (:id_orcamento, :id_produto, :qtd, :valor_unitario, 0.00)'
    )->execute([
        'id_orcamento'   => $idOrcamento,
        'id_produto'     => $painel['id_produto'],
        'qtd'            => $panelCount,
        'valor_unitario' => $painel['valorUn_produto'],
    ]);

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    error_log('Falha ao salvar orçamento: ' . $e->getMessage());
    $_SESSION['errors'] = ['bill_amount' => 'Não foi possível salvar o orçamento. Tente novamente.'];
    $_SESSION['old']    = $old;
    header('Location: orcamento.php');
    exit;
}

header('Location: relatorio.php?id=' . $idOrcamento);
exit;
