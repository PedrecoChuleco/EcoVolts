/*
 * EcoVolts - Painel (CRUD de registros)
 * Controla a modal de novo/editar registro, a confirmação de exclusão
 * e a validação client-side do formulário de registro.
 */

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalRegistro');
    const btnNovo = document.getElementById('btnNovoRegistro');
    const btnFechar = document.getElementById('fecharModalRegistro');
    const form = document.getElementById('formRegistro');
    const titulo = document.getElementById('tituloModalRegistro');
    const mensagem = document.getElementById('mensagemRegistro');

    const campoId = document.getElementById('registroId');
    const campoNome = document.getElementById('nomeEquipamento');
    const campoPotencia = document.getElementById('potencia');
    const campoData = document.getElementById('data');
    const campoObs = document.getElementById('observacao');

    function abrirModalNovo() {
        form.action = 'includes/processa_criar_item.php';
        form.reset();
        campoId.value = '';
        titulo.textContent = 'Novo Registro';
        mensagem.textContent = '';
        mensagem.className = 'mensagem';
        modal.classList.remove('escondido');
    }

    function abrirModalEditar(botao) {
        form.action = 'includes/processa_editar_item.php';
        campoId.value = botao.dataset.id;
        campoNome.value = botao.dataset.nome;
        campoPotencia.value = botao.dataset.potencia;
        campoData.value = botao.dataset.data;
        campoObs.value = botao.dataset.obs;
        titulo.textContent = 'Editar Registro';
        mensagem.textContent = '';
        mensagem.className = 'mensagem';
        modal.classList.remove('escondido');
    }

    function fecharModal() {
        modal.classList.add('escondido');
    }

    btnNovo.addEventListener('click', abrirModalNovo);
    btnFechar.addEventListener('click', fecharModal);

    modal.addEventListener('click', function (evento) {
        if (evento.target === modal) fecharModal();
    });

    /* Liga o botão "Editar" de cada linha da tabela à modal */
    document.querySelectorAll('.btn-editar').forEach(function (botao) {
        botao.addEventListener('click', function () {
            abrirModalEditar(botao);
        });
    });

    /* Pede confirmação antes de excluir um registro */
    document.querySelectorAll('.form-excluir').forEach(function (formExcluir) {
        formExcluir.addEventListener('submit', function (evento) {
            const confirmou = confirm('Deseja realmente excluir este registro?');
            if (!confirmou) {
                evento.preventDefault();
            }
        });
    });

    /* Validação client-side do formulário de criar/editar registro */
    form.addEventListener('submit', function (evento) {
        const nome = campoNome.value.trim();
        const potencia = campoPotencia.value;
        const data = campoData.value;

        if (nome === '' || potencia === '' || data === '') {
            evento.preventDefault();
            mensagem.textContent = 'Preencha os campos obrigatórios.';
            mensagem.className = 'mensagem erro';
            return;
        }

        if (isNaN(potencia) || Number(potencia) < 0) {
            evento.preventDefault();
            mensagem.textContent = 'Informe uma potência válida.';
            mensagem.className = 'mensagem erro';
        }
    });
});
