<?php
/**
 * Direção do telhado e eficiência de geração associada.
 *
 * Espelha App\Enums\RoofDirection (versão Laravel), mas sem namespace/autoload,
 * para ser usado direto via require nas páginas em PHP puro.
 */

enum RoofDirection: string
{
    case Norte = 'Norte';
    case Leste = 'Leste';
    case Oeste = 'Oeste';
    case Sul   = 'Sul';

    public function efficiency(): float
    {
        return match ($this) {
            self::Norte => 1.0,
            self::Leste, self::Oeste => 0.85,
            self::Sul => 0.70,
        };
    }

    /** Rótulo amigável para exibir na UI, com o ganho relativo. */
    public function label(): string
    {
        return match ($this) {
            self::Norte => 'Norte',
            self::Leste => 'Leste',
            self::Oeste => 'Oeste',
            self::Sul   => 'Sul',
        };
    }
}
