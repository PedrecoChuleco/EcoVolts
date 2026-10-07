<?php
$requiredPermission = 'gerenciar_estoque';
require __DIR__ . '/includes/require-permission.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: movimentacao-estoque.php');
    exit;
}

if (!verifyCsrf()) {
    $_SESSION['flash_error'] = 'Sessão expirada ou requisição inválida. Tente novamente.';
    header('Location: movimentacao-estoque.php');
    exit;
}

$db = getDb();
$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

function effect(string $tipo, int $quantidade): int
{
    return strtolower($tipo) === 'entrada' ? $quantidade : -$quantidade;
}

function backToForm(int $id, string $error): never
{
    $_SESSION['movement_error'] = $error;
    $_SESSION['movement_old'] = $_POST;
    header('Location: movimentacao-estoque-form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

if (!in_array($action, ['create', 'update', 'delete'], true)) {
    $_SESSION['flash_error'] = 'Ação inválida.';
    header('Location: movimentacao-estoque.php');
    exit;
}

try {
    $db->beginTransaction();

    if ($action === 'create') {
        $idEstoque = (int) ($_POST['id_estoque'] ?? 0);
        $tipo = strtolower(trim((string) ($_POST['tipo'] ?? '')));
        $quantidade = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $data = trim((string) ($_POST['data'] ?? ''));

        if ($idEstoque <= 0 || !in_array($tipo, ['entrada', 'saida'], true) || $quantidade === false) {
            $db->rollBack();
            backToForm(0, 'Informe produto, tipo e uma quantidade válida.');
        }
        $dt = DateTime::createFromFormat('Y-m-d', $data);
        if (!$dt || $dt->format('Y-m-d') !== $data) {
            $db->rollBack();
            backToForm(0, 'Informe uma data válida.');
        }

        $stmt = $db->prepare('SELECT id_estoque, quantidade FROM Estoque WHERE id_estoque = :id FOR UPDATE');
        $stmt->execute(['id' => $idEstoque]);
        $estoque = $stmt->fetch();
        if (!$estoque) {
            throw new RuntimeException('Estoque não encontrado.');
        }

        $saldoNovo = (int) $estoque['quantidade'] + effect($tipo, (int) $quantidade);
        if ($saldoNovo < 0) {
            $db->rollBack();
            backToForm(0, 'A saída não pode deixar o estoque negativo.');
        }

        $db->prepare('INSERT INTO MovimentacaoEstoque (tipo, quantidade, data_movimentacao, id_estoque) VALUES (:tipo, :quantidade, :data, :estoque)')->execute([
            'tipo' => $tipo,
            'quantidade' => (int) $quantidade,
            'data' => $data,
            'estoque' => $idEstoque,
        ]);
        $db->prepare('UPDATE Estoque SET quantidade = :qtd WHERE id_estoque = :id')->execute([
            'qtd' => $saldoNovo,
            'id' => $idEstoque,
        ]);

        $db->commit();
        $_SESSION['flash_success'] = 'Movimentação registrada e estoque atualizado.';
        header('Location: movimentacao-estoque.php');
        exit;
    }

    if ($action === 'update') {
        if ($id <= 0) {
            throw new RuntimeException('Movimentação inválida.');
        }

        $idEstoqueNovo = (int) ($_POST['id_estoque'] ?? 0);
        $tipoNovo = strtolower(trim((string) ($_POST['tipo'] ?? '')));
        $quantidadeNova = filter_var($_POST['quantidade'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $dataNova = trim((string) ($_POST['data'] ?? ''));

        if ($idEstoqueNovo <= 0 || !in_array($tipoNovo, ['entrada', 'saida'], true) || $quantidadeNova === false) {
            $db->rollBack();
            backToForm($id, 'Informe produto, tipo e uma quantidade válida.');
        }
        $dt = DateTime::createFromFormat('Y-m-d', $dataNova);
        if (!$dt || $dt->format('Y-m-d') !== $dataNova) {
            $db->rollBack();
            backToForm($id, 'Informe uma data válida.');
        }

        $stmt = $db->prepare('SELECT id_movimentacao, tipo, quantidade, id_estoque FROM MovimentacaoEstoque WHERE id_movimentacao = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);
        $anterior = $stmt->fetch();
        if (!$anterior) {
            throw new RuntimeException('Movimentação não encontrada.');
        }

        $idsEstoque = [(int) $anterior['id_estoque'], $idEstoqueNovo];
        $idsEstoque = array_values(array_unique($idsEstoque));
        sort($idsEstoque, SORT_NUMERIC);

        $estoquesBloqueados = [];
        $q = $db->prepare('SELECT id_estoque, quantidade FROM Estoque WHERE id_estoque = :id FOR UPDATE');
        foreach ($idsEstoque as $estoqueId) {
            $q->execute(['id' => $estoqueId]);
            $row = $q->fetch();
            if (!$row) {
                throw new RuntimeException('Estoque selecionado não foi encontrado.');
            }
            $estoquesBloqueados[$estoqueId] = $row;
        }

        $saldoAntigo = (int) $estoquesBloqueados[(int) $anterior['id_estoque']]['quantidade'];
        $saldoRevertido = $saldoAntigo - effect((string) $anterior['tipo'], (int) $anterior['quantidade']);
        if ($saldoRevertido < 0) {
            throw new RuntimeException('A movimentação não pode ser editada porque o saldo atual é incompatível com a reversão do histórico.');
        }
        $estoquesBloqueados[(int) $anterior['id_estoque']]['quantidade'] = $saldoRevertido;

        $saldoBaseNovo = (int) $estoquesBloqueados[$idEstoqueNovo]['quantidade'];
        $saldoFinalNovo = $saldoBaseNovo + effect($tipoNovo, (int) $quantidadeNova);
        if ($saldoFinalNovo < 0) {
            throw new RuntimeException('A nova movimentação não pode deixar o estoque negativo.');
        }

        $db->prepare(
            'UPDATE MovimentacaoEstoque
             SET tipo = :tipo, quantidade = :quantidade, data_movimentacao = :data, id_estoque = :estoque
             WHERE id_movimentacao = :id'
        )->execute([
            'tipo' => $tipoNovo,
            'quantidade' => (int) $quantidadeNova,
            'data' => $dataNova,
            'estoque' => $idEstoqueNovo,
            'id' => $id,
        ]);

        foreach ($estoquesBloqueados as $estoqueId => $row) {
            $saldo = (int) $row['quantidade'];
            if ($estoqueId === $idEstoqueNovo) {
                $saldo = $saldoFinalNovo;
            }
            $db->prepare('UPDATE Estoque SET quantidade = :qtd WHERE id_estoque = :id')->execute([
                'qtd' => $saldo,
                'id' => $estoqueId,
            ]);
        }

        $db->commit();
        $_SESSION['flash_success'] = 'Movimentação alterada e estoque recalculado.';
        header('Location: movimentacao-estoque.php');
        exit;
    }

    // delete
    if ($id <= 0) {
        throw new RuntimeException('Movimentação inválida.');
    }

    $stmt = $db->prepare('SELECT id_movimentacao, tipo, quantidade, id_estoque FROM MovimentacaoEstoque WHERE id_movimentacao = :id FOR UPDATE');
    $stmt->execute(['id' => $id]);
    $anterior = $stmt->fetch();
    if (!$anterior) {
        throw new RuntimeException('Movimentação não encontrada.');
    }

    $stmt = $db->prepare('SELECT quantidade FROM Estoque WHERE id_estoque = :id FOR UPDATE');
    $stmt->execute(['id' => (int) $anterior['id_estoque']]);
    $estoque = $stmt->fetch();
    if (!$estoque) {
        throw new RuntimeException('Estoque da movimentação não foi encontrado.');
    }

    $saldoNovo = (int) $estoque['quantidade'] - effect((string) $anterior['tipo'], (int) $anterior['quantidade']);
    if ($saldoNovo < 0) {
        throw new RuntimeException('Esta movimentação não pode ser excluída porque deixaria o estoque negativo.');
    }

    $db->prepare('DELETE FROM MovimentacaoEstoque WHERE id_movimentacao = :id')->execute(['id' => $id]);
    $db->prepare('UPDATE Estoque SET quantidade = :qtd WHERE id_estoque = :id')->execute([
        'qtd' => $saldoNovo,
        'id' => (int) $anterior['id_estoque'],
    ]);

    $db->commit();
    $_SESSION['flash_success'] = 'Movimentação excluída e estoque ajustado.';
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Falha na movimentação de estoque: ' . $e->getMessage());
    $_SESSION['flash_error'] = $e->getMessage() !== '' ? $e->getMessage() : 'Não foi possível concluir a operação.';
}

header('Location: movimentacao-estoque.php');
exit;
