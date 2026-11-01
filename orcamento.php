<?php
require __DIR__ . '/includes/config.php';

// Proteção de rota: visitantes voltam para o login.
if (!$auth['user']) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/auth-helpers.php';
require_once __DIR__ . '/includes/business-lookups.php';

$clienteAlvo = null;
if (isset($_GET['cliente'])) {
    $clienteAlvo = userCan('consultar_clientes') && userCan('simular_orcamento')
        ? getClienteParaOrcamento(getDb(), (int) $_GET['cliente'])
        : null;
    if (!$clienteAlvo) {
        header('Location: ' . route('clientes'));
        exit;
    }
}

// Direções e padrões de ligação vêm dos enums (fonte única de verdade para as
// opções e para os valores usados no cálculo do orçamento).
require_once __DIR__ . '/includes/RoofDirection.php';
require_once __DIR__ . '/includes/PadraoLigacao.php';
$direcoes = RoofDirection::cases();
$padroes  = PadraoLigacao::cases();

// Erros de validação e valores antigos, se o handler tiver redirecionado de volta.
$erros  = $_SESSION['erros'] ?? [];
$antigo = $_SESSION['antigo'] ?? [];
unset($_SESSION['erros'], $_SESSION['antigo']);

$sabeTamanho = array_key_exists('sabe_tamanho_telhado', $antigo) ? (bool) $antigo['sabe_tamanho_telhado'] : true;
$sabeDirecao = array_key_exists('sabe_direcao', $antigo) ? (bool) $antigo['sabe_direcao'] : true;
$sabePadrao  = array_key_exists('sabe_padrao', $antigo) ? (bool) $antigo['sabe_padrao'] : true;

$variant   = 'account';
$pageTitle = 'EcoVolts - Orçamento';
require __DIR__ . '/includes/header.php';

$classeInput = 'w-full rounded-md border border-eco-line bg-[#FBFDFC] px-3 py-2.5 font-sans text-[15px] text-eco-ink outline-none focus-visible:border-eco-green focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-eco-green';
?>

<div class="mb-2.5 flex items-center gap-2.5 text-[13.5px] text-eco-muted mt-10">
    <span class="roof-tick"><i data-lucide="check"></i></span> Simulação
</div>
<h1 class="mb-6 font-serif text-[34px] font-medium">Calcular orçamento</h1>

