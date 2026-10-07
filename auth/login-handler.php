<?php
/**
 * Login against the real Usuario table. Looks the user up by login
 * (the e-mail), verifies the password hash, and refuses `is_system` /
 * deactivated accounts even if someone somehow had the right password.
 */
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: ../login.php?error=' . urlencode('Preencha e-mail e senha.'));
    exit;
}

$db = getDb();

$stmt = $db->prepare(
    "SELECT u.id_usuario, u.nome_usuario, u.login, u.senha, u.ativo, u.is_system, p.nome_perfil
     FROM Usuario u
     LEFT JOIN Perfil p ON p.id_perfil = u.id_perfil
     WHERE u.login = :login
     LIMIT 1"
);
$stmt->execute(['login' => $email]);
$user = $stmt->fetch();

$genericError = 'E-mail ou senha incorretos.';

if (!$user || !password_verify($password, $user['senha'])) {
    header('Location: ../login.php?error=' . urlencode($genericError));
    exit;
}

if ((int) $user['is_system'] === 1 || (int) $user['ativo'] !== 1) {
    // Same generic message — don't reveal that the account exists but is
    // a system/disabled account.
    header('Location: ../login.php?error=' . urlencode($genericError));
    exit;
}

session_regenerate_id(true); // rotate the session id on privilege change

$_SESSION['user'] = [
    'id'     => (int) $user['id_usuario'],
    'name'   => $user['nome_usuario'],
    'email'  => $user['login'],
    'perfil' => $user['nome_perfil'],
];

header('Location: ../dashboard.php');
exit;
