<?php
/**
 * Shared layout "opener". Include this at the top of every page after
 * setting the two variables below:
 *
 *   $variant    = 'landing' | 'guest' | 'account';
 *   $pageTitle  = 'EcoVolts - Whatever'; (optional)
 *
 * includes/footer.php must be included at the end of the page to close
 * the tags this file opens.
 */
require_once __DIR__ . '/config.php';

$variant   = $variant ?? 'landing';
$pageTitle = $pageTitle ?? 'EcoVolts';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;900&family=Source+Serif+Pro:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Tailwind via CDN: no build step needed, keeps the raw HTML/CSS/JS constraint -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          'eco-ink':     '#212121',
          'eco-dark':    '#111111',
          'eco-bg':      '#dedede',
          'eco-surface': '#ffffff',
          'eco-line':    '#d8e1db',
          'eco-muted':   '#94A89A',
          'eco-hint':    '#616362',
          'eco-green':   '#404E44',
          'eco-amber':   '#FFDA21',
        },
        fontFamily: {
          outfit: ['Outfit', 'sans-serif'],
          serif: ['"Source Serif Pro"', 'serif'],
        },
      },
    },
  };
</script>

<link rel="stylesheet" href="assets/css/style.css">
<script src="https://unpkg.com/lucide@latest"></script>
 
<!-- Font Awesome brand icons: used for the social row in the footer
     (Instagram/Facebook/YouTube/X). Lucide only ships outline icons,
     not brand logos, so this covers what @icons-pack/react-simple-icons
     did in the React version. -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
      crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="min-h-screen bg-eco-bg font-sans text-eco-ink">

<header id="site-header" class="fixed top-0 left-0 z-50 w-full<?= $variant === 'guest' ? ' lg:w-1/2' : '' ?>">
    <div id="header-bg"
         class="absolute inset-0 bg-eco-dark/25 backdrop-blur-sm transition-opacity duration-150 ease-out"
         style="opacity:0" aria-hidden="true"></div>

    <div class="relative flex flex-wrap items-center justify-between gap-6 px-5 py-4 md:px-10">
        <a href="<?= route('home') ?>" class="flex items-center gap-2.5">
            <img src="assets/images/brand-small.png" class="w-[55px] h-[48px] shrink-1"/> 
            <span class="font-serif text-2xl font-semibold tracking-[0.2px]">
                <span class="text-eco-green">Eco</span><span class="text-eco-amber">Volts</span>
            </span>
        </a>

        <?php if (!$auth['user']): ?>
        <nav class="flex gap-1 overflow-x-auto">
            <a href="<?= route('register') ?>"
               class="flex items-baseline gap-2 whitespace-nowrap rounded-t-md border-b-2 px-3.5 py-2 text-sm <?= isActive('register') ? 'border-eco-amber font-semibold' : 'border-transparent text-eco-muted hover:text-eco-hint' ?>">
                Cadastro
            </a>
            <a href="<?= route('login') ?>"
               class="flex items-baseline gap-2 whitespace-nowrap rounded-t-md border-b-2 px-3.5 py-2 text-sm <?= isActive('login') ? 'border-eco-amber font-semibold' : 'border-transparent text-eco-muted hover:text-eco-hint' ?>">
                Login
            </a>
        </nav>
        <?php endif; ?>
    </div>
</header>

<div class="<?= $variant === 'account'
    ? 'grid min-h-screen grid-cols-1 md:grid-cols-[248px_1fr]'
    : 'grid min-h-[calc(100vh-65px)] grid-cols-1' ?>">

    <?php if ($variant === 'account'): ?>
    <aside class="flex flex-row items-center gap-6 overflow-x-auto bg-eco-ink px-5 text-eco-line md:flex-col md:items-stretch md:gap-8 md:px-5 md:py-6 md:pt-20">
        <nav class="flex flex-row gap-0.5 md:flex-col">
            <p class="mb-2.5 ml-0.5 hidden text-xs text-[#9FB4AE] md:block">Área da conta</p>
            <a href="<?= route('dashboard') ?>" class="flex items-baseline gap-2.5 rounded-md border-l-2 px-2.5 py-2.5 text-[14.5px] <?= isActive('dashboard') ? 'border-eco-amber bg-eco-amber/14 text-white' : 'border-transparent text-[#C7D8D1] hover:bg-white/5 hover:text-white' ?>">Menu</a>
            <a href="<?= route('perfil') ?>" class="flex items-baseline gap-2.5 rounded-md border-l-2 px-2.5 py-2.5 text-[14.5px] <?= isActive('perfil') ? 'border-eco-amber bg-eco-amber/14 text-white' : 'border-transparent text-[#C7D8D1] hover:bg-white/5 hover:text-white' ?>">Perfil</a>
            <a href="<?= route('orcamento') ?>" class="flex items-baseline gap-2.5 rounded-md border-l-2 px-2.5 py-2.5 text-[14.5px] <?= isActive('orcamento') ? 'border-eco-amber bg-eco-amber/14 text-white' : 'border-transparent text-[#C7D8D1] hover:bg-white/5 hover:text-white' ?>">Orçamento</a>
            <a href="<?= route('relatorio') ?>" class="flex items-baseline gap-2.5 rounded-md border-l-2 px-2.5 py-2.5 text-[14.5px] <?= isActive('relatorio') ? 'border-eco-amber bg-eco-amber/14 text-white' : 'border-transparent text-[#C7D8D1] hover:bg-white/5 hover:text-white' ?>">Relatório</a>
        </nav>
    </aside>
    <?php endif; ?>

    <?php if ($variant === 'guest'): ?>
    <div class="grid min-h-screen grid-cols-1 lg:grid-cols-2">
        <main class="flex min-h-screen flex-col justify-center px-6 pt-24 pb-12 md:px-14 lg:px-16">
            <div class="mx-auto w-full max-w-[600px]">
    <?php else: ?>
    <main class="<?= $variant === 'landing' ? 'max-w-none p-0' : 'max-w-[760px] px-[22px] py-[34px] pb-[60px] md:px-14 md:py-12 md:pb-20' ?>">
    <?php endif; ?>
