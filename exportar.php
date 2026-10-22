<?php
/**
 * Baixa um relatório em planilha (CSV):
 *
 *   exportar.php?tipo=orcamentos[&cliente=N][&vendedor=N][&data_inicial=AAAA-MM-DD][&data_final=AAAA-MM-DD]
 *   exportar.php?tipo=clientes[&q=texto]
 *   exportar.php?tipo=estoque
 *
 * As colunas e o SQL de cada relatório ficam em includes/relatorios.php.
 * Somente leitura (GET); exige login e a permissão de consulta da área
 * correspondente (a mesma exigida para abrir a tela).
 *
 * FORMATO DO ARQUIVO (abre direto no Excel em português do Brasil)
 *   - UTF-8 com BOM (acentos corretos no Excel);
 *   - separador ponto e vírgula (;);
 *   - decimais com vírgula e sem separador de milhar (1234,56);
 *   - datas em AAAA-MM-DD; indicadores 0/1;
 *   - textos que começam com = + - @ recebem um apóstrofo, para não serem
 *     interpretados como fórmula pelo Excel (proteção contra "CSV injection").
 *
 * Ao importar em ferramentas de dashboard, configure: separador ";", decimal ",".
 */
require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth-helpers.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/relatorios.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

const CSV_SEPARADOR = ';';

$tipo        = (string) ($_GET['tipo'] ?? '');
$disponiveis = relatoriosDisponiveis();

if (!isset($disponiveis[$tipo])) {
    http_response_code(400);
    exit('Relatório inválido. Use: ' . implode(', ', array_keys($disponiveis)) . '.');
}

$relatorio = $disponiveis[$tipo]($_GET);

if (!userCan($relatorio['permissao'])) {
    http_response_code(403);
    exit('Você não tem permissão para exportar este relatório.');
}

/** Converte um valor do banco para o texto da célula, conforme o tipo da coluna. */
function formatarCelula(mixed $valor, array $coluna): string
{
    if ($valor === null || $valor === '') {
        return '';
    }

    switch ($coluna['tipo']) {
        case 'inteiro':
            return (string) (int) $valor;
        case 'decimal':
            return number_format((float) $valor, $coluna['casas'] ?? 2, ',', '');
        case 'data':
            return (string) $valor;
        default: // texto
            $texto = (string) $valor;
            return preg_match('/^[=+\-@\t\r]/', $texto) ? "'" . $texto : $texto;
    }
}

$consulta = getDb()->prepare($relatorio['sql']);
$consulta->execute($relatorio['params']);

$nomeArquivo = $relatorio['arquivo'] . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
header('Cache-Control: private, no-store');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF"); // BOM UTF-8

fputcsv($saida, array_column($relatorio['colunas'], 'titulo'), CSV_SEPARADOR, '"', '');

while ($linha = $consulta->fetch()) {
    $celulas = [];
    foreach ($relatorio['colunas'] as $coluna) {
        $celulas[] = formatarCelula($linha[$coluna['campo']] ?? null, $coluna);
    }
    fputcsv($saida, $celulas, CSV_SEPARADOR, '"', '');
}

fclose($saida);
exit;
