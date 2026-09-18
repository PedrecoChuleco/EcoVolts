<?php
/**
 * Conexão com o banco de dados usando PDO.
 * Ajuste as constantes abaixo conforme seu ambiente MySQL.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'ecovolts_db';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // usa prepared statements reais
        ]
    );
} catch (PDOException $e) {
    // Em produção, nunca exponha $e->getMessage() ao usuário final.
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}
