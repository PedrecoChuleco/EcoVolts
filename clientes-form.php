<?php
/**
 * Cadastro (sem ?id) e edição (?id=N) de clientes pela equipe comercial.
 *   cadastrar -> permissão `cadastrar_clientes`
 *   editar    -> permissão `editar_clientes`
 * O nome "clientes-form.php" contém "clientes", então o item "Clientes" do
 * menu lateral fica destacado aqui sem precisar mexer no sidebar.
 */
$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;

$requiredPermission = $editando ? 'editar_clientes' : 'cadastrar_clientes';
require __DIR__ . '/includes/require-permission.php';
require_once __DIR__ . '/includes/auth-helpers.php';
require_once __DIR__ . '/includes/address-helpers.php';

$db       = getDb();
$cliente  = null;
$endereco = null;

if ($editando) {
    $stmt = $db->prepare(
        "SELECT u.id_usuario, u.nome_usuario, u.login, u.email_usuario, u.cpf_usuario, u.cnpj_usuario, u.ativo
         FROM Usuario u JOIN Perfil p ON p.id_perfil = u.id_perfil
         WHERE u.id_usuario = :id AND p.nome_perfil = 'Cliente' AND u.is_system = 0
         LIMIT 1"
    );
    $stmt->execute(['id' => $id]);
    $cliente = $stmt->fetch();

    if (!$cliente) {
        $_SESSION['flash_error'] = 'Cliente não encontrado.';
        header('Location: ' . route('clientes'));
        exit;
    }
    $endereco = getEnderecoResidencial($db, $id);
}

$estados = listEstados($db);
$errors  = $_SESSION['errors'] ?? [];
$old     = $_SESSION['old'] ?? null;
unset($_SESSION['errors'], $_SESSION['old']);

// Reexibe o que a pessoa digitou se houve erro; senão, o que está salvo.
$form = $old ?? [
    'name'          => $cliente['nome_usuario'] ?? '',
    'email'         => '',
    'email_usuario' => $cliente['email_usuario'] ?? '',
    'cpf'           => $cliente['cpf_usuario'] ?? '',
    'cnpj'          => $cliente['cnpj_usuario'] ?? '',
    'logradouro'    => $endereco['logradouro'] ?? '',
    'num_endereco'  => $endereco['num_endereco'] ?? '',
    'complemento'   => $endereco['complemento'] ?? '',
    'nome_bairro'   => $endereco['nome_bairro'] ?? '',
    'nome_cidade'   => $endereco['nome_cidade'] ?? '',
    'sigla_estado'  => $endereco['sigla_estado'] ?? '',
    'cep'           => $endereco['cep'] ?? '',
];

$variant   = 'account';
$pageTitle = $editando ? 'EcoVolts - Editar cliente' : 'EcoVolts - Cadastrar cliente';
require __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
$erro = fn (string $campo) => !empty($errors[$campo])
    ? '<p class="text-[13px] text-red-600">' . htmlspecialchars($errors[$campo]) . '</p>'
    : '';
$v = fn (string $campo) => htmlspecialchars((string) ($form[$campo] ?? ''));
?>

<div class="mb-2.5 mt-10 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
    <span class="roof-tick"><i data-lucide="users-round"></i></span> Área comercial
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium"><?= $editando ? 'Editar cliente' : 'Cadastrar cliente' ?></h1>

