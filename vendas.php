<?php
    require("conexao.php");
    include("protect.php");

    // Buscar todas as vendas com informações do cliente
    $stmt = $pdo->query("
        SELECT v.*, c.nome as cliente_nome 
        FROM vendas v
        INNER JOIN clientes c ON v.cliente_id = c.id
        ORDER BY v.id DESC
    ");
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar exclusão
    if(isset($_GET['delete'])) {
        $id = $_GET['delete'];
        
        // Excluir itens da venda primeiro (relacionamento)
        $stmt = $pdo->prepare("DELETE FROM vendas_itens WHERE venda_id = ?");
        $stmt->execute([$id]);
        
        // Excluir a venda
        $stmt = $pdo->prepare("DELETE FROM vendas WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: vendas.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Vendas</title>   
</head>
<body>
    <h1>Gerenciamento de Vendas</h1>
    
    <a href="formvenda.php">Cadastrar Nova Venda</a>
    
    <h2>Lista de Vendas</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Valor Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($vendas) > 0){ ?>
                <?php foreach($vendas as $venda): ?>
                <tr>
                    <td><?= htmlspecialchars($venda['id']) ?></td>
                    <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                    <td>R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                    <td>
                        <a href="detalhevenda.php?id=<?= $venda['id'] ?>">Visualizar</a>                       
                        <a href="vendas.php?delete=<?= $venda['id'] ?>" onclick="return confirm('Deseja realmente excluir esta venda?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php }else{ ?>
                <tr>
                    <td colspan="5">Nenhuma venda cadastrada</td>
                </tr>
            <?php }; ?>
        </tbody>
    </table>
</body>
</html>