<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}

$usuarioId = $_SESSION['usuario_id'];
$id        = $_POST['id'] ?? '';

if (!ctype_digit((string) $id)) {
    redirecionar('../dashboard.php?erro=' . urlencode('Registro inválido.'));
}

// Só apaga se o registro pertencer ao usuário logado.
$stmt = $pdo->prepare('DELETE FROM registros WHERE id = ? AND usuario_id = ?');
$stmt->execute([$id, $usuarioId]);

if ($stmt->rowCount() > 0) {
    redirecionar('../dashboard.php?sucesso=' . urlencode('Registro excluído com sucesso!'));
}

redirecionar('../dashboard.php?erro=' . urlencode('Registro não encontrado.'));
