<?php
/**
 * Altera o status de um orçamento (somente POST, com CSRF).
 * Exige a permissão `alterar_status_orcamentos` (Vendedor e Administrador).
 */
$requiredPermission = 'alterar_status_orcamentos';
require __DIR__ . '/includes/require-permission.php';
require __DIR__ . '/includes/StatusOrcamento.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orcamentos.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$destino = $id > 0 ? 'orcamento-detalhe.php?id=' . $id : 'orcamentos.php';

if (!verifyCsrf()) {
    $_SESSION['flash_error'] = 'Sessão expirada ou requisição inválida. Tente novamente.';
    header('Location: ' . $destino);
    exit;
}

$status = StatusOrcamento::tryFrom((string) ($_POST['status'] ?? ''));
if ($id <= 0 || !$status) {
    $_SESSION['flash_error'] = 'Orçamento ou status inválido.';
    header('Location: ' . $destino);
    exit;
}

$db = getDb();
$consulta = $db->prepare('SELECT id_orcamento FROM Orcamento WHERE id_orcamento = :id LIMIT 1');
$consulta->execute(['id' => $id]);
if (!$consulta->fetchColumn()) {
    $_SESSION['flash_error'] = 'Orçamento não encontrado.';
    header('Location: orcamentos.php');
    exit;
}

$db->prepare('UPDATE Orcamento SET status = :status WHERE id_orcamento = :id')
    ->execute(['status' => $status->value, 'id' => $id]);

$_SESSION['flash_success'] = 'Status atualizado para "' . $status->label() . '".';
header('Location: ' . $destino);
exit;
