<?php
require __DIR__ . '/includes/config.php';

// Simple route guard: bounce guests back to login.
if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

// Direções disponíveis: agora vêm do enum RoofDirection (fonte única de verdade
// para as opções e para a eficiência usada no cálculo do orçamento).
require_once __DIR__ . '/includes/RoofDirection.php';
$directions = RoofDirection::cases();

// Erros de validação e valores antigos, se o handler tiver redirecionado de volta.
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

$knowsRoofSize  = array_key_exists('knows_roof_size', $old) ? (bool) $old['knows_roof_size'] : true;
$knowsDirection = array_key_exists('knows_direction', $old) ? (bool) $old['knows_direction'] : true;

$variant   = 'account';
$pageTitle = 'EcoVolts - Orçamento';
require __DIR__ . '/includes/header.php';

$inputClass = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Simulação
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium">Calcular orçamento</h1>

<div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
    <form action="orcamento-handler.php" method="post" class="flex flex-col gap-0" id="orcamento-form">

        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">

            <div class="col-span-full flex flex-col gap-1.5">
                <label for="bill_amount" class="text-[13px] font-medium text-eco-muted">Conta de energia (R$)</label>
                <input
                    id="bill_amount"
                    name="bill_amount"
                    type="text"
                    inputmode="numeric"
                    required
                    placeholder="Ex: 380,00"
                    value="<?= htmlspecialchars($old['bill_amount'] ?? '') ?>"
                    class="<?= $inputClass ?>"
                >
                <?php if (!empty($errors['bill_amount'])): ?>
                    <p class="text-[13px] text-red-600"><?= htmlspecialchars($errors['bill_amount']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Tamanho do telhado -->
            <div class="col-span-full mt-1 border-t border-eco-line pt-5">
                <span class="mb-2.5 block text-[14.5px] font-medium">Você sabe o tamanho do telhado?</span>

                <input type="hidden" name="knows_roof_size" id="knows_roof_size" value="<?= $knowsRoofSize ? '1' : '0' ?>">

                <button type="button" data-toggle="roof_size" data-value="1"
                    class="toggle-btn mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= $knowsRoofSize ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Sim
                </button>
                <button type="button" data-toggle="roof_size" data-value="0"
                    class="toggle-btn mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= !$knowsRoofSize ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Não
                </button>

                <span class="mt-1.5 block text-[12.5px] text-eco-hint">Se não souber, usamos uma estimativa média.</span>

                <div id="roof_size_field" class="mt-4 max-w-[420px]" style="<?= $knowsRoofSize ? '' : 'display:none' ?>">
                    <label for="roof_size_m2" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Tamanho do telhado (m²)</label>
                    <input
                        id="roof_size_m2"
                        name="roof_size_m2"
                        type="text"
                        placeholder="Ex: 45"
                        value="<?= htmlspecialchars($old['roof_size_m2'] ?? '') ?>"
                        class="<?= $inputClass ?>"
                    >
                    <?php if (!empty($errors['roof_size_m2'])): ?>
                        <p class="mt-1 text-[13px] text-red-600"><?= htmlspecialchars($errors['roof_size_m2']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Direção do telhado -->
            <div class="col-span-full mt-1 border-t border-eco-line pt-5">
                <span class="mb-2.5 block text-[14.5px] font-medium">Você sabe a direção do telhado?</span>

                <input type="hidden" name="knows_direction" id="knows_direction" value="<?= $knowsDirection ? '1' : '0' ?>">

                <button type="button" data-toggle="direction" data-value="1"
                    class="toggle-btn mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= $knowsDirection ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Sim
                </button>
                <button type="button" data-toggle="direction" data-value="0"
                    class="toggle-btn mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= !$knowsDirection ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Não
                </button>

                <span class="mt-1.5 block text-[12.5px] text-eco-hint">A direção muda a eficiência de geração das placas.</span>

                <div id="direction_field" class="mt-4 max-w-[420px]" style="<?= $knowsDirection ? '' : 'display:none' ?>">
                    <label for="roof_direction" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Direção do telhado</label>
                    <select id="roof_direction" name="roof_direction" class="<?= $inputClass ?>">
                        <?php $selectedDirection = $old['roof_direction'] ?? RoofDirection::Norte->value; ?>
                        <?php foreach ($directions as $direction): ?>
                            <option value="<?= htmlspecialchars($direction->value) ?>"
                                <?= $selectedDirection === $direction->value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($direction->label()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['roof_direction'])): ?>
                        <p class="mt-1 text-[13px] text-red-600"><?= htmlspecialchars($errors['roof_direction']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-[26px] flex items-center gap-3.5">
            <button type="submit"
                class="inline-flex w-fit items-center gap-2 rounded-md bg-eco-amber px-[22px] py-3 text-[15px] font-semibold text-eco-ink hover:bg-eco-amber/75">
                Calcular orçamento
            </button>
            <a href="<?= route('dashboard') ?>"
                class="inline-flex w-fit items-center gap-2 rounded-md px-[22px] py-3 text-[15px] font-semibold text-eco-muted hover:text-eco-ink">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Máscara de moeda (equivalente ao formatCurrency do React)
    var billInput = document.getElementById('bill_amount');
    billInput.addEventListener('input', function (e) {
        var digits = e.target.value.replace(/\D/g, '');
        var cents  = parseInt(digits || '0', 10);
        e.target.value = (cents / 100).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    });

    // Toggles Sim/Não (roof size e direction)
    var groups = {
        roof_size: { hidden: 'knows_roof_size', field: 'roof_size_field' },
        direction: { hidden: 'knows_direction', field: 'direction_field' },
    };

    document.querySelectorAll('.toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = btn.getAttribute('data-toggle');
            var value = btn.getAttribute('data-value');
            var cfg   = groups[group];

            document.getElementById(cfg.hidden).value = value;
            document.getElementById(cfg.field).style.display = value === '1' ? '' : 'none';

            document.querySelectorAll('.toggle-btn[data-toggle="' + group + '"]').forEach(function (b) {
                var active = b.getAttribute('data-value') === value;
                b.classList.toggle('border-eco-green', active);
                b.classList.toggle('bg-eco-green', active);
                b.classList.toggle('text-white', active);
                b.classList.toggle('border-eco-line', !active);
                b.classList.toggle('text-eco-muted', !active);
            });
        });
    });
});
</script>