<div class="rounded-[10px] border border-eco-line bg-eco-surface p-[30px_32px]">
    <form action="orcamento-handler.php" method="post" class="flex flex-col gap-0" id="form-orcamento">
        <?php if ($clienteAlvo): ?>
            <p class="mb-4 text-sm">Criando orçamento para <strong><?= htmlspecialchars($clienteAlvo['nome_usuario']) ?></strong></p>
            <input type="hidden" name="cliente_id" value="<?= (int) $clienteAlvo['id_usuario'] ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-x-5 gap-y-[18px] md:grid-cols-2">

            <div class="col-span-full flex flex-col gap-1.5">
                <label for="valor_conta" class="text-[13px] font-medium text-eco-muted">Conta de energia (R$)</label>
                <input
                    id="valor_conta"
                    name="valor_conta"
                    type="text"
                    inputmode="numeric"
                    required
                    placeholder="Ex: 380,00"
                    value="<?= htmlspecialchars($antigo['valor_conta'] ?? '') ?>"
                    class="<?= $classeInput ?>">
                <?php if (!empty($erros['valor_conta'])): ?>
                    <p class="text-[13px] text-red-600"><?= htmlspecialchars($erros['valor_conta']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Padrão de ligação -->
            <div class="col-span-full mt-1 border-t border-eco-line pt-5">
                <span class="mb-2.5 block text-[14.5px] font-medium">Você sabe o padrão de ligação (monofásico, bifásico ou trifásico)?</span>

                <input type="hidden" name="sabe_padrao" id="sabe_padrao" value="<?= $sabePadrao ? '1' : '0' ?>">

                <button type="button" data-grupo="padrao" data-valor="1"
                    class="botao-opcao mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= $sabePadrao ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Sim
                </button>
                <button type="button" data-grupo="padrao" data-valor="0"
                    class="botao-opcao mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= !$sabePadrao ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Não
                </button>

                <span class="mt-1.5 block text-[12.5px] text-eco-hint">Define o valor mínimo da conta. Se não souber, consideramos bifásico.</span>

                <div id="campo_padrao" class="mt-4 max-w-[420px]" style="<?= $sabePadrao ? '' : 'display:none' ?>">
                    <label for="padrao_ligacao" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Padrão de ligação</label>
                    <select id="padrao_ligacao" name="padrao_ligacao" class="<?= $classeInput ?>">
                        <?php $padraoSelecionado = $antigo['padrao_ligacao'] ?? PadraoLigacao::PADRAO_QUANDO_NAO_SABE->value; ?>
                        <?php foreach ($padroes as $padrao): ?>
                            <option value="<?= htmlspecialchars($padrao->value) ?>"
                                <?= $padraoSelecionado === $padrao->value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($padrao->label()) ?>
                                (mín. R$ <?= number_format($padrao->custoMinimo(), 2, ',', '.') ?> · <?= $padrao->consumoMinimoKwh() ?> kWh)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($erros['padrao_ligacao'])): ?>
                        <p class="mt-1 text-[13px] text-red-600"><?= htmlspecialchars($erros['padrao_ligacao']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tamanho do telhado -->
            <div class="col-span-full mt-1 border-t border-eco-line pt-5">
                <span class="mb-2.5 block text-[14.5px] font-medium">Você sabe o tamanho do telhado?</span>

                <input type="hidden" name="sabe_tamanho_telhado" id="sabe_tamanho_telhado" value="<?= $sabeTamanho ? '1' : '0' ?>">

                <button type="button" data-grupo="tamanho" data-valor="1"
                    class="botao-opcao mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= $sabeTamanho ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Sim
                </button>
                <button type="button" data-grupo="tamanho" data-valor="0"
                    class="botao-opcao mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= !$sabeTamanho ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Não
                </button>

                <span class="mt-1.5 block text-[12.5px] text-eco-hint">Se não souber, calculamos a quantidade de placas apenas pelo consumo.</span>

                <div id="campo_tamanho" class="mt-4 max-w-[420px]" style="<?= $sabeTamanho ? '' : 'display:none' ?>">
                    <label for="tamanho_telhado_m2" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Tamanho do telhado (m²)</label>
                    <input
                        id="tamanho_telhado_m2"
                        name="tamanho_telhado_m2"
                        type="text"
                        placeholder="Ex: 45"
                        value="<?= htmlspecialchars($antigo['tamanho_telhado_m2'] ?? '') ?>"
                        class="<?= $classeInput ?>">
                    <?php if (!empty($erros['tamanho_telhado_m2'])): ?>
                        <p class="mt-1 text-[13px] text-red-600"><?= htmlspecialchars($erros['tamanho_telhado_m2']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Direção do telhado -->
            <div class="col-span-full mt-1 border-t border-eco-line pt-5">
                <span class="mb-2.5 block text-[14.5px] font-medium">Você sabe a direção do telhado?</span>

                <input type="hidden" name="sabe_direcao" id="sabe_direcao" value="<?= $sabeDirecao ? '1' : '0' ?>">

                <button type="button" data-grupo="direcao" data-valor="1"
                    class="botao-opcao mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= $sabeDirecao ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Sim
                </button>
                <button type="button" data-grupo="direcao" data-valor="0"
                    class="botao-opcao mr-2 inline-block rounded-full border px-4 py-[7px] text-[13.5px] <?= !$sabeDirecao ? 'border-eco-green bg-eco-green text-white' : 'border-eco-line text-eco-muted' ?>">
                    Não
                </button>

                <span class="mt-1.5 block text-[12.5px] text-eco-hint">A direção muda a eficiência de geração das placas.</span>

                <div id="campo_direcao" class="mt-4 max-w-[420px]" style="<?= $sabeDirecao ? '' : 'display:none' ?>">
                    <label for="direcao_telhado" class="mb-1.5 block text-[13px] font-medium text-eco-muted">Direção do telhado</label>
                    <select id="direcao_telhado" name="direcao_telhado" class="<?= $classeInput ?>">
                        <?php $direcaoSelecionada = $antigo['direcao_telhado'] ?? RoofDirection::Norte->value; ?>
                        <?php foreach ($direcoes as $direcao): ?>
                            <option value="<?= htmlspecialchars($direcao->value) ?>"
                                <?= $direcaoSelecionada === $direcao->value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($direcao->label()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($erros['direcao_telhado'])): ?>
                        <p class="mt-1 text-[13px] text-red-600"><?= htmlspecialchars($erros['direcao_telhado']) ?></p>
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
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara de moeda
        var campoConta = document.getElementById('valor_conta');
        campoConta.addEventListener('input', function(evento) {
            var digitos = evento.target.value.replace(/\D/g, '');
            var centavos = parseInt(digitos || '0', 10);
            evento.target.value = (centavos / 100).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        });

        // Botões Sim/Não (padrão de ligação, tamanho e direção do telhado)
        var grupos = {
            padrao: {
                oculto: 'sabe_padrao',
                campo: 'campo_padrao'
            },
            tamanho: {
                oculto: 'sabe_tamanho_telhado',
                campo: 'campo_tamanho'
            },
            direcao: {
                oculto: 'sabe_direcao',
                campo: 'campo_direcao'
            },
        };

        document.querySelectorAll('.botao-opcao').forEach(function(botao) {
            botao.addEventListener('click', function() {
                var grupo = botao.getAttribute('data-grupo');
                var valor = botao.getAttribute('data-valor');
                var config = grupos[grupo];

                document.getElementById(config.oculto).value = valor;
                document.getElementById(config.campo).style.display = valor === '1' ? '' : 'none';

                document.querySelectorAll('.botao-opcao[data-grupo="' + grupo + '"]').forEach(function(outro) {
                    var ativo = outro.getAttribute('data-valor') === valor;
                    outro.classList.toggle('border-eco-green', ativo);
                    outro.classList.toggle('bg-eco-green', ativo);
                    outro.classList.toggle('text-white', ativo);
                    outro.classList.toggle('border-eco-line', !ativo);
                    outro.classList.toggle('text-eco-muted', !ativo);
                });
            });
        });
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
