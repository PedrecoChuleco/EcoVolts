<?php
/**
 * Stub login handler. Replace with a real check against your database.
 * Wires up just enough so $_SESSION['user'] / $auth['user'] behave like
 * the original Inertia `auth.user` prop.
 */
require __DIR__ . '/../includes/config.php';

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: ../login.php?error=' . urlencode('Preencha e-mail e senha.'));
    exit;
}

// TODO: look the user up in your database and verify the password hash.
// For now, any non-empty email/password "logs in" so you can see the
// dashboard-facing parts of the layout.
$_SESSION['user'] = [
    'email' => $email,
];

header('Location: ../dashboard.php');
exit;
