<?php
$requiredPermission = 'gerenciar_estoque';
require __DIR__ . '/includes/require-permission.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: placas.php');
    exit;
}

if (!verifyCsrf()) {
    $_SESSION['flash_error'] = 'Sessão expirada ou requisição inválida. Tente novamente.';
    header('Location: placas.php');
    exit;
}

$db = getDb();
$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

if ($action !== 'save') {
    $_SESSION['flash_error'] = 'Ação inválida.';
    header('Location: placas.php');
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$brand = trim((string) ($_POST['brand'] ?? ''));
$fornecedor = trim((string) ($_POST['fornecedor'] ?? ''));
$descricao = trim((string) ($_POST['descricao'] ?? ''));
$quantidadeRaw = trim((string) ($_POST['quantidade'] ?? ''));
$tamRaw = trim((string) ($_POST['tam'] ?? ''));
$voltagemRaw = trim((string) ($_POST['voltagem'] ?? ''));
$valorRaw = trim((string) ($_POST['valor'] ?? ''));

$errors = [];
$quantidade = filter_var($quantidadeRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$tam = $tamRaw === '' ? null : filter_var($tamRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$voltagem = $voltagemRaw === '' ? null : filter_var($voltagemRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
$valor = $valorRaw === '' ? null : filter_var($valorRaw, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0]]);

if ($name === '') {
    $errors['name'] = 'Informe o nome do produto.';
}
if ($quantidade === false) {
    $errors['quantidade'] = 'Informe uma quantidade inteira igual ou maior que zero.';
}
if ($tamRaw !== '' && $tam === false) {
    $errors['tam'] = 'Informe uma potência válida.';
}
if ($voltagemRaw !== '' && $voltagem === false) {
    $errors['voltagem'] = 'Informe uma voltagem válida.';
}
if ($valorRaw !== '' && $valor === false) {
    $errors['valor'] = 'Informe um valor válido.';
}

if ($errors) {
    $_SESSION['stock_errors'] = $errors;
    $_SESSION['stock_old'] = $_POST;
    header('Location: produto-form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

$quantidade = (int) $quantidade;
$tam = $tam === false ? null : $tam;
$voltagem = $voltagem === false ? null : $voltagem;
$valor = $valor === false ? null : $valor;

try {
    $db->beginTransaction();

    if ($id > 0) {
        $stmt = $db->prepare(
            'SELECT p.id_produto, p.nome_produto, e.id_estoque, e.quantidade
             FROM Produto p
             LEFT JOIN Estoque e ON e.id_produto = p.id_produto
             WHERE p.id_produto = :id
             LIMIT 1
             FOR UPDATE'
        );
        $stmt->execute(['id' => $id]);
        $atual = $stmt->fetch();

        if (!$atual) {
            throw new RuntimeException('Produto não encontrado.');
        }

        $db->prepare(
            'UPDATE Produto
             SET nome_produto = :nome, marca_produto = :marca, voltagem_produto = :voltagem,
                 valorUn_produto = :valor, tam_produto = :tam, descricao_produto = :descricao,
                 fornecedor = :fornecedor
             WHERE id_produto = :id'
        )->execute([
            'nome' => $name,
            'marca' => $brand !== '' ? $brand : null,
            'voltagem' => $voltagem,
            'valor' => $valor,
            'tam' => $tam,
            'descricao' => $descricao !== '' ? $descricao : null,
            'fornecedor' => $fornecedor !== '' ? $fornecedor : null,
            'id' => $id,
        ]);

        $qtdAnterior = (int) ($atual['quantidade'] ?? 0);
        if ($atual['id_estoque']) {
            $db->prepare('UPDATE Estoque SET quantidade = :qtd WHERE id_estoque = :id')->execute([
                'qtd' => $quantidade,
                'id' => $atual['id_estoque'],
            ]);
        } else {
            $db->prepare('INSERT INTO Estoque (quantidade, id_produto) VALUES (:qtd, :produto)')->execute([
                'qtd' => $quantidade,
                'produto' => $id,
            ]);
        }

        $delta = $quantidade - $qtdAnterior;
        if ($delta !== 0) {
            $tipo = $delta > 0 ? 'entrada' : 'saida';
            $db->prepare(
                'INSERT INTO MovimentacaoEstoque (tipo, quantidade, data_movimentacao, id_estoque)
                 VALUES (:tipo, :quantidade, CURDATE(),
                    (SELECT id_estoque FROM Estoque WHERE id_produto = :produto LIMIT 1))'
            )->execute([
                'tipo' => $tipo,
                'quantidade' => abs($delta),
                'produto' => $id,
            ]);
        }

        $mensagem = 'Produto atualizado com sucesso.';
    } else {
        $db->prepare(
            'INSERT INTO Produto (nome_produto, marca_produto, voltagem_produto, valorUn_produto, tam_produto, descricao_produto, fornecedor)
             VALUES (:nome, :marca, :voltagem, :valor, :tam, :descricao, :fornecedor)'
        )->execute([
            'nome' => $name,
            'marca' => $brand !== '' ? $brand : null,
            'voltagem' => $voltagem,
            'valor' => $valor,
            'tam' => $tam,
            'descricao' => $descricao !== '' ? $descricao : null,
            'fornecedor' => $fornecedor !== '' ? $fornecedor : null,
        ]);

        $novoProdutoId = (int) $db->lastInsertId();
        $db->prepare('INSERT INTO Estoque (quantidade, id_produto) VALUES (:qtd, :produto)')->execute([
            'qtd' => $quantidade,
            'produto' => $novoProdutoId,
        ]);
        $novoEstoqueId = (int) $db->lastInsertId();

        if ($quantidade > 0) {
            $db->prepare(
                'INSERT INTO MovimentacaoEstoque (tipo, quantidade, data_movimentacao, id_estoque)
                 VALUES (\'entrada\', :quantidade, CURDATE(), :estoque)'
            )->execute(['quantidade' => $quantidade, 'estoque' => $novoEstoqueId]);
        }

        $mensagem = 'Produto criado com sucesso.';
    }

    $db->commit();
    $_SESSION['flash_success'] = $mensagem;
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Falha ao salvar produto/estoque: ' . $e->getMessage());
    $_SESSION['stock_errors'] = ['general' => 'Não foi possível salvar o produto.'];
    $_SESSION['stock_old'] = $_POST;
    header('Location: produto-form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

header('Location: placas.php');
exit;
