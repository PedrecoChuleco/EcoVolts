<?php
/**
 * Padrão de ligação da unidade consumidora e o custo de disponibilidade
 * (valor mínimo da conta de energia, cobrado mesmo que o cliente gere toda a
 * energia que consome).
 *
 *   Padrão       Consumo mínimo   Valor mínimo da conta
 *   Monofásico        30 kWh          R$  23,68
 *   Bifásico          50 kWh          R$  39,46
 *   Trifásico        100 kWh          R$  78,93
 *
 * Quando o cliente não sabe o padrão, o simulador assume Bifásico
 * (PadraoLigacao::PADRAO_QUANDO_NAO_SABE) e o orçamento é marcado com
 * padrao_estimado = 1, o que faz o relatório exibir um aviso.
 *
 * Sem namespace/autoload, para ser usado direto via require (como RoofDirection).
 */

enum PadraoLigacao: string
{
    case Monofasico = 'Monofasico';
    case Bifasico   = 'Bifasico';
    case Trifasico  = 'Trifasico';

    /** Padrão assumido quando o cliente responde "Não sei". */
    public const PADRAO_QUANDO_NAO_SABE = self::Bifasico;

    /** Valor mínimo da conta (R$), o custo de disponibilidade. */
    public function custoMinimo(): float
    {
        return match ($this) {
            self::Monofasico => 23.68,
            self::Bifasico   => 39.46,
            self::Trifasico  => 78.93,
        };
    }

    /** Consumo mínimo faturado (kWh), apenas informativo. */
    public function consumoMinimoKwh(): int
    {
        return match ($this) {
            self::Monofasico => 30,
            self::Bifasico   => 50,
            self::Trifasico  => 100,
        };
    }

    /** Rótulo para a interface. */
    public function label(): string
    {
        return match ($this) {
            self::Monofasico => 'Monofásico',
            self::Bifasico   => 'Bifásico',
            self::Trifasico  => 'Trifásico',
        };
    }
}
