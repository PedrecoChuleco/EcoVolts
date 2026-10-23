<?php
/**
 * cadastro.php — Aula 09 (SIF072): do formulário ao PHP seguro.
 *
 * Tela STANDALONE do PI EcoVolts: cadastro de usuário. Sem banco de dados.
 *
 * Fluxo: formulário (POST) -> $_POST no servidor -> valida (vazio, formato,
 * faixa) -> sanitiza na saída -> "inicia a sessão" e redireciona ao dashboard.php.
 *
 * Onde ficam os dados? Só na memória do navegador: um cookie de sessão (sem
 * data de expiração, some quando o navegador fecha) com nome e e-mail.
 * A senha NÃO é guardada em lugar nenhum: é validada e descartada.
 *
 * Arquivos: cadastro.php + dashboard.php, na mesma pasta do htdocs.
 */

// DECISÃO: nunca mostrar erro/caminho/stack trace ao usuário (item PROIBIDO).
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

/**
 * FUNÇÃO 1 — escapa texto para sair no HTML (defesa contra XSS).
 * DECISÃO: ENT_QUOTES protege também atributos value="..." com aspas.
 */
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * FUNÇÃO 2 — lê um campo de $_POST com segurança.
 * DECISÃO: "?? ''" evita warning de índice inexistente; is_string() evita que
 * name[]=x (array) cause TypeError no trim().
 */
function campoPost(string $campo): string
{
    $valor = $_POST[$campo] ?? '';
    return is_string($valor) ? $valor : '';
}

/**
 * FUNÇÃO 3 — valida os dados no SERVIDOR (o navegador não é confiável).
 * Retorna [campo => mensagem]; array vazio = tudo válido.
 */
function validarCadastro(string $nome, string $email, string $senha, string $confirma): array
{
    $erros = [];

    // Nome: vazio -> faixa de tamanho -> formato (letras, espaço, ' . -)
    if ($nome === '') {
        $erros['name'] = 'Informe o nome.';
    } elseif (mb_strlen($nome) < 3 || mb_strlen($nome) > 100) {
        $erros['name'] = 'O nome deve ter entre 3 e 100 caracteres.';
    } elseif (!preg_match("/^[\p{L}\p{M}\s'.-]+$/u", $nome)) {
        $erros['name'] = 'O nome só pode ter letras, espaços, apóstrofo, ponto e hífen.';
    }

    // E-mail: vazio -> faixa (máx. 254) -> formato
    if ($email === '') {
        $erros['email'] = 'Informe o e-mail.';
    } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'E-mail inválido.';
    }

    // Senha: vazio -> faixa. Mín. 8; máx. 72 bytes (limite do bcrypt do password_hash).
    if ($senha === '') {
        $erros['password'] = 'Informe a senha.';
    } elseif (strlen($senha) < 8) {
        $erros['password'] = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif (strlen($senha) > 72) {
        $erros['password'] = 'A senha pode ter no máximo 72 caracteres.';
    }

    // Confirmação: só compara se a senha em si foi aceita.
    if (!isset($erros['password']) && $senha !== $confirma) {
        $erros['password_confirm'] = 'As senhas não conferem.';
    }

    return $erros;
}

// ---------------------------------------------------------------------------
// Processamento: só roda quando o formulário foi ENVIADO (POST). Abrir a
// página no navegador é GET e apenas mostra o formulário.
// ---------------------------------------------------------------------------
$nome  = '';
$email = '';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // DECISÃO: trim() em nome/e-mail, mas NUNCA na senha (espaços podem fazer
    // parte dela). Só $_POST — jamais $_REQUEST.
    $nome     = trim(campoPost('name'));
    $email    = trim(campoPost('email'));
    $senha    = campoPost('password');
    $confirma = campoPost('password_confirm');

    $erros = validarCadastro($nome, $email, $senha, $confirma);

    if ($erros === []) {
        // DECISÃO: cookie de SESSÃO (expires = 0): vive só na memória do
        // navegador e some ao fechá-lo. httponly = o JavaScript não lê;
        // samesite = não é enviado em requisições vindas de outros sites.
        // Guarda só nome e e-mail; a senha é descartada aqui.
        setcookie(
            'ecovolts_user',
            json_encode(['nome' => $nome, 'email' => $email], JSON_UNESCAPED_UNICODE),
            [
                'expires'  => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => !empty($_SERVER['HTTPS']),
            ]
        );

        // DECISÃO: redireciona (padrão POST/Redirect/GET): atualizar a página
        // do dashboard não reenvia o formulário.
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoVolts - Criar conta</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="card">
  <div class="brand"><b>Eco</b><b>Volts</b></div>
  <h1>Criar uma conta</h1>
  <p class="sub">Comece agora</p>

  <!-- method="POST": a senha não pode aparecer na URL. action fixo (PHP_SELF seria refletido na página).
       novalidate: o navegador não bloqueia o envio, para a validação do SERVIDOR ser a que vale. -->
  <form action="cadastro.php" method="post" novalidate>

    <div class="campo">
      <label for="name">Nome</label>
      <input id="name" name="name" type="text" maxlength="100" autocomplete="name"
             value="<?= e($nome) ?>" class="<?= isset($erros['name']) ? 'invalido' : '' ?>">
      <?php if (isset($erros['name'])): ?><p class="erro" role="alert"><?= e($erros['name']) ?></p><?php endif; ?>
    </div>

    <div class="campo">
      <label for="email">E-mail</label>
      <input id="email" name="email" type="email" maxlength="254" autocomplete="email"
             value="<?= e($email) ?>" class="<?= isset($erros['email']) ? 'invalido' : '' ?>">
      <?php if (isset($erros['email'])): ?><p class="erro" role="alert"><?= e($erros['email']) ?></p><?php endif; ?>
    </div>

    <div class="campo">
      <label for="password">Senha</label>
      <!-- DECISÃO: a senha nunca é devolvida em value= depois de um erro. -->
      <input id="password" name="password" type="password" maxlength="72" autocomplete="new-password"
             class="<?= isset($erros['password']) ? 'invalido' : '' ?>">
      <?php if (isset($erros['password'])): ?><p class="erro" role="alert"><?= e($erros['password']) ?></p><?php endif; ?>
    </div>

    <div class="campo">
      <label for="password_confirm">Confirmar senha</label>
      <input id="password_confirm" name="password_confirm" type="password" maxlength="72" autocomplete="new-password"
             class="<?= isset($erros['password_confirm']) ? 'invalido' : '' ?>">
      <?php if (isset($erros['password_confirm'])): ?><p class="erro" role="alert"><?= e($erros['password_confirm']) ?></p><?php endif; ?>
    </div>

    <button type="submit">Criar conta</button>

    <p class="rodape">Já tem conta? <a href="#" onclick="return false;">Entrar</a></p>
  </form>
</div>

</body>
</html>
