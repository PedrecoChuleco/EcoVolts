<?php
require __DIR__ . '/includes/config.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit;
}

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/address-helpers.php';

$old = $_POST;
$errors = [];

$name         = trim($_POST['name'] ?? '');
$emailUsuario = trim($_POST['email_usuario'] ?? '');
$cpf          = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$cnpj         = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');

$newPassword  = $_POST['new_password'] ?? '';
$newPassword2 = $_POST['new_password_confirm'] ?? '';

if ($name === '') {
    $errors['name'] = 'Informe seu nome.';
}

if ($emailUsuario !== '' && !filter_var($emailUsuario, FILTER_VALIDATE_EMAIL)) {
    $errors['email_usuario'] = 'E-mail de contato inválido.';
}

if ($cpf !== '' && strlen($cpf) !== 11) {
    $errors['cpf'] = 'CPF deve ter 11 dígitos.';
}

if ($cnpj !== '' && strlen($cnpj) !== 14) {
    $errors['cnpj'] = 'CNPJ deve ter 14 dígitos.';
}

if ($newPassword !== '' && strlen($newPassword) < 8) {
    $errors['new_password'] = 'A nova senha precisa ter pelo menos 8 caracteres.';
} elseif ($newPassword !== '' && $newPassword !== $newPassword2) {
    $errors['new_password'] = 'As senhas não coincidem.';
}

// --- Endereço: só valida/salva se a pessoa preencheu pelo menos o essencial ---
$logradouro  = trim($_POST['logradouro'] ?? '');
$numEndereco = trim($_POST['num_endereco'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');
$nomeBairro  = trim($_POST['nome_bairro'] ?? '');
$nomeCidade  = trim($_POST['nome_cidade'] ?? '');
$siglaEstado = trim($_POST['sigla_estado'] ?? '');
$cep         = preg_replace('/\D/', '', $_POST['cep'] ?? '');

$enderecoPreenchidoParcialmente = $logradouro !== '' || $nomeCidade !== '' || $nomeBairro !== '' || $siglaEstado !== '';
$enderecoCompleto = $logradouro !== '' && $nomeCidade !== '' && $nomeBairro !== '' && $siglaEstado !== '';

if ($enderecoPreenchidoParcialmente && !$enderecoCompleto) {
    $errors['endereco'] = 'Para salvar o endereço, preencha ao menos logradouro, bairro, cidade e estado.';
}

$estado = null;
if ($enderecoCompleto) {
    $db     = getDb();
    $estado = findEstadoBySigla($db, $siglaEstado);
    if (!$estado) {
        $errors['endereco'] = 'Estado inválido.';
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $old;
    header('Location: perfil.php');
    exit;
}

$db = getDb();

try {
    $db->beginTransaction();

    $params = [
        'id'    => $auth['user']['id'],
        'name'  => $name,
        'email' => $emailUsuario !== '' ? $emailUsuario : null,
        'cpf'   => $cpf !== '' ? $cpf : null,
        'cnpj'  => $cnpj !== '' ? $cnpj : null,
    ];

    if ($newPassword !== '') {
        $params['senha'] = password_hash($newPassword, PASSWORD_DEFAULT);
        $db->prepare(
            'UPDATE Usuario SET nome_usuario = :name, email_usuario = :email,
                cpf_usuario = :cpf, cnpj_usuario = :cnpj, senha = :senha
             WHERE id_usuario = :id'
        )->execute($params);
    } else {
        $db->prepare(
            'UPDATE Usuario SET nome_usuario = :name, email_usuario = :email,
                cpf_usuario = :cpf, cnpj_usuario = :cnpj
             WHERE id_usuario = :id'
        )->execute($params);
    }

    if ($enderecoCompleto && $estado) {
        upsertEnderecoResidencial($db, $auth['user']['id'], [
            'logradouro'   => $logradouro,
            'num_endereco' => $numEndereco !== '' ? $numEndereco : null,
            'complemento'  => $complemento !== '' ? $complemento : null,
            'nome_cidade'  => $nomeCidade,
            'nome_bairro'  => $nomeBairro,
            'id_estado'    => $estado['id_estado'],
            'cep'          => $cep !== '' ? $cep : null,
        ]);
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollBack();
    error_log('Falha ao salvar perfil: ' . $e->getMessage());
    $_SESSION['errors'] = ['name' => 'Não foi possível salvar. Tente novamente.'];
    $_SESSION['old']    = $old;
    header('Location: perfil.php');
    exit;
}

// Mantém o nome exibido no header/dashboard sincronizado nesta sessão.
$_SESSION['user']['name'] = $name;

$_SESSION['success'] = 'Perfil atualizado com sucesso.';
header('Location: perfil.php');
exit;
