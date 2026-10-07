<?php
/**
 * Single shared PDO connection. Call getDb() wherever you need to talk to
 * the database — it connects once per request and reuses the connection.
 */
if (!function_exists('getDb')) {
function getDb(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require __DIR__ . '/db-config.php';

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['name'],
        $config['charset']
    );

    try {
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Never leak DSN/credentials or raw exception details to the browser.
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(500);

        if (!empty($config['debug'])) {
            exit('Falha na conexão com o banco: ' . htmlspecialchars($e->getMessage()));
        }

        exit('Algo deu errado. Tente novamente mais tarde.');
    }

    return $pdo;
}
}
