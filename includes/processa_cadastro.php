<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../cadastro.php');
}

$nome            = trim($_POST['nome'] ?? '');
$email           = trim($_POST['email'] ?? '');
$senha           = $_POST['senha'] ?? '';
$confirmarSenha  = $_POST['confirmarSenha'] ?? '';

// --- Validação no servidor (nunca confiar só no JS do navegador) ---
if ($nome === '' || $email === '' || $senha === '' || $confirmarSenha === '') {
    redirecionar('../cadastro.php?erro=' . urlencode('Preencha todos os campos.'));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirecionar('../cadastro.php?erro=' . urlencode('Digite um e-mail válido.'));
}

if (strlen($senha) < 6) {
    redirecionar('../cadastro.php?erro=' . urlencode('A senha deve ter no mínimo 6 caracteres.'));
}

if ($senha !== $confirmarSenha) {
    redirecionar('../cadastro.php?erro=' . urlencode('As senhas não coincidem.'));
}

// Verifica duplicidade de e-mail com prepared statement.
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    redirecionar('../cadastro.php?erro=' . urlencode('Este e-mail já está cadastrado.'));
}

// Nunca salvamos a senha em texto puro — apenas o hash.
$hash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
    $stmt->execute([$nome, $email, $hash]);

    redirecionar('../index.php?sucesso=' . urlencode('Cadastro realizado com sucesso! Faça login.'));
} catch (PDOException $e) {
    // Ex.: corrida entre duas requisições tentando o mesmo e-mail (UNIQUE).
    redirecionar('../cadastro.php?erro=' . urlencode('Erro ao cadastrar. Tente novamente.'));
}
