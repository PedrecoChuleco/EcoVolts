<?php
/**
 * Stub register handler. Replace with a real insert into your database
 * (and hash the password with password_hash()).
 */
require __DIR__ . '/../includes/config.php';

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    header('Location: ../register.php?error=' . urlencode('Preencha todos os campos.'));
    exit;
}

// TODO: INSERT the user into your database with password_hash($password, PASSWORD_DEFAULT).
$_SESSION['user'] = [
    'name'  => $name,
    'email' => $email,
];

header('Location: ../dashboard.php');
exit;
