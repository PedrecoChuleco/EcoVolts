<?php
require __DIR__ . '/includes/config.php';

// Simple route guard: bounce guests back to login.
if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

$variant   = 'account';
$pageTitle = 'EcoVolts - Perfil';
require __DIR__ . '/includes/header.php';
?>