<form action="clientes-handler.php" method="post" class="flex flex-col gap-6">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <h2 class="mb-5 font-serif text-xl">Dados do cliente</h2>

        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="col-span-full flex flex-col gap-1.5">
                <label for="name" class="text-[13px] font-medium text-eco-muted">Nome completo</label>
                <input id="name" name="name" type="text" required value="<?= $v('name') ?>" class="<?= $inputClass ?>">
                <?= $erro('name') ?>
            </div>

            <?php if ($editando): ?>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[13px] font-medium text-eco-muted">E-mail de login</label>
                    <input type="email" value="<?= htmlspecialchars($cliente['login']) ?>" disabled class="<?= $inputClass ?> cursor-not-allowed opacity-70">
                    <span class="text-[12px] text-eco-hint">O login do cliente só pode ser alterado por um administrador.</span>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="email_usuario" class="text-[13px] font-medium text-eco-muted">E-mail de contato</label>
                    <input id="email_usuario" name="email_usuario" type="email" value="<?= $v('email_usuario') ?>" class="<?= $inputClass ?>">
                    <?= $erro('email_usuario') ?>
                </div>
            <?php else: ?>
                <div class="col-span-full flex flex-col gap-1.5">
                    <label for="email" class="text-[13px] font-medium text-eco-muted">E-mail</label>
                    <input id="email" name="email" type="email" required value="<?= $v('email') ?>" class="<?= $inputClass ?>">
                    <?= $erro('email') ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col gap-1.5">
                <label for="cpf" class="text-[13px] font-medium text-eco-muted">CPF (opcional)</label>
                <input id="cpf" name="cpf" type="text" inputmode="numeric" maxlength="14" placeholder="Somente números" value="<?= $v('cpf') ?>" class="<?= $inputClass ?>">
                <?= $erro('cpf') ?>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="cnpj" class="text-[13px] font-medium text-eco-muted">CNPJ (opcional, pessoa jurídica)</label>
                <input id="cnpj" name="cnpj" type="text" inputmode="numeric" maxlength="18" placeholder="Somente números" value="<?= $v('cnpj') ?>" class="<?= $inputClass ?>">
                <?= $erro('cnpj') ?>
            </div>
        </div>
    </div>

    <div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
        <h2 class="mb-5 font-serif text-xl">Endereço residencial <span class="text-[14px] font-normal text-eco-muted">(opcional)</span></h2>

        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">
            <div class="col-span-full flex flex-col gap-1.5">
                <label for="logradouro" class="text-[13px] font-medium text-eco-muted">Logradouro</label>
                <input id="logradouro" name="logradouro" type="text" value="<?= $v('logradouro') ?>" class="<?= $inputClass ?>">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="num_endereco" class="text-[13px] font-medium text-eco-muted">Número</label>
                <input id="num_endereco" name="num_endereco" type="text" value="<?= $v('num_endereco') ?>" class="<?= $inputClass ?>">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="complemento" class="text-[13px] font-medium text-eco-muted">Complemento</label>
                <input id="complemento" name="complemento" type="text" value="<?= $v('complemento') ?>" class="<?= $inputClass ?>">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="nome_bairro" class="text-[13px] font-medium text-eco-muted">Bairro</label>
                <input id="nome_bairro" name="nome_bairro" type="text" value="<?= $v('nome_bairro') ?>" class="<?= $inputClass ?>">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="nome_cidade" class="text-[13px] font-medium text-eco-muted">Cidade</label>
                <input id="nome_cidade" name="nome_cidade" type="text" value="<?= $v('nome_cidade') ?>" class="<?= $inputClass ?>">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="sigla_estado" class="text-[13px] font-medium text-eco-muted">Estado</label>
                <select id="sigla_estado" name="sigla_estado" class="<?= $inputClass ?>">
                    <option value="">Selecione…</option>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?= htmlspecialchars($estado['sigla_estado']) ?>" <?= ($form['sigla_estado'] ?? '') === $estado['sigla_estado'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($estado['nome_estado']) ?> (<?= htmlspecialchars($estado['sigla_estado']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="cep" class="text-[13px] font-medium text-eco-muted">CEP</label>
                <input id="cep" name="cep" type="text" inputmode="numeric" maxlength="9" placeholder="Somente números" value="<?= $v('cep') ?>" class="<?= $inputClass ?>">
            </div>
        </div>
        <?= $erro('endereco') ?>
    </div>

    <?php if (!$editando): ?>
        <p class="rounded-md bg-eco-surface px-4 py-3 text-[13.5px] text-eco-hint">
            O cliente é cadastrado <strong>sem acesso ao portal</strong> (a senha é gerada aleatoriamente e ninguém a conhece).
            Para liberar um acesso, um administrador define a senha em <em>Usuários</em>.
        </p>
    <?php endif; ?>

    <div class="flex items-center gap-3.5">
        <button type="submit"
                class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
            <?= $editando ? 'Salvar alterações' : 'Cadastrar cliente' ?>
        </button>
        <a href="<?= route('clientes') ?>" class="inline-flex w-fit items-center gap-2 rounded-md px-[22px] py-3 text-[15px] font-semibold text-eco-muted hover:text-eco-ink">Cancelar</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
