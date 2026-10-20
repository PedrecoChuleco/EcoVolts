<?php
/**
 * Abre o documento: <!DOCTYPE>, <head> e <body>.
 *
 * Aqui entra SÓ o que é global ao site todo: fontes, Tailwind + paleta de
 * cores, o CSS do projeto e os ícones Lucide (usados em quase todas as telas).
 * Qualquer coisa específica de uma parte do site (ex.: ícones de marcas do
 * rodapé) deve ficar junto do arquivo que a usa, não aqui.
 *
 * Espera: $pageTitle
 */
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

<!-- Tailwind via CDN: sem etapa de build. A paleta do site é definida aqui, em um só lugar. -->
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
</head>
<body class="min-h-screen bg-eco-bg font-sans text-eco-ink">
