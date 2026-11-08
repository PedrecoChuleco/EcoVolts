<?php
/**
 * Status do orçamento (coluna Orcamento.status).
 *
 *   aberto      Orçamento gerado, ainda sem contato comercial (status inicial)
 *   enviado     Proposta enviada ao cliente
 *   negociacao  Em negociação
 *   aprovado    Cliente aprovou
 *   recusado    Cliente recusou
 *
 * Todo orçamento nasce como "aberto" (StatusOrcamento::PADRAO). A mudança de
 * status é feita em orcamento-detalhe.php por quem tem a permissão
 * `alterar_status_orcamentos` (Vendedor e Administrador).
 *
 * Sem namespace/autoload, para ser usado direto via require.
 */

enum StatusOrcamento: string
{
    case Aberto     = 'aberto';
    case Enviado    = 'enviado';
    case Negociacao = 'negociacao';
    case Aprovado   = 'aprovado';
    case Recusado   = 'recusado';

    /** Status de todo orçamento novo. */
    public const PADRAO = self::Aberto;

    public function label(): string
    {
        return match ($this) {
            self::Aberto     => 'Aberto',
            self::Enviado    => 'Enviado',
            self::Negociacao => 'Em negociação',
            self::Aprovado   => 'Aprovado',
            self::Recusado   => 'Recusado',
        };
    }

    /** Classes Tailwind do selo (badge) na interface. */
    public function classeBadge(): string
    {
        return match ($this) {
            self::Aberto     => 'bg-gray-100 text-gray-700',
            self::Enviado    => 'bg-blue-50 text-blue-700',
            self::Negociacao => 'bg-amber-50 text-amber-800',
            self::Aprovado   => 'bg-green-50 text-green-700',
            self::Recusado   => 'bg-red-50 text-red-700',
        };
    }

    /** Cor (hex) usada nos gráficos. */
    public function corGrafico(): string
    {
        return match ($this) {
            self::Aberto     => '#94A89A',
            self::Enviado    => '#5B8DEF',
            self::Negociacao => '#FFDA21',
            self::Aprovado   => '#5FCE93',
            self::Recusado   => '#b5502f',
        };
    }

    /** HTML do selo de status; aceita o valor vindo do banco (null/inválido vira "Aberto"). */
    public static function selo(?string $valor): string
    {
        $status = self::tryFrom((string) $valor) ?? self::PADRAO;

        return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[12.5px] font-medium '
            . $status->classeBadge() . '">' . htmlspecialchars($status->label()) . '</span>';
    }
}
