<?php
/**
 * Barra FIXA do topo — usada só na landing e nas telas de login/cadastro
 * (variantes 'landing' e 'guest'), onde ela flutua sobre uma imagem.
 * O fundo translúcido aparece aos poucos conforme a página rola
 * (ver assets/js/main.js, que procura o elemento #header-bg).
 *
 * A área logada ('account') NÃO usa esta barra: lá a logo fica no menu lateral
 * e rola junto com a página (ver layout/sidebar.php).
 */
?>
<header id="site-header" class="fixed left-0 top-0 z-50 w-full">
    <div id="header-bg"
         class="absolute inset-0 bg-eco-dark/25 backdrop-blur-sm transition-opacity duration-150 ease-out"
         style="opacity:0" aria-hidden="true"></div>

    <div class="relative flex flex-wrap items-center justify-between gap-6 px-5 py-4 md:px-10">
        <?php require __DIR__ . '/brand.php'; ?>

        <?php if (!$auth['user']): ?>
        <nav class="flex gap-1 overflow-x-auto">
            <?php foreach ([['register', 'Cadastro'], ['login', 'Login']] as [$rota, $texto]): ?>
                <a href="<?= route($rota) ?>"
                   class="flex items-baseline gap-2 whitespace-nowrap rounded-t-md border-b-2 px-3.5 py-2 text-sm <?= isActive($rota) ? 'border-eco-amber font-semibold text-eco-muted' : 'border-transparent text-eco-line hover:text-eco-hint' ?>">
                    <?= $texto ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>
    </div>
</header>
