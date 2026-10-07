<?php
/**
 * Registration against the real Usuario table. Validates input, checks for
 * a duplicate login (the e-mail), hashes the password, and inserts the row
 * with the "Cliente" role.
 */
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/business-lookups.php';

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    header('Location: ../register.php?error=' . urlencode('Preencha todos os campos.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../register.php?error=' . urlencode('E-mail inválido.'));
    exit;
}

if (strlen($password) < 8) {
    header('Location: ../register.php?error=' . urlencode('A senha precisa ter pelo menos 8 caracteres.'));
    exit;
}

$db = getDb();

$check = $db->prepare('SELECT id_usuario FROM Usuario WHERE login = :login LIMIT 1');
$check->execute(['login' => $email]);

if ($check->fetch()) {
    header('Location: ../register.php?error=' . urlencode('Este e-mail já está cadastrado.'));
    exit;
}

$senhaHash  = password_hash($password, PASSWORD_DEFAULT);
$idPerfil   = getPerfilId($db, 'Cliente');

$insert = $db->prepare(
    'INSERT INTO Usuario (login, senha, id_perfil, email_usuario, nome_usuario, ativo, is_system)
     VALUES (:login, :senha, :id_perfil, :email, :nome, 1, 0)'
);
$insert->execute([
    'login'     => $email,
    'senha'     => $senhaHash,
    'id_perfil' => $idPerfil,
    'email'     => $email,
    'nome'      => $name,
]);

$_SESSION['user'] = [
    'id'     => (int) $db->lastInsertId(),
    'name'   => $name,
    'email'  => $email,
    'perfil' => 'Cliente',
];

header('Location: ../dashboard.php');
exit;
