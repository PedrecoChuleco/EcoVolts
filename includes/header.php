<?php
/**
 * Abertura do layout. Inclua no TOPO de toda página, depois de definir:
 *
 *   $variant    = 'landing' | 'guest' | 'account';
 *   $pageTitle  = 'EcoVolts - Algo';   (opcional)
 *   $wide       = true;                (opcional, só 'account': conteúdo mais largo)
 *
 * Este arquivo só COMPÕE as peças; cada uma cuida do próprio estilo:
 *
 *   layout/head.php      <head> global (fontes, Tailwind + paleta, CSS, Lucide)
 *   layout/topbar.php    barra FIXA do topo     -> 'landing' e 'guest'
 *   layout/sidebar.php   menu lateral com logo  -> 'account'
 *   layout/brand.php     logo reutilizável
 *
 * includes/footer.php fecha o que este arquivo abre.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth-helpers.php';

$variant   = $variant ?? 'landing';
$pageTitle = $pageTitle ?? 'EcoVolts';
$wide      = $wide ?? true;

require __DIR__ . '/layout/head.php';
?>

<?php if ($variant === 'account'): ?>
<div class="grid min-h-screen grid-cols-1 md:grid-cols-[248px_1fr]">
    <?php require __DIR__ . '/layout/sidebar.php'; ?>
    <main class="<?= $wide
        ? 'max-w-[1180px] px-[22px] py-[34px] pb-[60px] md:px-10 md:py-12 md:pb-20'
        : 'max-w-[760px] px-[22px] py-[34px] pb-[60px] md:px-14 md:py-12 md:pb-20' ?>">

<?php elseif ($variant === 'guest'): ?>
<?php require __DIR__ . '/layout/topbar.php'; ?>
<div class="grid min-h-[calc(100vh-65px)] grid-cols-1">
    <div class="relative min-h-screen">
        <!-- Imagem de fundo de tela cheia (só em telas grandes), atrás do cartão de login/cadastro -->
        <div class="hidden h-screen overflow-hidden bg-eco-dark -z-10 inset-0 lg:fixed lg:block">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black via-transparent to-transparent"></div>
            <img src="assets/images/auth-banner.jpg" alt="EcoVolts Energia Solar"
                 class="h-full w-full object-cover object-center">
        </div>
        <main class="relative z-10 px-6 pb-12 pt-24 md:px-14 lg:px-16">

<?php else: /* landing */ ?>
<?php require __DIR__ . '/layout/topbar.php'; ?>
<div class="grid min-h-[calc(100vh-65px)] grid-cols-1">
    <main class="max-w-none p-0">
<?php endif; ?>
