<?php
    include("protect.php");
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
     <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/dashboard.css">  
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid">
            <i class="fa-solid fa-book-open-reader" id="nav_logo"> BOOKS <br>& COFFEE <i class="fa-solid fa-mug-hot"></i></i>
            <h3>Bem vindo ao Dashboard, 
                <?php echo $_SESSION["nome"]; ?>
                ! 
            </h3>
            <p><a href="logout.php">Sair</a></p>
        </div>
    </nav>
    <div class="container my-5">
        <h1 class="text-center mb-4">Menu Principal</h1>
        <div class="row justify-content-center">
            
            <!-- Card Simulador de Compras -->
            <div class="col-md-4">
                <div class="card shadow p-3 mb-5 bg-white rounded">
                    <div class="card-body text-center">
                        <h4 class="card-title">Simulador de Compras</h4>
                        <p class="card-text">Selecione livros e cafés e calcule o valor total da compra.</p>
                        <a href="simulacao.html" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>

            <!-- Card Cadastro de Usuários -->
            <div class="col-md-4">
                <div class="card shadow p-3 mb-5 bg-white rounded">
                    <div class="card-body text-center">
                        <h4 class="card-title">Cadastro de Produtos</h4>
                        <p class="card-text">Gerencie produtos com as funções de criar, visualizar, editar e excluir.</p>
                        <a href="produtos.php" class="btn btn-success">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <!-- Card Cadastro de Clientes -->
            <div class="col-md-4">
                <div class="card shadow p-3 mb-5 bg-white rounded">
                    <div class="card-body text-center">
                        <h4 class="card-title">Cadastro de Clientes</h4>
                        <p class="card-text">Gerencie clientes com as funções de criar, visualizar, editar e excluir.</p>
                        <a href="clientes.php" class="btn btn-secondary">Acessar</a>
                    </div>
                </div>
            </div>

             <!-- Card Cadastro de Vendas -->
            <div class="col-md-4">
                <div class="card shadow p-3 mb-5 bg-white rounded">
                    <div class="card-body text-center">
                        <h4 class="card-title">Cadastro de Vendas</h4>
                        <p class="card-text">Gerencie as vendas com as funções de criar, visualizar, editar e excluir.</p>
                        <a href="vendas.php" class="btn btn-info">Acessar</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
