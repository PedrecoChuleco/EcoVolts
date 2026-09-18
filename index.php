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
<title>EcoVolts - Login</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="topo">
    <nav class="nav-publica">
        <span class="marca">EcoVolts</span>
        <a href="cadastro.php">Criar conta</a>
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
    <h1>Bem-vindo</h1>
    <p class="sub">Acesse sua conta EcoVolts</p>

    <form id="loginForm" action="includes/processa_login.php" method="POST" novalidate>
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" placeholder="Digite seu e-mail" required>

        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" placeholder="Digite sua senha" required>

        <button type="submit">Entrar</button>
        <p id="mensagemLogin" class="mensagem <?= $erro ? 'erro' : ($sucesso ? 'sucesso' : '') ?>">
            <?= h($erro ?: $sucesso) ?>
        </p>
    </form>

    <div class="ou"><span>ou</span></div>
    <p class="cadastro">Ainda não possui cadastro?
        <a href="cadastro.php">Criar conta</a>
    </p>
</div>
<footer class="rodape">EcoVolts • ODS 9 • Energia limpa e inovação</footer>
</section>
</main>

<script src="js/validacao.js"></script>
</body>
</html>
