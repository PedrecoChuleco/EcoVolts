<?php
require_once __DIR__ . '/includes/auth.php';

$_SESSION = [];
session_unset();
session_destroy();

header('Location: index.php');
exit;
