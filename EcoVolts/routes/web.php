<?php

use App\Http\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('perfil', function () {
        return Inertia::render('perfil');
    })->name('perfil');

    Route::get('orcamento', [BudgetController::class, 'create'])->name('orcamento');
    Route::post('orcamento', [BudgetController::class, 'store'])->name('orcamento.store');
    Route::get('relatorio', [BudgetController::class, 'show'])->name('relatorio');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
