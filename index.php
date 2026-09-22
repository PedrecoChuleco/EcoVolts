<?php
$variant   = 'landing';
$pageTitle = 'EcoVolts';
require __DIR__ . '/includes/header.php';
?>

<main>
    <section class="relative flex flex-col items-center justify-between gap-12 bg-eco-surface bg-bottom py-24 md:flex-row md:py-[88px] md:pb-[76px] h-screen inset">
        <div class="absolute inset-0 bg-cover" style="background-image: url('assets/images/bg-01.jpg');"></div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black via-transparent to-transparent"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-b from-transparent to-eco-ink"></div>

        <div class="relative z-10 mt-4 max-w-[600px]">
            <div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted">
                <span class="roof-tick"><i data-lucide="check"></i></span> Energia solar sob medida
            </div>
            <h1 class="mb-1.5 font-outfit text-4xl leading-[1.08] text-gray-200 md:text-[60px]">
                Descubra quantas placas cabem no seu telhado.
            </h1>
            <p class="max-w-[52ch] text-[15px] text-gray-300">
                Informe sua conta de energia e alguns dados do telhado &mdash; o EcoVolts calcula a quantidade
                de placas, o investimento e o tempo de retorno para você.
            </p>

            <div class="mt-[30px] flex items-center gap-3.5">
                <?php if ($auth['user']): ?>
                    <a href="<?= route('dashboard') ?>"
                       class="inline-flex items-center rounded-md bg-eco-ink px-[22px] py-3 text-[15px] font-semibold text-white transition-transform duration-200 hover:scale-105 hover:bg-[#0B1D20]">
                        Ir para o menu
                    </a>
                <?php else: ?>
                    <a href="<?= route('register') ?>"
                       class="inline-flex items-center rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink transition-transform duration-200 hover:scale-105 hover:bg-eco-amber/75">
                        Criar conta
                    </a>
                    <a href="<?= route('login') ?>"
                       class="inline-flex items-center rounded-md bg-eco-line px-[22px] py-3 text-[15px] font-semibold text-eco-ink transition-transform duration-200 hover:scale-105 hover:bg-eco-line/75">
                        Já tenho conta
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 bg-eco-ink gap-4 lg:grid-cols-3 inset py-10">
        <?php
        $stats = [
            ['title' => '20.000+', 'body' => 'Placas instaladas'],
            ['title' => '20MW+',   'body' => 'Potência instalada'],
            ['title' => 'R$10M+',  'body' => 'Economia gerada'],
        ];
        foreach ($stats as $stat): ?>
            <div class="my-4 flex items-center justify-center gap-6 rounded-md border border-eco-amber/10 bg-eco-dark px-auto py-5 transition-transform duration-200 hover:scale-105">
                <div class="text-center">
                    <h3 class="m-0 font-outfit text-6xl font-black uppercase text-eco-amber">
                        <?= htmlspecialchars($stat['title']) ?>
                    </h3>
                    <p class="m-0 mt-1 text-md text-white"><?= htmlspecialchars($stat['body']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="grid h-[33vh] grid-cols-1 bg-eco-ink lg:h-screen lg:grid-cols-2 inset">
        <div class="py-16 lg:py-[33%]">
            <span class="roof-tick"><i data-lucide="check"></i></span>
            <h2 class="mb-2 font-serif text-5xl font-medium text-eco-surface">Orçamento em minutos</h2>
            <p class="text-2xl text-eco-muted">Informe o valor da sua conta de energia e receba a quantidade de placas e o investimento estimado.</p>
        </div>
        <div class="hidden overflow-hidden bg-eco-ink lg:block lg:py-10 lg:pl-[5vw]">
            <img src="assets/images/landing-banner1.jpg" alt="EcoVolts Energia Solar"
                 class="h-full w-full rounded-2xl border-2 border-eco-amber/75 object-cover object-center">
        </div>
    </section>

    <section class="grid h-[33vh] grid-cols-1 bg-eco-ink lg:-my-[15%] lg:h-screen lg:grid-cols-2">
        <div class="order-last m-auto py-16 lg:py-40 inset">
            <span class="roof-tick"><i data-lucide="check"></i></span>
            <h2 class="mb-2 font-serif text-5xl text-eco-surface">Telhado sob medida</h2>
            <p class="text-2xl text-eco-muted">Sabendo o tamanho e a direção do telhado, o cálculo considera a eficiência real de geração.</p>
        </div>
        <div class="hidden overflow-hidden rounded-r-2xl bg-eco-bg lg:block lg:py-[5vw] lg:pl-[10vw] lg:pr-[5vw]">
            <img src="assets/images/landing-banner2.jpg" alt="EcoVolts Energia Solar"
                 class="h-full w-full rounded-2xl border-2 border-eco-ink object-cover object-center">
        </div>
    </section>

    <section class="grid h-[33vh] grid-cols-1 bg-eco-ink lg:h-screen lg:grid-cols-2 inset pb-10">
        <div class="py-16 lg:py-[33%] content-end">
            <span class="roof-tick"><i data-lucide="check"></i></span>
            <h2 class="mb-2 font-serif text-5xl font-medium text-eco-surface">Payback claro</h2>
            <p class="text-2xl text-eco-muted">Veja em quantos meses o investimento se paga com a economia na conta de energia.</p>
        </div>
        <div class="hidden overflow-hidden lg:block lg:py-10 lg:pl-[5vw]">
            <img src="assets/images/landing-banner3.jpg" alt="EcoVolts Energia Solar"
                 class="h-full w-full rounded-2xl border-2 border-eco-amber/75 object-cover object-center">
        </div>
    </section>

    <section class="grid h-screen grid-cols-1 items-center bg-eco-dark lg:grid-cols-2 lg:gap-32 inset" id="contato">
        <div>
            <h1 class="mb-2 font-outfit text-6xl font-bold text-eco-surface">Pronto para começar?</h1>
            <p class="mb-2 text-2xl text-eco-muted">Crie uma conta ou entre para simular um orçamento de energia solar.</p>
            <a href="<?= route($auth['user'] ? 'dashboard' : 'register') ?>"
               class="my-4 flex w-fit flex-row items-center gap-2 rounded-md bg-eco-amber p-3 transition-transform duration-200 hover:scale-105 hover:bg-eco-amber/75">
                <span class="text-xl text-eco-ink">Começar</span>
                <i data-lucide="arrow-right" class="text-eco-ink"></i>
            </a>
        </div>

        <div class="order-first lg:order-last">
            <?php
            $address = 'Av. Dr. Maximiliano Baruto, 500 - Jardim Universitario, Araras - SP, 13607-339';
            $contacts = [
                [
                    'icon'  => 'phone',
                    'title' => 'Entre em contato',
                    'body'  => '(19) 99999-9999',
                    'href'  => 'tel:+5519999999999',
                ],
                [
                    'icon'  => 'mail',
                    'title' => 'Mande um E-Mail',
                    'body'  => 'ecovolts@ecovolts.com.br',
                    'href'  => 'mailto:ecovolts@ecovolts.com.br',
                ],
                [
                    'icon'  => 'map-pin',
                    'title' => 'Sede',
                    'body'  => $address,
                    'href'  => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address),
                ],
            ];
            foreach ($contacts as $contact):
                $isExternal = str_starts_with($contact['href'], 'http');
            ?>
                <a href="<?= htmlspecialchars($contact['href']) ?>"
                   <?= $isExternal ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                   class="my-4 flex items-center gap-4 rounded-md border border-eco-amber/10 bg-eco-ink px-6 py-5 transition-transform duration-200 hover:scale-105">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-md bg-eco-amber">
                        <i data-lucide="<?= htmlspecialchars($contact['icon']) ?>" class="size-6 text-eco-ink"></i>
                    </div>
                    <div>
                        <p class="m-0 text-xs font-bold uppercase tracking-widest text-eco-amber">
                            <?= htmlspecialchars($contact['title']) ?>
                        </p>
                        <p class="m-0 mt-1 text-lg text-white"><?= htmlspecialchars($contact['body']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
