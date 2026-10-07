<?php
/**
 * Atualiza um orçamento existente. Somente administradores.
 */
require __DIR__ . '/includes/require-admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orcamentos.php');
    exit;
}

if (!verifyCsrf()) {
    $_SESSION['flash_error'] = 'Sessão expirada ou requisição inválida. Tente novamente.';
    header('Location: orcamentos.php');
    exit;
}

$db = getDb();
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Orçamento inválido.';
    header('Location: orcamentos.php');
    exit;
}

$stmt = $db->prepare('SELECT id_orcamento FROM Orcamento WHERE id_orcamento = :id LIMIT 1');
$stmt->execute(['id' => $id]);
if (!$stmt->fetchColumn()) {
    $_SESSION['flash_error'] = 'Orçamento não encontrado.';
    header('Location: orcamentos.php');
    exit;
}

$old = $_POST;
$errors = [];

$num = trim($_POST['num_orcamento'] ?? '');
$data = trim($_POST['data_emissao'] ?? '');
$vendedor = (int) ($_POST['id_usuario_vendedor'] ?? 0);

function normalizarDecimal(?string $valor): ?float {
    $valor = trim((string) $valor);
    if ($valor === '') {
        return null;
    }
    // Aceita 1234.56, 1234,56 e 1.234,56.
    $valor = str_replace(['R$', ' '], '', $valor);
    if (str_contains($valor, ',')) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    }
    return is_numeric($valor) ? (float) $valor : null;
}

$investimento = normalizarDecimal($_POST['investimento'] ?? null);
$payback = normalizarDecimal($_POST['payback'] ?? null);
$totalCE = normalizarDecimal($_POST['valor_totalCE'] ?? null);
$totalCEPI = normalizarDecimal($_POST['valor_totalCEPI'] ?? null);

if ($num === '') {
    $errors['num_orcamento'] = 'Informe o número do orçamento.';
} elseif (strlen($num) > 20) {
    $errors['num_orcamento'] = 'O número pode ter no máximo 20 caracteres.';
}

if ($data !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    $errors['data_emissao'] = 'Data inválida.';
}

foreach ([
    'investimento' => $investimento,
    'payback' => $payback,
    'valor_totalCE' => $totalCE,
    'valor_totalCEPI' => $totalCEPI,
] as $campo => $valor) {
    $raw = trim((string) ($_POST[$campo] ?? ''));
    if ($raw !== '' && $valor === null) {
        $errors[$campo] = 'Informe um valor numérico válido.';
    } elseif ($valor !== null && $valor < 0) {
        $errors[$campo] = 'O valor não pode ser negativo.';
    }
}

$stmt = $db->prepare(
    "SELECT 1
     FROM Usuario u
     JOIN Perfil p ON p.id_perfil = u.id_perfil
     WHERE u.id_usuario = :id
       AND p.nome_perfil = 'Vendedor'
       AND (u.is_system = 1 OR u.ativo = 1)
     LIMIT 1"
);
$stmt->execute(['id' => $vendedor]);
if (!$stmt->fetchColumn()) {
    $errors['id_usuario_vendedor'] = 'Selecione um vendedor válido e ativo.';
}

$itens = $_POST['itens'] ?? [];
$itensValidados = [];
$produtosVistos = [];

if (!is_array($itens)) {
    $itens = [];
}

foreach ($itens as $idx => $item) {
    if (!is_array($item)) {
        continue;
    }

    $produto = (int) ($item['id_produto'] ?? 0);
    $qtdRaw = trim((string) ($item['qtd'] ?? ''));
    $unitRaw = trim((string) ($item['valor_unitario'] ?? ''));
    $descRaw = trim((string) ($item['desconto'] ?? ''));

    if ($produto === 0) {
        if ($qtdRaw !== '' || $unitRaw !== '' || $descRaw !== '' && $descRaw !== '0') {
            $errors['itens'] = 'Não deixe valores preenchidos em uma linha sem produto.';
            break;
        }
        continue;
    }

    if (isset($produtosVistos[$produto])) {
        $errors['itens'] = 'O mesmo produto não pode aparecer duas vezes no orçamento.';
        break;
    }
    $produtosVistos[$produto] = true;

    $qtd = (int) $qtdRaw;
    if ($qtd <= 0) {
        $errors['itens'] = 'A quantidade de cada item precisa ser maior que zero.';
        break;
    }

    $unit = normalizarDecimal($unitRaw);
    if ($unitRaw === '' || $unit === null || $unit < 0) {
        $errors['itens'] = 'Informe um valor unitário válido para cada item.';
        break;
    }

    $desconto = normalizarDecimal($descRaw);
    if ($descRaw !== '' && ($desconto === null || $desconto < 0)) {
        $errors['itens'] = 'Informe um desconto válido para cada item.';
        break;
    }
    $desconto = $desconto ?? 0.0;

    $itensValidados[] = [
        'id_produto' => $produto,
        'qtd' => $qtd,
        'valor_unitario' => $unit,
        'desconto' => $desconto,
    ];
}

if (!$errors && $itensValidados) {
    $ids = array_column($itensValidados, 'id_produto');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $check = $db->prepare("SELECT id_produto FROM Produto WHERE id_produto IN ($placeholders)");
    $check->execute($ids);
    $existentes = array_map('intval', $check->fetchAll(PDO::FETCH_COLUMN));
    if (count($existentes) !== count($ids)) {
        $errors['itens'] = 'Um ou mais produtos selecionados não existem.';
    }
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header('Location: orcamento-form.php?id=' . $id);
    exit;
}

try {
    $db->beginTransaction();

    $db->prepare(
        'UPDATE Orcamento
         SET num_orcamento = :num,
             payback = :payback,
             valor_totalCE = :totalCE,
             valor_totalCEPI = :totalCEPI,
             data_emissao = :data_emissao,
             investimento = :investimento,
             id_usuario_vendedor = :vendedor
         WHERE id_orcamento = :id'
    )->execute([
        'num' => $num,
        'payback' => $payback,
        'totalCE' => $totalCE,
        'totalCEPI' => $totalCEPI,
        'data_emissao' => $data !== '' ? $data : null,
        'investimento' => $investimento,
        'vendedor' => $vendedor,
        'id' => $id,
    ]);

    // Regrava os itens para permitir incluir, remover ou alterar produtos.
    $db->prepare('DELETE FROM Orcamento_Prod WHERE id_orcamento = :id')->execute(['id' => $id]);

    $insertItem = $db->prepare(
        'INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
         VALUES (:orcamento, :produto, :qtd, :unitario, :desconto)'
    );

    foreach ($itensValidados as $item) {
        $insertItem->execute([
            'orcamento' => $id,
            'produto' => $item['id_produto'],
            'qtd' => $item['qtd'],
            'unitario' => $item['valor_unitario'],
            'desconto' => $item['desconto'],
        ]);
    }

    $db->commit();
    $_SESSION['flash_success'] = 'Orçamento atualizado com sucesso.';
    header('Location: orcamento-detalhe.php?id=' . $id);
    exit;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Falha ao editar orçamento #' . $id . ': ' . $e->getMessage());
    $_SESSION['errors'] = ['num_orcamento' => 'Não foi possível salvar as alterações.'];
    $_SESSION['old'] = $old;
    header('Location: orcamento-form.php?id=' . $id);
    exit;
}
