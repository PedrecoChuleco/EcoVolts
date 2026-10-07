<?php
require __DIR__ . '/includes/config.php';

if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/address-helpers.php';

$db = getDb();

$stmt = $db->prepare(
    'SELECT nome_usuario, login, email_usuario, cpf_usuario, cnpj_usuario
     FROM Usuario WHERE id_usuario = :id LIMIT 1'
);
$stmt->execute(['id' => $auth['user']['id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    // Sessão aponta para um usuário que não existe mais (ex.: removido do banco).
    header('Location: auth/logout.php');
    exit;
}

$endereco = getEnderecoResidencial($db, $auth['user']['id']);
$estados  = listEstados($db);

$errors  = $_SESSION['errors']  ?? [];
$success = $_SESSION['success'] ?? null;
$old     = $_SESSION['old']     ?? null;
unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old']);

// Se o formulário acabou de ser reenviado com erro, usa os valores antigos;
// caso contrário, pré-preenche com o que já está salvo no banco.
$form = $old ?? [
    'name'         => $usuario['nome_usuario'],
    'email_usuario'=> $usuario['email_usuario'],
    'cpf'          => $usuario['cpf_usuario'],
    'cnpj'         => $usuario['cnpj_usuario'],
    'logradouro'   => $endereco['logradouro']  ?? '',
    'num_endereco' => $endereco['num_endereco'] ?? '',
    'complemento'  => $endereco['complemento'] ?? '',
    'nome_bairro'  => $endereco['nome_bairro'] ?? '',
    'nome_cidade'  => $endereco['nome_cidade'] ?? '',
    'sigla_estado' => $endereco['sigla_estado'] ?? '',
    'cep'          => $endereco['cep'] ?? '',
];

$variant   = 'account';
$pageTitle = 'EcoVolts - Perfil';
require __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Área da conta
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium">Meu perfil</h1>

<?php if ($success): ?>
    <p class="mb-5 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form action="perfil-handler.php" method="post" class="flex flex-col gap-8">

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <h2 class="mb-5 font-serif text-xl">Dados pessoais</h2>

        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="col-span-full flex flex-col gap-1.5">
                <label for="name" class="text-[13px] font-medium text-eco-muted">Nome completo</label>
                <input id="name" name="name" type="text" required value="<?= htmlspecialchars($form['name']) ?>" class="<?= $inputClass ?>">
                <?php if (!empty($errors['name'])): ?><p class="text-[13px] text-red-600"><?= htmlspecialchars($errors['name']) ?></p><?php endif; ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label class="text-[13px] font-medium text-eco-muted">E-mail de login</label>
                <input type="email" value="<?= htmlspecialchars($usuario['login']) ?>" disabled
                       class="<?= $inputClass ?> cursor-not-allowed opacity-70">
                <span class="text-[12px] text-eco-hint">Para alterar o e-mail de login, entre em contato com o suporte.</span>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="email_usuario" class="text-[13px] font-medium text-eco-muted">E-mail de contato</label>
                <input id="email_usuario" name="email_usuario" type="email" value="<?= htmlspecialchars($form['email_usuario'] ?? '') ?>" class="<?= $inputClass ?>">
                <?php if (!empty($errors['email_usuario'])): ?><p class="text-[13px] text-red-600"><?= htmlspecialchars($errors['email_usuario']) ?></p><?php endif; ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="cpf" class="text-[13px] font-medium text-eco-muted">CPF (opcional)</label>
                <input id="cpf" name="cpf" type="text" inputmode="numeric" maxlength="11" placeholder="Somente números"
                       value="<?= htmlspecialchars($form['cpf'] ?? '') ?>" class="<?= $inputClass ?>">
                <?php if (!empty($errors['cpf'])): ?><p class="text-[13px] text-red-600"><?= htmlspecialchars($errors['cpf']) ?></p><?php endif; ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="cnpj" class="text-[13px] font-medium text-eco-muted">CNPJ (opcional, para pessoa jurídica)</label>
                <input id="cnpj" name="cnpj" type="text" inputmode="numeric" maxlength="14" placeholder="Somente números"
                       value="<?= htmlspecialchars($form['cnpj'] ?? '') ?>" class="<?= $inputClass ?>">
                <?php if (!empty($errors['cnpj'])): ?><p class="text-[13px] text-red-600"><?= htmlspecialchars($errors['cnpj']) ?></p><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <h2 class="mb-5 font-serif text-xl">Endereço residencial</h2>

        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="col-span-full flex flex-col gap-1.5">
                <label for="logradouro" class="text-[13px] font-medium text-eco-muted">Logradouro</label>
                <input id="logradouro" name="logradouro" type="text" value="<?= htmlspecialchars($form['logradouro']) ?>" class="<?= $inputClass ?>">
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="num_endereco" class="text-[13px] font-medium text-eco-muted">Número</label>
                <input id="num_endereco" name="num_endereco" type="text" value="<?= htmlspecialchars($form['num_endereco']) ?>" class="<?= $inputClass ?>">
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="complemento" class="text-[13px] font-medium text-eco-muted">Complemento</label>
                <input id="complemento" name="complemento" type="text" value="<?= htmlspecialchars($form['complemento']) ?>" class="<?= $inputClass ?>">
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="nome_bairro" class="text-[13px] font-medium text-eco-muted">Bairro</label>
                <input id="nome_bairro" name="nome_bairro" type="text" value="<?= htmlspecialchars($form['nome_bairro']) ?>" class="<?= $inputClass ?>">
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="nome_cidade" class="text-[13px] font-medium text-eco-muted">Cidade</label>
                <input id="nome_cidade" name="nome_cidade" type="text" value="<?= htmlspecialchars($form['nome_cidade']) ?>" class="<?= $inputClass ?>">
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="sigla_estado" class="text-[13px] font-medium text-eco-muted">Estado</label>
                <select id="sigla_estado" name="sigla_estado" class="<?= $inputClass ?>">
                    <option value="">Selecione…</option>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?= htmlspecialchars($estado['sigla_estado']) ?>"
                            <?= ($form['sigla_estado'] ?? '') === $estado['sigla_estado'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($estado['nome_estado']) ?> (<?= htmlspecialchars($estado['sigla_estado']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="cep" class="text-[13px] font-medium text-eco-muted">CEP</label>
                <input id="cep" name="cep" type="text" inputmode="numeric" maxlength="8" placeholder="Somente números"
                       value="<?= htmlspecialchars($form['cep']) ?>" class="<?= $inputClass ?>">
            </div>
        </div>
        <?php if (!empty($errors['endereco'])): ?>
            <p class="mt-3 text-[13px] text-red-600"><?= htmlspecialchars($errors['endereco']) ?></p>
        <?php endif; ?>
    </div>

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <h2 class="mb-1 font-serif text-xl">Alterar senha</h2>
        <p class="mb-5 text-[13px] text-eco-muted">Deixe em branco para manter sua senha atual.</p>

        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="flex flex-col gap-1.5">
                <label for="new_password" class="text-[13px] font-medium text-eco-muted">Nova senha</label>
                <input id="new_password" name="new_password" type="password" class="<?= $inputClass ?>">
                <?php if (!empty($errors['new_password'])): ?><p class="text-[13px] text-red-600"><?= htmlspecialchars($errors['new_password']) ?></p><?php endif; ?>
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="new_password_confirm" class="text-[13px] font-medium text-eco-muted">Confirmar nova senha</label>
                <input id="new_password_confirm" name="new_password_confirm" type="password" class="<?= $inputClass ?>">
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3.5">
        <button type="submit"
            class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            Salvar alterações
        </button>
        <a href="<?= route('dashboard') ?>" class="inline-flex w-fit items-center gap-2 rounded-md px-[22px] py-3 text-[15px] font-semibold text-eco-muted hover:text-eco-ink">
            Cancelar
        </a>
    </div>
</form>