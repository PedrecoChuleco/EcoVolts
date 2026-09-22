<?php
$variant   = 'guest';
$pageTitle = 'EcoVolts - Criar conta';
require __DIR__ . '/includes/header.php';
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Comece agora
</div>
<h1 class="mb-1.5 font-serif text-[34px] font-medium">Criar uma conta</h1>

<form action="auth/register-handler.php" method="post" class="mt-6 flex flex-col gap-5">
    <div class="flex flex-col gap-1.5">
        <label for="name" class="text-[13px] font-medium text-eco-muted">Nome</label>
        <input id="name" name="name" type="text" required
               class="rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green">
    </div>

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
            class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
        Criar conta
    </button>

    <p class="text-sm text-eco-muted">
        Já tem conta?
        <a href="<?= route('login') ?>" class="font-semibold text-eco-green hover:underline">Entrar</a>
    </p>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
