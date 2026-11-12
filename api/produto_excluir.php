<?php
// POST api/produto_excluir.php  com corpo JSON: {"id": 5}
require_once __DIR__ . '/_comum.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'erro' => 'Use POST.']);
}

// JSON enviado pelo fetch não vem em $_POST: lemos o corpo cru.
$dados = json_decode(file_get_contents('php://input'), true);
$id = isset($dados['id']) ? filter_var($dados['id'], FILTER_VALIDATE_INT) : false;

if ($id === false) {
    responder(400, ['ok' => false, 'erro' => 'id ausente ou inválido.']);
}

try {
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        responder(404, ['ok' => false, 'erro' => 'Produto não encontrado.']);
    }
    responder(200, ['ok' => true, 'id' => $id]);

} catch (PDOException $e) {
    // 23000 = violação de chave estrangeira (produto já foi vendido)
    if ($e->getCode() === '23000') {
        responder(409, ['ok' => false, 'erro' => 'Este produto já foi vendido e não pode ser excluído.']);
    }
    error_log('api/produto_excluir.php: ' . $e->getMessage());
    responder(500, ['ok' => false, 'erro' => 'Erro interno ao excluir.']);
}
