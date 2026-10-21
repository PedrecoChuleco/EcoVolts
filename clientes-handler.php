<?php
/**
 * Grava o cadastro (id=0) ou a edição (id>0) de um cliente pela equipe comercial.
 *
 * Regras:
 *  - cadastrar exige `cadastrar_clientes`; editar exige `editar_clientes`;
 *  - somente POST, com token CSRF;
 *  - só edita quem tem perfil "Cliente" (nunca vendedor, administrador ou conta de sistema);
 *  - só os campos abaixo são aceitos. Login, senha, perfil e status (ativo) NUNCA
 *    são alterados por aqui, mesmo que alguém forje esses campos no POST;
 *  - cliente novo nasce sem acesso ao portal (senha aleatória que ninguém conhece).
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: clientes.php');
    exit;
}

$id       = (int) ($_POST['id'] ?? 0);
$editando = $id > 0;

$requiredPermission = $editando ? 'editar_clientes' : 'cadastrar_clientes';
require __DIR__ . '/includes/require-permission.php';
require_once __DIR__ . '/includes/auth-helpers.php';
require_once __DIR__ . '/includes/address-helpers.php';
require_once __DIR__ . '/includes/business-lookups.php';

function voltarParaClientes(?string $ok = null, ?string $erro = null, string $busca = ''): never
{
    if ($ok)   { $_SESSION['flash_success'] = $ok; }
    if ($erro) { $_SESSION['flash_error']   = $erro; }
    header('Location: clientes.php' . ($busca !== '' ? '?q=' . urlencode($busca) : ''));
    exit;
}

if (!verifyCsrf()) {
    voltarParaClientes(null, 'Sessão expirada ou requisição inválida. Tente novamente.');
}

$db   = getDb();
$alvo = null;

if ($editando) {
    $stmt = $db->prepare(
        "SELECT u.id_usuario, u.login
         FROM Usuario u JOIN Perfil p ON p.id_perfil = u.id_perfil
         WHERE u.id_usuario = :id AND p.nome_perfil = 'Cliente' AND u.is_system = 0
         LIMIT 1"
    );
    $stmt->execute(['id' => $id]);
    $alvo = $stmt->fetch();

    if (!$alvo) {
        voltarParaClientes(null, 'Cliente não encontrado ou não pode ser editado.');
    }
}

$old    = $_POST;
$errors = [];

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');          // só no cadastro (vira login e contato)
$contato = trim($_POST['email_usuario'] ?? '');  // só na edição
$cpf     = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$cnpj    = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');

$logradouro  = trim($_POST['logradouro'] ?? '');
$numEndereco = trim($_POST['num_endereco'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');
$nomeBairro  = trim($_POST['nome_bairro'] ?? '');
$nomeCidade  = trim($_POST['nome_cidade'] ?? '');
$siglaEstado = trim($_POST['sigla_estado'] ?? '');
$cep         = preg_replace('/\D/', '', $_POST['cep'] ?? '');

if ($name === '') {
    $errors['name'] = 'Informe o nome do cliente.';
}

if (!$editando) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Informe um e-mail válido.';
    } else {
        $stmt = $db->prepare('SELECT 1 FROM Usuario WHERE login = :l OR email_usuario = :e LIMIT 1');
        $stmt->execute(['l' => $email, 'e' => $email]);
        if ($stmt->fetchColumn()) {
            $errors['email'] = 'Já existe um cadastro com este e-mail.';
        }
    }
} elseif ($contato !== '' && !filter_var($contato, FILTER_VALIDATE_EMAIL)) {
    $errors['email_usuario'] = 'E-mail de contato inválido.';
}

if ($cpf !== '' && strlen($cpf) !== 11)   { $errors['cpf']  = 'CPF deve ter 11 dígitos.'; }
if ($cnpj !== '' && strlen($cnpj) !== 14) { $errors['cnpj'] = 'CNPJ deve ter 14 dígitos.'; }

// Evita cliente duplicado: mesmo CPF/CNPJ em outro cadastro.
foreach ([['cpf', 'cpf_usuario', $cpf, 'CPF'], ['cnpj', 'cnpj_usuario', $cnpj, 'CNPJ']] as [$campo, $coluna, $valor, $rotulo]) {
    if ($valor !== '' && !isset($errors[$campo])) {
        $stmt = $db->prepare("SELECT nome_usuario FROM Usuario WHERE $coluna = :v AND id_usuario <> :id LIMIT 1");
        $stmt->execute(['v' => $valor, 'id' => $id]);
        if ($outro = $stmt->fetchColumn()) {
            $errors[$campo] = "Este $rotulo já está cadastrado para " . $outro . '.';
        }
    }
}

// Endereço: tudo ou nada.
$parcial  = $logradouro !== '' || $nomeBairro !== '' || $nomeCidade !== '' || $siglaEstado !== '';
$completo = $logradouro !== '' && $nomeBairro !== '' && $nomeCidade !== '' && $siglaEstado !== '';
$estado   = null;

if ($parcial && !$completo) {
    $errors['endereco'] = 'Para salvar o endereço, preencha ao menos logradouro, bairro, cidade e estado.';
} elseif ($completo) {
    $estado = findEstadoBySigla($db, $siglaEstado);
    if (!$estado) {
        $errors['endereco'] = 'Estado inválido.';
    }
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $old;
    header('Location: clientes-form.php' . ($editando ? '?id=' . $id : ''));
    exit;
}

try {
    $db->beginTransaction();

    if ($editando) {
        $db->prepare(
            'UPDATE Usuario SET nome_usuario = :nome, email_usuario = :contato, cpf_usuario = :cpf, cnpj_usuario = :cnpj
             WHERE id_usuario = :id'
        )->execute([
            'nome' => $name, 'contato' => $contato !== '' ? $contato : null,
            'cpf' => $cpf !== '' ? $cpf : null, 'cnpj' => $cnpj !== '' ? $cnpj : null, 'id' => $id,
        ]);
        $idCliente = $id;
        $login     = $alvo['login'];
    } else {
        $db->prepare(
            'INSERT INTO Usuario (login, senha, id_perfil, email_usuario, nome_usuario, cpf_usuario, cnpj_usuario, ativo, is_system)
             VALUES (:login, :senha, :perfil, :email, :nome, :cpf, :cnpj, 1, 0)'
        )->execute([
            'login'  => $email,
            'senha'  => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT), // ninguém conhece
            'perfil' => getPerfilId($db, 'Cliente'),
            'email'  => $email, 'nome' => $name,
            'cpf'    => $cpf !== '' ? $cpf : null, 'cnpj' => $cnpj !== '' ? $cnpj : null,
        ]);
        $idCliente = (int) $db->lastInsertId();
        $login     = $email;
    }

    if ($completo && $estado) {
        upsertEnderecoResidencial($db, $idCliente, [
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
} catch (PDOException $e) {
    $db->rollBack();
    if ($e->getCode() === '23000') { // corrida: e-mail duplicado entre a checagem e o INSERT
        $_SESSION['errors'] = ['email' => 'Já existe um cadastro com este e-mail.'];
    } else {
        error_log('Falha ao salvar cliente: ' . $e->getMessage());
        $_SESSION['errors'] = ['name' => 'Não foi possível salvar. Tente novamente.'];
    }
    $_SESSION['old'] = $old;
    header('Location: clientes-form.php' . ($editando ? '?id=' . $id : ''));
    exit;
}

// Volta para a lista já filtrada por esse cliente: o botão "Criar orçamento" fica a um clique.
voltarParaClientes($editando ? 'Dados do cliente atualizados.' : 'Cliente cadastrado com sucesso.', null, $login);
