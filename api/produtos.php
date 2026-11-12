<?php
// GET api/produtos.php?q=termo  -> lista (busca por nome ou descrição)
// GET api/produtos.php?id=5     -> detalhe de um produto
require_once __DIR__ . '/_comum.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    responder(405, ['ok' => false, 'erro' => 'Método não permitido.']);
}

try {
    // ---- Detalhe ----
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            responder(400, ['ok' => false, 'erro' => 'id inválido.']);
        }

        $stmt = $pdo->prepare(
            "SELECT p.id, p.nome, p.valor, p.descricao,
                    COALESCE(SUM(vi.quantidade), 0) AS total_vendido
             FROM produtos p
             LEFT JOIN vendas_itens vi ON vi.produto_id = p.id
             WHERE p.id = ?
             GROUP BY p.id, p.nome, p.valor, p.descricao"
        );
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            responder(404, ['ok' => false, 'erro' => 'Produto não encontrado.']);
        }
        responder(200, ['ok' => true, 'produto' => $produto]);
    }

    // ---- Busca / listagem ----
    $q = trim($_GET['q'] ?? '');
    $stmt = $pdo->prepare(
        "SELECT id, nome, valor, descricao
         FROM produtos
         WHERE nome LIKE ? OR descricao LIKE ?
         ORDER BY id DESC
         LIMIT 50"
    );
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
    $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

    responder(200, ['ok' => true, 'total' => count($lista), 'produtos' => $lista]);

} catch (PDOException $e) {
    // Detalhe técnico só no log do servidor, nunca no navegador.
    error_log('api/produtos.php: ' . $e->getMessage());
    responder(500, ['ok' => false, 'erro' => 'Erro interno ao consultar produtos.']);
}
