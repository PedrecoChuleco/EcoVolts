<?php

use App\Enums\RoofDirection;
use App\Support\SolarBudgetCalculator;

test('it estimates fourteen north-facing panels for a 380 reais bill', function () {
    $report = (new SolarBudgetCalculator)->calculate(380.0, true, 45.0, true, RoofDirection::Norte);

    expect($report)
        ->panel_count->toBe(14)
        ->investment->toBe(25200.0)
        ->bill_after->toBe(102.6)
        ->current_bill->toBe(380.0)
        ->payback_months->toBe(91)
        ->roof_fits->toBeTrue()
        ->roof_direction->toBe('Norte');
});

test('it marks the roof as too small when the area cannot fit the panels', function () {
    $report = (new SolarBudgetCalculator)->calculate(380.0, true, 10.0, true, RoofDirection::Norte);

    expect($report['roof_fits'])->toBeFalse();
});

test('it uses the default roof size and north efficiency when details are unknown', function () {
    $report = (new SolarBudgetCalculator)->calculate(380.0, false, null, false, null);

    expect($report)
        ->roof_size_m2->toBe(45.0)
        ->roof_direction->toBe('Norte')
        ->panel_count->toBe(14);
});
