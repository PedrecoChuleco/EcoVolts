<?php
$variant   = 'guest';
$pageTitle = 'EcoVolts - Login';
require __DIR__ . '/includes/header.php';
?>
<div class="mx-auto lg:w-[50vw] px-12 pt-2 pb-12 bg-eco-surface rounded-[2%]">
    <div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
        <span class="roof-tick"><i data-lucide="check"></i></span> Bem-vindo de volta
    </div>
    <h1 class="mb-1.5 font-serif text-[34px] font-medium">Entrar na sua conta</h1>

    <?php if (!empty($_GET['error'])): ?>
        <p class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
            <?= htmlspecialchars($_GET['error']) ?>
        </p>
    <?php endif; ?>

    <form action="auth/login-handler.php" method="post" class="mt-6 flex flex-col gap-5">
        <div class="flex flex-col gap-1.5">
            <label for="email" class="text-[13px] font-medium text-eco-muted">E-mail</label>
            <input id="email" name="email" type="email" required
                class="rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green">
        </div>

        <div class="flex flex-col gap-1.5">
            <label for="password" class="text-[13px] font-medium text-eco-muted">Senha</label>
            <input id="password" name="password" type="password" required
                class="rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green">
        </div>

        <button type="submit"
            class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-ink px-[22px] py-3 text-[15px] font-semibold text-white hover:bg-[#0B1D20]">
            Entrar
        </button>

        <p class="text-sm text-eco-muted">
            Ainda não tem conta?
            <a href="<?= route('register') ?>" class="font-semibold text-eco-green hover:underline">Criar conta</a>
        </p>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>