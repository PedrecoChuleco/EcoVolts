<?php
/**
 * Cria (ou promove) um usuário Administrador.
 * Só roda pela linha de comando — nunca pelo navegador.
 *
 * Uso:
 *   php database/criar-admin.php EMAIL "Nome Completo" "SenhaForte123"
 *
 * - E-mail NOVO:        cria o usuário já como Administrador (nome e senha obrigatórios).
 * - E-mail EXISTENTE:   promove a Administrador e reativa a conta. A senha só é
 *                       trocada se você informar uma (o nome é ignorado).
 *
 * Exemplo no XAMPP (Windows):
 *   C:\xampp\php\php.exe database\criar-admin.php admin@ecovolts.com.br "Administrador" "SenhaForte123"
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Execute este script apenas pela linha de comando.');
}

require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/business-lookups.php';

[, $email, $nome, $senha] = array_pad($argv, 4, null);

if (!$email) {
    fwrite(STDERR, "Uso: php database/criar-admin.php EMAIL \"Nome Completo\" \"Senha\"\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "E-mail inválido: {$email}\n");
    exit(1);
}

if ($senha !== null && strlen($senha) < 8) {
    fwrite(STDERR, "A senha precisa ter pelo menos 8 caracteres.\n");
    exit(1);
}

$db       = getDb();
$idAdmin  = getPerfilId($db, 'Administrador');

$stmt = $db->prepare('SELECT id_usuario, is_system FROM Usuario WHERE login = :login LIMIT 1');
$stmt->execute(['login' => $email]);
$existente = $stmt->fetch();

if ($existente) {
    if ((int) $existente['is_system'] === 1) {
        fwrite(STDERR, "Esse e-mail pertence a uma conta de sistema e não pode virar administrador.\n");
        exit(1);
    }

    $sql    = 'UPDATE Usuario SET id_perfil = :perfil, ativo = 1' . ($senha !== null ? ', senha = :senha' : '') . ' WHERE id_usuario = :id';
    $params = ['perfil' => $idAdmin, 'id' => $existente['id_usuario']];
    if ($senha !== null) {
        $params['senha'] = password_hash($senha, PASSWORD_DEFAULT);
    }
    $db->prepare($sql)->execute($params);

    echo "OK: {$email} agora é Administrador" . ($senha !== null ? ' (senha atualizada).' : '.') . "\n";
    exit(0);
}

if (!$nome || $senha === null) {
    fwrite(STDERR, "Esse e-mail ainda não existe: informe também o nome e a senha.\n");
    exit(1);
}

$db->prepare(
    'INSERT INTO Usuario (login, senha, id_perfil, email_usuario, nome_usuario, ativo, is_system)
     VALUES (:login, :senha, :perfil, :email, :nome, 1, 0)'
)->execute([
    'login'  => $email,
    'senha'  => password_hash($senha, PASSWORD_DEFAULT),
    'perfil' => $idAdmin,
    'email'  => $email,
    'nome'   => $nome,
]);

echo "OK: administrador {$email} criado. Entre pelo login.php.\n";
