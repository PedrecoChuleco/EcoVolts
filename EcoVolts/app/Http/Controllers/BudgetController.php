<?php

namespace App\Http\Controllers;

use App\Enums\RoofDirection;
use App\Http\Requests\StoreBudgetRequest;
use App\Support\SolarBudgetCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('orcamento', [
            'directions' => array_column(RoofDirection::cases(), 'value'),
        ]);
    }

    public function store(StoreBudgetRequest $request, SolarBudgetCalculator $calculator): RedirectResponse
    {
        $validated = $request->validated();

        $direction = isset($validated['roof_direction'])
            ? RoofDirection::from($validated['roof_direction'])
            : null;

        $report = $calculator->calculate(
            (float) $validated['bill_amount'],
            (bool) $validated['knows_roof_size'],
            isset($validated['roof_size_m2']) ? (float) $validated['roof_size_m2'] : null,
            (bool) $validated['knows_direction'],
            $direction,
        );

        $user = $request->user();

        $request->session()->put('budget_report', [
            ...$report,
            'name' => $user->name,
            'address' => $user->formattedAddress(),
            'issued_at' => now()->timezone(config('app.timezone'))->format('d/m/Y'),
        ]);

        return to_route('relatorio');
    }

    public function show(Request $request): Response|RedirectResponse
    {
        $report = $request->session()->get('budget_report');

        if (! is_array($report)) {
            return to_route('orcamento');
        }

        return Inertia::render('relatorio', [
            'report' => $report,
        ]);
    }
}
