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
$nome      = trim($_POST['nome_equipamento'] ?? '');
$potencia  = $_POST['potencia_kwh'] ?? '';
$data      = $_POST['data_registro'] ?? '';
$obs       = trim($_POST['observacao'] ?? '');

if (!ctype_digit((string) $id)) {
    redirecionar('../dashboard.php?erro=' . urlencode('Registro inválido.'));
}

if ($nome === '' || $potencia === '' || $data === '') {
    redirecionar('../dashboard.php?erro=' . urlencode('Preencha os campos obrigatórios.'));
}

if (!is_numeric($potencia) || (float) $potencia < 0) {
    redirecionar('../dashboard.php?erro=' . urlencode('Informe uma potência válida.'));
}

// O "AND usuario_id = ?" garante que um usuário só edite os próprios registros.
$stmt = $pdo->prepare(
    'UPDATE registros
     SET nome_equipamento = ?, potencia_kwh = ?, data_registro = ?, observacao = ?
     WHERE id = ? AND usuario_id = ?'
);
$stmt->execute([$nome, $potencia, $data, $obs, $id, $usuarioId]);

if ($stmt->rowCount() > 0) {
    redirecionar('../dashboard.php?sucesso=' . urlencode('Registro atualizado com sucesso!'));
}

redirecionar('../dashboard.php?erro=' . urlencode('Registro não encontrado ou nada foi alterado.'));
