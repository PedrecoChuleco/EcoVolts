<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (estaLogado()) {
    redirecionar('dashboard.php');
}

$erro    = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoVolts - Criar conta</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="topo">
    <nav class="nav-publica">
        <span class="marca">EcoVolts</span>
        <a href="index.php">Já tenho conta</a>
    </nav>
</header>

<main class="pagina">
<section class="esquerda">
    <img class="logo" src="./ecovolts-logo.png" alt="Logo EcoVolts">
    <div class="slogan">
        <span></span>
        <h2>Inteligência a serviço da<br>energia limpa.</h2>
        <p>Sistema de apoio à sustentabilidade<br>por meio da energia solar.</p>
    </div>
</section>

<section class="direita">
<div class="card">
    <h1>Criar cadastro</h1>
    <p class="sub">Cadastre um novo usuário no sistema</p>

    <form id="cadastroForm" action="includes/processa_cadastro.php" method="POST" novalidate>
        <label for="nome">Nome</label>
        <input id="nome" name="nome" type="text" placeholder="Nome completo" required>

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" placeholder="Seu e-mail" required>

        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" placeholder="Crie uma senha (mín. 6 caracteres)" required minlength="6">

        <label for="confirmarSenha">Confirmar senha</label>
        <input id="confirmarSenha" name="confirmarSenha" type="password" placeholder="Repita a senha" required minlength="6">

        <button type="submit">Cadastrar</button>
        <p id="mensagemCadastro" class="mensagem <?= $erro ? 'erro' : ($sucesso ? 'sucesso' : '') ?>">
            <?= h($erro ?: $sucesso) ?>
        </p>
    </form>

    <div class="ou"><span>ou</span></div>
    <p class="cadastro">Já possui cadastro?
        <a href="index.php">Fazer login</a>
    </p>
</div>
<footer class="rodape">EcoVolts • ODS 9 • Energia limpa e inovação</footer>
</section>
</main>

<script src="js/validacao.js"></script>
</body>
</html>
