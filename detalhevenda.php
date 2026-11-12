<?php
    require_once 'conexao.php';
    include("protect.php");

    $id = $_GET['id'] ?? null;

    if(!$id) {
        header("Location: vendas.php");
        exit;
    }

    // Buscar dados da venda
    $stmt = $pdo->prepare("
        SELECT v.*, c.nome as cliente_nome, c.email as cliente_email, c.cpf as cliente_cpf
        FROM vendas v
        INNER JOIN clientes c ON v.cliente_id = c.id
        WHERE v.id = ?
    ");
    $stmt->execute([$id]);
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$venda) {
        header("Location: vendas.php");
        exit;
    }

    // Buscar itens da venda
    $stmt = $pdo->prepare("
        SELECT vi.*, p.nome as produto_nome, p.descricao as produto_descricao
        FROM vendas_itens vi
        INNER JOIN produtos p ON vi.produto_id = p.id
        WHERE vi.venda_id = ?
        ORDER BY vi.id
    ");
    $stmt->execute([$id]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Venda #<?= $venda['id'] ?></title>   
</head>
<body>
    <div>
        <h1>Detalhes da Venda #<?= $venda['id'] ?></h1>
        
        <div>
            <h3>Informações do Cliente</h3>
            <p><strong>Nome:</strong> <?= htmlspecialchars($venda['cliente_nome']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($venda['cliente_email']) ?></p>
            <p><strong>CPF:</strong> <?= htmlspecialchars($venda['cliente_cpf']) ?></p>
        </div>
        
        <div>
            <h3>Informações da Venda</h3>
            <p><strong>Data:</strong> <?= date('d/m/Y H:i:s', strtotime($venda['data_venda'])) ?></p>
            <p><strong>Valor Total:</strong> R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></p>
        </div>
        
        <div>
            <h3>Produtos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($itens as $item): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($item['produto_nome']) ?></strong>
                            <?php if($item['produto_descricao']): ?>
                                <br><small><?= htmlspecialchars($item['produto_descricao']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $item['quantidade'] ?></td>
                        <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                        <td>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div>
                Total da Venda: R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?>
            </div>
        </div>
        
        <div>
            <a href="vendas.php">Voltar</a>          
        </div>
    </div>
</body>
</html>