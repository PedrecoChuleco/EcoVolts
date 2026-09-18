<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../index.php');
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    redirecionar('../index.php?erro=' . urlencode('Preencha todos os campos.'));
}

// Prepared statement: o valor do usuário nunca é concatenado na query.
$stmt = $pdo->prepare('SELECT id, nome, email, senha FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$usuario = $stmt->fetch();

// password_verify compara a senha digitada com o hash salvo no banco.
if ($usuario && password_verify($senha, $usuario['senha'])) {
    // Regenera o ID de sessão ao logar (evita session fixation).
    session_regenerate_id(true);

    $_SESSION['usuario_id']    = $usuario['id'];
    $_SESSION['usuario_nome']  = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];

    redirecionar('../dashboard.php');
} else {
    redirecionar('../index.php?erro=' . urlencode('E-mail ou senha incorretos.'));
}
