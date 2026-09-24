<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/RoofDirection.php';

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

// --- Guarda o resultado para a página de relatório exibir ---
$_SESSION['orcamento_resultado'] = [
    'bill_amount'          => $billAmount,
    'roof_direction'       => $directionForCalc->value,
    'direction_efficiency' => $efficiency,
    'knows_roof_size'      => $knowsRoofSize,
    'roof_size_m2'         => $roofSizeM2,
    'kwp_instalado'        => round($kwpInstalado, 2),
    'panel_count'          => $panelCount,
    'geracao_mensal_kwh'   => round($geracaoMensalKwh, 1),
    'cobertura_percent'    => round($coberturaPercent, 1),
    'economia_mensal'      => round($economiaMensal, 2),
    'bill_after'           => round(max(0, $billAmount - $economiaMensal), 2),
    'investimento'         => round($investimento, 2),
    'payback_meses'        => $paybackMeses !== null ? round($paybackMeses, 1) : null,
    'roof_fits'            => $roofFits,
    'issued_at'            => date('d/m/Y'),
];

header('Location: relatorio.php');
exit;