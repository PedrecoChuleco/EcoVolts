<?php
    require("conexao.php");
    include("protect.php");

    // Buscar todos os clientes
    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY id DESC");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar exclusão
    if(isset($_GET['delete'])) {
        $id = $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: clientes.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Clientes</title>
</head>
<body>
    <h1>Gerenciamento de Clientes</h1>
    
    <a href="formcliente.php">Cadastrar Novo Cliente</a>
    
    <h2>Lista de Clientes</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>CPF</th>
                <th>Endereço</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($clientes) > 0){ ?>
                <?php foreach($clientes as $cliente): ?>
                <tr>
                    <!-- Sintaxe longa -->
                    <td><?php echo htmlspecialchars($cliente['id']) ?></td>
                    <!-- Sintaxe curta -->
                    <td><?= htmlspecialchars($cliente['nome']) ?></td>
                    <td><?= htmlspecialchars($cliente['email']) ?></td>
                    <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                    <td><?= htmlspecialchars($cliente['cpf']) ?></td>
                    <td><?= htmlspecialchars($cliente['endereco']) ?></td>
                    <td>
                        <!-- Sintaxe longa -->
                        <a href="formcliente.php?id=<?php echo $cliente['id'] ?>">Editar</a>
                        <!-- Sintaxe curta -->
                        <a href="clientes.php?delete=<?= $cliente['id'] ?>" onclick="return confirm('Deseja realmente excluir?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php }else{ ?>
                <tr>
                    <td colspan="7">Nenhum cliente cadastrado</td>
                </tr>
            <?php }; ?>
        </tbody>
    </table>
</body>
</html>