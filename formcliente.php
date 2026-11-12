<?php
    require_once 'conexao.php';
    include("protect.php");

    $id = $_GET['id'] ?? null;
    $cliente = ['nome' => '', 'email' => '', 'telefone' => '', 'cpf' => '', 'endereco' => ''];

    // Buscar cliente para edição
    if($id) {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$cliente) {
            header("Location: clientes.php");
            exit;
        }
    }

    // Processar formulário
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
        $cpf = $_POST['cpf'];
        $endereco = $_POST['endereco'];
        
        if($id) {
            // Atualizar
            $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, cpf = ?, endereco = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $telefone, $cpf, $endereco, $id]);
        } else {
            // Inserir
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, cpf, endereco) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $cpf, $endereco]);
        }
        
        header("Location: clientes.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Cadastrar' ?> Cliente</title>
</head>
<body>
    <h1><?= $id ? 'Editar' : 'Cadastrar' ?> Cliente</h1>
    
    <form method="POST">
        <p>
            <label>Nome:</label><br>
            <!-- Sintaxe longa -->
            <input type="text" name="nome" value="<?php echo htmlspecialchars($cliente['nome']) ?>" required size="50">
        </p>
        
        <p>
            <label>Email:</label><br>
            <!-- Sintaxe curta -->
            <input type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required size="50">
        </p>
        
        <p>
            <label>Telefone:</label><br>
            <input type="text" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>" size="20">
        </p>
        
        <p>
            <label>CPF:</label><br>
            <input type="text" name="cpf" value="<?= htmlspecialchars($cliente['cpf']) ?>" required size="20">
        </p>
        
        <p>
            <label>Endereço:</label><br>
            <textarea name="endereco" rows="3" cols="50"><?= htmlspecialchars($cliente['endereco']) ?></textarea>
        </p>
        
        <p>
            <button type="submit"><?= $id ? 'Atualizar' : 'Cadastrar' ?></button>
            <a href="clientes.php">Cancelar</a>
        </p>
    </form>
</body>
</html>