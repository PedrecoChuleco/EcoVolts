<?php

namespace App\Support;

use App\Enums\RoofDirection;

class SolarBudgetCalculator
{
    public const TARIFF_PER_KWH = 0.85;

    public const KWH_PER_PANEL_NORTH = 34.0;

    public const PANEL_COST = 1800.0;

    public const RESIDUAL_BILL_RATIO = 0.27;

    public const PANEL_AREA_M2 = 2.8;

    public const DEFAULT_ROOF_M2 = 45.0;

    /**
     * @return array{
     *     panel_count: int,
     *     investment: float,
     *     payback_months: int,
     *     current_bill: float,
     *     bill_after: float,
     *     roof_fits: bool,
     *     roof_size_m2: float,
     *     roof_direction: string
     * }
     */
    public function calculate(
        float $billAmount,
        bool $knowsRoofSize,
        ?float $roofSizeM2,
        bool $knowsDirection,
        ?RoofDirection $direction,
    ): array {
        $resolvedDirection = $knowsDirection && $direction instanceof RoofDirection
            ? $direction
            : RoofDirection::Norte;

        $roofSize = $knowsRoofSize && $roofSizeM2 !== null
            ? $roofSizeM2
            : self::DEFAULT_ROOF_M2;

        $consumptionKwh = $billAmount / self::TARIFF_PER_KWH;
        $kwhPerPanel = self::KWH_PER_PANEL_NORTH * $resolvedDirection->efficiency();
        $panelCount = max(1, (int) ceil($consumptionKwh / $kwhPerPanel));
        $investment = $panelCount * self::PANEL_COST;
        $billAfter = round($billAmount * self::RESIDUAL_BILL_RATIO, 2);
        $monthlySavings = max(0.01, $billAmount - $billAfter);
        $paybackMonths = (int) ceil($investment / $monthlySavings);
        $roofFits = $roofSize >= $panelCount * self::PANEL_AREA_M2;

        return [
            'panel_count' => $panelCount,
            'investment' => $investment,
            'payback_months' => $paybackMonths,
            'current_bill' => round($billAmount, 2),
            'bill_after' => $billAfter,
            'roof_fits' => $roofFits,
            'roof_size_m2' => $roofSize,
            'roof_direction' => $resolvedDirection->value,
        ];
    }
}
