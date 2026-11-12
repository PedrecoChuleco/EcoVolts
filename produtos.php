<?php
    require("conexao.php");
    include("protect.php");
    // A listagem, a busca, o detalhe e a exclusão agora são feitos
    // via fetch() em api/produtos.php e api/produto_excluir.php.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container my-4">
        <p><a href="dashboard.php">&larr; Voltar ao menu</a></p>
        <h1 class="mb-3">Gerenciamento de Produtos</h1>

        <div class="d-flex gap-2 mb-2">
            <input type="search" id="busca" class="form-control" placeholder="Buscar por nome ou descrição..." autocomplete="off">
            <a href="formproduto.php" class="btn btn-success text-nowrap" style="color:#fff">Novo produto</a>
        </div>

        <div id="status" class="small my-2" role="status" aria-live="polite"></div>

        <div id="detalhe" class="card card-body shadow-sm mb-3" hidden></div>

        <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>ID</th><th>Nome</th><th>Preço</th><th>Descrição</th><th>Ações</th></tr>
                </thead>
                <tbody id="corpo-tabela"></tbody>
            </table>
        </div>
    </div>
    <script src="js/produtos.js"></script>
</body>
</html>
