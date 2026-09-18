<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
exigirLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../dashboard.php');
}

$usuarioId = $_SESSION['usuario_id'];
$nome      = trim($_POST['nome_equipamento'] ?? '');
$potencia  = $_POST['potencia_kwh'] ?? '';
$data      = $_POST['data_registro'] ?? '';
$obs       = trim($_POST['observacao'] ?? '');

if ($nome === '' || $potencia === '' || $data === '') {
    redirecionar('../dashboard.php?erro=' . urlencode('Preencha os campos obrigatórios.'));
}

if (!is_numeric($potencia) || (float) $potencia < 0) {
    redirecionar('../dashboard.php?erro=' . urlencode('Informe uma potência válida.'));
}

$stmt = $pdo->prepare(
    'INSERT INTO registros (usuario_id, nome_equipamento, potencia_kwh, data_registro, observacao)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$usuarioId, $nome, $potencia, $data, $obs]);

redirecionar('../dashboard.php?sucesso=' . urlencode('Registro criado com sucesso!'));
