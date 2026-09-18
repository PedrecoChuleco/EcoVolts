<?php
/**
 * Funções utilitárias usadas em todo o projeto.
 */

/** Escapa qualquer dado antes de exibi-lo no HTML (evita XSS). */
function h(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redireciona o navegador para outra página e encerra o script. */
function redirecionar(string $url): void
{
    header("Location: {$url}");
    exit;
}
