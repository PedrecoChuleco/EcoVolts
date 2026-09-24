<?php
require __DIR__ . '/../includes/config.php';

unset($_SESSION['user']);
session_destroy();

header('Location: ../index.php');
exit;
