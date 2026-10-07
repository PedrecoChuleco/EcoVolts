<?php
require __DIR__ . '/includes/require-admin.php';
require_once __DIR__ . '/includes/business-lookups.php';

$db = getDb();

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;
$usuario  = null;
$perfilNovo = strtolower(trim($_GET['perfil'] ?? 'cliente'));

if ($editando) {
    $stmt = $db->prepare(
        'SELECT id_usuario, nome_usuario, login, email_usuario, id_perfil, cpf_usuario, cnpj_usuario, ativo, is_system
         FROM Usuario WHERE id_usuario = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch();

    if (!$usuario || (int) $usuario['is_system'] === 1) {
        $_SESSION['flash_error'] = 'Usuário não encontrado ou não editável.';
        header('Location: usuarios.php');
        exit;
    }
}

$ehEu   = $editando && $id === (int) $auth['user']['id'];
$perfis = $db->query('SELECT id_perfil, nome_perfil FROM Perfil ORDER BY id_perfil')->fetchAll();

$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old'] ?? null;
unset($_SESSION['errors'], $_SESSION['old']);

if ($old !== null) {
    $form = [
        'name'          => $old['name'] ?? '',
        'login'         => $old['login'] ?? '',
        'email_usuario' => $old['email_usuario'] ?? '',
        'id_perfil'     => (int) ($old['id_perfil'] ?? 0),
        'cpf'           => $old['cpf'] ?? '',
        'cnpj'          => $old['cnpj'] ?? '',
        'ativo'         => !empty($old['ativo']),
    ];
} elseif ($editando) {
    $form = [
        'name'          => $usuario['nome_usuario'],
        'login'         => $usuario['login'],
        'email_usuario' => $usuario['email_usuario'] ?? '',
        'id_perfil'     => (int) $usuario['id_perfil'],
        'cpf'           => $usuario['cpf_usuario'] ?? '',
        'cnpj'          => $usuario['cnpj_usuario'] ?? '',
        'ativo'         => (int) $usuario['ativo'] === 1,
    ];
} else {
    $perfilInicial = $perfilNovo === 'vendedor' ? 'Vendedor' : 'Cliente';
    $form = [
        'name' => '', 'login' => '', 'email_usuario' => '',
        'id_perfil' => getPerfilId($db, $perfilInicial),
        'cpf' => '', 'cnpj' => '', 'ativo' => true,
    ];
}

$variant   = 'account';
$pageTitle = $editando ? 'EcoVolts - Editar usuário' : 'EcoVolts - Novo usuário';
require __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
$erro = fn (string $campo) => !empty($errors[$campo])
    ? '<p class="text-[13px] text-red-600">' . htmlspecialchars($errors[$campo]) . '</p>'
    : '';
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="check"></i></span> Administração
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium"><?= $editando ? 'Editar usuário' : 'Novo usuário' ?></h1>

<form action="usuario-handler.php" method="post" class="flex flex-col gap-6">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="col-span-full flex flex-col gap-1.5">
                <label for="name" class="text-[13px] font-medium text-eco-muted">Nome completo</label>
                <input id="name" name="name" type="text" required value="<?= htmlspecialchars($form['name']) ?>" class="<?= $inputClass ?>">
                <?= $erro('name') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="login" class="text-[13px] font-medium text-eco-muted">E-mail de login</label>
                <input id="login" name="login" type="email" required value="<?= htmlspecialchars($form['login']) ?>" class="<?= $inputClass ?>">
                <?= $erro('login') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="email_usuario" class="text-[13px] font-medium text-eco-muted">E-mail de contato (opcional)</label>
                <input id="email_usuario" name="email_usuario" type="email" value="<?= htmlspecialchars($form['email_usuario']) ?>" class="<?= $inputClass ?>">
                <?= $erro('email_usuario') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="id_perfil" class="text-[13px] font-medium text-eco-muted">Perfil</label>
                <select id="id_perfil" name="id_perfil" class="<?= $inputClass ?>" <?= $ehEu ? 'disabled' : '' ?>>
                    <?php foreach ($perfis as $p): ?>
                        <option value="<?= (int) $p['id_perfil'] ?>" <?= $form['id_perfil'] === (int) $p['id_perfil'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nome_perfil']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($ehEu): ?>
                    <span class="text-[12px] text-eco-hint">Você não pode alterar o seu próprio perfil.</span>
                <?php endif; ?>
                <?= $erro('id_perfil') ?>
            </div>

            <div class="flex flex-col justify-end gap-1.5 pb-2">
                <label class="inline-flex items-center gap-2.5 text-[14.5px]">
                    <input type="checkbox" name="ativo" value="1" <?= $form['ativo'] ? 'checked' : '' ?> <?= $ehEu ? 'disabled' : '' ?>
                           class="size-4 accent-eco-green">
                    Conta ativa
                </label>
                <?php if ($ehEu): ?>
                    <span class="text-[12px] text-eco-hint">Você não pode desativar a sua própria conta.</span>
                <?php endif; ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="cpf" class="text-[13px] font-medium text-eco-muted">CPF (opcional)</label>
                <input id="cpf" name="cpf" type="text" inputmode="numeric" maxlength="11" placeholder="Somente números"
                       value="<?= htmlspecialchars($form['cpf']) ?>" class="<?= $inputClass ?>">
                <?= $erro('cpf') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="cnpj" class="text-[13px] font-medium text-eco-muted">CNPJ (opcional)</label>
                <input id="cnpj" name="cnpj" type="text" inputmode="numeric" maxlength="14" placeholder="Somente números"
                       value="<?= htmlspecialchars($form['cnpj']) ?>" class="<?= $inputClass ?>">
                <?= $erro('cnpj') ?>
            </div>

            <div class="col-span-full flex flex-col gap-1.5">
                <label for="senha" class="text-[13px] font-medium text-eco-muted">
                    <?= $editando ? 'Nova senha (deixe em branco para manter a atual)' : 'Senha (mínimo 8 caracteres)' ?>
                </label>
                <input id="senha" name="senha" type="password" autocomplete="new-password" <?= $editando ? '' : 'required' ?> class="<?= $inputClass ?>">
                <?= $erro('senha') ?>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3.5">
        <button type="submit"
                class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <?= $editando ? 'Salvar alterações' : 'Criar usuário' ?>
        </button>
        <a href="usuarios.php" class="inline-flex w-fit items-center gap-2 rounded-md px-[22px] py-3 text-[15px] font-semibold text-eco-muted hover:text-eco-ink">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
