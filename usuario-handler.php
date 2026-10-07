<?php
/**
 * Ações do CRUD de usuários (somente POST, somente administradores, com CSRF):
 *   action=save    -> cria (id=0) ou atualiza (id>0)
 *   action=toggle  -> ativa/desativa
 *   action=delete  -> exclui (só se a pessoa não tiver orçamentos)
 *
 * Regras de proteção:
 *   - um administrador não pode alterar o próprio perfil, desativar ou excluir a si mesmo
 *     (evita ficar sem nenhum administrador);
 *   - contas de sistema (is_system, ex.: "Vendedor Online") nunca são alteradas aqui;
 *   - usuário com orçamentos não pode ser excluído (histórico comercial) — só desativado.
 */
require __DIR__ . '/includes/require-admin.php';
require_once __DIR__ . '/includes/business-lookups.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}

/** Volta para a listagem mantendo busca/filtro/página. */
function voltarParaLista(?string $ok = null, ?string $erro = null): never
{
    if ($ok)   { $_SESSION['flash_success'] = $ok; }
    if ($erro) { $_SESSION['flash_error']   = $erro; }

    parse_str($_POST['back'] ?? '', $back);
    $query = http_build_query(array_filter([
        'q'      => is_string($back['q'] ?? null) ? $back['q'] : null,
        'perfil' => (int) ($back['perfil'] ?? 0) ?: null,
        'page'   => (int) ($back['page'] ?? 0) ?: null,
    ]));

    header('Location: usuarios.php' . ($query ? '?' . $query : ''));
    exit;
}

if (!verifyCsrf()) {
    voltarParaLista(null, 'Sessão expirada ou requisição inválida. Tente novamente.');
}

$db     = getDb();
$meuId  = (int) $auth['user']['id'];
$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['id'] ?? 0);

