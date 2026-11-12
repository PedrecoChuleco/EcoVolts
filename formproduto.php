<?php
    require_once 'conexao.php';
    include("protect.php");

    $id = $_GET['id'] ?? null;
    $produto = ['nome' => '', 'valor' => '', 'descricao' => ''];

    // Buscar produto para edição
    if($id) {
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$produto) {
            header("Location: produtos.php");
            exit;
        }
}

// Processar formulário
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];
    $descricao = $_POST['descricao'];
    
    if($id) {
        // Atualizar
        $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, valor = ?, descricao = ? WHERE id = ?");
        $stmt->execute([$nome, $valor, $descricao, $id]);
    } else {
        // Inserir
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, valor, descricao) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $valor, $descricao]);
    }
    
    header("Location: produtos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Cadastrar' ?> Produto</title>
</head>
<body>
    <h1><?= $id ? 'Editar' : 'Cadastrar' ?> Produto</h1>
    
    <form method="POST">
        <p>
            <label>Nome:</label><br>
            <!-- Sintaxe longa -->
            <input type="text" name="nome" value="<?php echo htmlspecialchars($produto['nome']) ?>" required size="50">
        </p>
        
        <p>
            <label>Valor:</label><br>
            <!-- Sintaxe curta -->
            <input type="number" name="valor" value="<?= htmlspecialchars($produto['valor']) ?>" step="0.01" required>
        </p>
        
        <p>
            <label>Descrição:</label><br>
            <textarea name="descricao" rows="5" cols="50"><?= htmlspecialchars($produto['descricao']) ?></textarea>
        </p>
        
        <p>
            <button type="submit"><?= $id ? 'Atualizar' : 'Cadastrar' ?></button>
            <a href="produtos.php">Cancelar</a>
        </p>
    </form>
</body>
</html>