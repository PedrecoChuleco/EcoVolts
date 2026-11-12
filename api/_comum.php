<?php
// Helpers compartilhados pelos endpoints JSON da pasta api/.
// Nenhum endpoint devolve HTML: apenas JSON + status HTTP.

require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Responde em JSON e encerra o script.
function responder(int $status, array $corpo): void {
    http_response_code($status);
    echo json_encode($corpo, JSON_UNESCAPED_UNICODE);
    exit;
}

// Mesma regra do protect.php, mas em JSON (sem die com HTML).
if (!isset($_SESSION['id'])) {
    responder(401, ['ok' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
}