/** Carrega o usuário-alvo ou volta com erro (inexistente / conta de sistema). */
function carregarAlvo(PDO $db, int $id): array
{
    $stmt = $db->prepare('SELECT id_usuario, nome_usuario, id_perfil, ativo, is_system FROM Usuario WHERE id_usuario = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $alvo = $stmt->fetch();

    if (!$alvo || (int) $alvo['is_system'] === 1) {
        voltarParaLista(null, 'Usuário não encontrado ou não pode ser alterado.');
    }

    return $alvo;
}

switch ($action) {
    case 'toggle':
        $alvo = carregarAlvo($db, $id);
        if ($id === $meuId) {
            voltarParaLista(null, 'Você não pode desativar a sua própria conta.');
        }
        $novo = (int) $alvo['ativo'] === 1 ? 0 : 1;
        $db->prepare('UPDATE Usuario SET ativo = :ativo WHERE id_usuario = :id')->execute(['ativo' => $novo, 'id' => $id]);
        voltarParaLista(sprintf('%s foi %s.', $alvo['nome_usuario'], $novo ? 'ativado' : 'desativado'));

    case 'delete':
        $alvo = carregarAlvo($db, $id);
        if ($id === $meuId) {
            voltarParaLista(null, 'Você não pode excluir a sua própria conta.');
        }

        $stmt = $db->prepare('SELECT COUNT(*) FROM Orcamento WHERE id_usuario_cliente = :id OR id_usuario_vendedor = :id2');
        $stmt->execute(['id' => $id, 'id2' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            voltarParaLista(null, $alvo['nome_usuario'] . ' possui orçamentos e não pode ser excluído. Desative a conta em vez disso.');
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare('SELECT id_endereco FROM usuario_endereco WHERE id_usuario = :id');
            $stmt->execute(['id' => $id]);
            $enderecos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // usuario_endereco é removido em cascata junto com o usuário.
            $db->prepare('DELETE FROM Usuario WHERE id_usuario = :id')->execute(['id' => $id]);

            // Limpa endereços que ficaram sem nenhum usuário vinculado.
            $limpa = $db->prepare(
                'DELETE FROM Endereco WHERE id_endereco = :eid
                 AND NOT EXISTS (SELECT 1 FROM usuario_endereco WHERE id_endereco = :eid2)'
            );
            foreach ($enderecos as $eid) {
                $limpa->execute(['eid' => $eid, 'eid2' => $eid]);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            error_log('Falha ao excluir usuário: ' . $e->getMessage());
            voltarParaLista(null, 'Não foi possível excluir o usuário.');
        }
        voltarParaLista($alvo['nome_usuario'] . ' foi excluído.');

    case 'save':
        break;

    default:
        voltarParaLista(null, 'Ação inválida.');
}

// ---------------------------------------------------------------- action=save
$old     = $_POST;
$errors  = [];
$editando = $id > 0;
$alvo    = $editando ? carregarAlvo($db, $id) : null;
$ehEu    = $editando && $id === $meuId;

$name     = trim($_POST['name'] ?? '');
$login    = trim($_POST['login'] ?? '');
$contato  = trim($_POST['email_usuario'] ?? '');
$idPerfil = (int) ($_POST['id_perfil'] ?? 0);
$cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
$cnpj     = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
$ativo    = isset($_POST['ativo']) ? 1 : 0;
$senha    = $_POST['senha'] ?? '';

if ($ehEu) {
    // Proteção contra auto-bloqueio: ignora o que veio do formulário.
    $idPerfil = (int) $alvo['id_perfil'];
    $ativo    = 1;
}

if ($name === '') {
    $errors['name'] = 'Informe o nome.';
}

if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
    $errors['login'] = 'Informe um e-mail de login válido.';
} else {
    $stmt = $db->prepare('SELECT 1 FROM Usuario WHERE login = :login AND id_usuario <> :id LIMIT 1');
    $stmt->execute(['login' => $login, 'id' => $id]);
    if ($stmt->fetchColumn()) {
        $errors['login'] = 'Este e-mail já está em uso por outro usuário.';
    }
}

if ($contato !== '' && !filter_var($contato, FILTER_VALIDATE_EMAIL)) {
    $errors['email_usuario'] = 'E-mail de contato inválido.';
}

$stmt = $db->prepare('SELECT 1 FROM Perfil WHERE id_perfil = :id LIMIT 1');
$stmt->execute(['id' => $idPerfil]);
if (!$stmt->fetchColumn()) {
    $errors['id_perfil'] = 'Selecione um perfil válido.';
}

if ($cpf !== '' && strlen($cpf) !== 11)   { $errors['cpf']  = 'CPF deve ter 11 dígitos.'; }
if ($cnpj !== '' && strlen($cnpj) !== 14) { $errors['cnpj'] = 'CNPJ deve ter 14 dígitos.'; }

if (!$editando && $senha === '') {
    $errors['senha'] = 'Informe uma senha para o novo usuário.';
} elseif ($senha !== '' && strlen($senha) < 8) {
    $errors['senha'] = 'A senha precisa ter pelo menos 8 caracteres.';
}

$voltarAoForm = 'usuario-form.php' . ($editando ? '?id=' . $id : '');

if ($errors) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old']    = $old;
    header('Location: ' . $voltarAoForm);
    exit;
}

try {
    if ($editando) {
        $params = [
            'id' => $id, 'nome' => $name, 'login' => $login,
            'email' => $contato !== '' ? $contato : null,
            'perfil' => $idPerfil, 'cpf' => $cpf !== '' ? $cpf : null,
            'cnpj' => $cnpj !== '' ? $cnpj : null, 'ativo' => $ativo,
        ];
        $setSenha = '';
        if ($senha !== '') {
            $setSenha = ', senha = :senha';
            $params['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        $db->prepare(
            "UPDATE Usuario SET nome_usuario = :nome, login = :login, email_usuario = :email,
                id_perfil = :perfil, cpf_usuario = :cpf, cnpj_usuario = :cnpj, ativo = :ativo $setSenha
             WHERE id_usuario = :id"
        )->execute($params);

        if ($ehEu) {
            $_SESSION['user']['name']  = $name;
            $_SESSION['user']['email'] = $login;
        }
        voltarParaLista('Usuário atualizado com sucesso.');
    }

    $db->prepare(
        'INSERT INTO Usuario (login, senha, id_perfil, email_usuario, nome_usuario, cpf_usuario, cnpj_usuario, ativo, is_system)
         VALUES (:login, :senha, :perfil, :email, :nome, :cpf, :cnpj, :ativo, 0)'
    )->execute([
        'login' => $login, 'senha' => password_hash($senha, PASSWORD_DEFAULT),
        'perfil' => $idPerfil, 'email' => $contato !== '' ? $contato : $login,
        'nome' => $name, 'cpf' => $cpf !== '' ? $cpf : null,
        'cnpj' => $cnpj !== '' ? $cnpj : null, 'ativo' => $ativo,
    ]);
    voltarParaLista('Usuário criado com sucesso.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // corrida: e-mail duplicado entre a checagem e o INSERT/UPDATE
        $_SESSION['errors'] = ['login' => 'Este e-mail já está em uso por outro usuário.'];
    } else {
        error_log('Falha ao salvar usuário: ' . $e->getMessage());
        $_SESSION['errors'] = ['name' => 'Não foi possível salvar. Tente novamente.'];
    }
    $_SESSION['old'] = $old;
    header('Location: ' . $voltarAoForm);
    exit;
}
