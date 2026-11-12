/**
 * Integração com a API ViaCEP: ao informar um CEP completo, preenche
 * logradouro, bairro, cidade e estado.
 *
 * Campos esperados (ids): cep, logradouro, nome_bairro, nome_cidade,
 * sigla_estado e, opcionalmente, num_endereco (recebe o foco ao final).
 * Não depende de masks.js: lê apenas os dígitos do campo CEP.
 */
(function () {
    'use strict';

    var ENDPOINT = 'https://viacep.com.br/ws/';

    var onlyDigits = function (v) { return String(v || '').replace(/\D/g, ''); };
    var $ = function (id) { return document.getElementById(id); };

    function init() {
        var cep = $('cep');
        if (!cep) return;

        // Mensagem de status logo abaixo do campo CEP.
        var status = document.createElement('span');
        status.setAttribute('aria-live', 'polite');
        cep.insertAdjacentElement('afterend', status);

        function setStatus(msg, isError) {
            status.textContent = msg || '';
            status.className = 'text-[12px] ' + (isError ? 'text-red-600' : 'text-eco-hint');
        }

        function fill(id, value) {
            var el = $(id);
            if (el && value) el.value = value; // só sobrescreve se o ViaCEP trouxe algo
        }

        var lastLookup = '';

        function lookup() {
            var digits = onlyDigits(cep.value);

            if (digits.length !== 8) {
                setStatus('');
                return;
            }
            if (digits === lastLookup) return;
            lastLookup = digits;

            setStatus('Buscando endereço…');

            fetch(ENDPOINT + digits + '/json/')
                .then(function (r) {
                    if (!r.ok) throw new Error('http ' + r.status);
                    return r.json();
                })
                .then(function (data) {
                    // Descarta resposta antiga se o usuário já mudou o CEP.
                    if (onlyDigits(cep.value) !== digits) return;

                    if (data.erro) {
                        lastLookup = '';
                        setStatus('CEP não encontrado. Preencha o endereço manualmente.', true);
                        return;
                    }

                    fill('logradouro', data.logradouro);
                    fill('nome_bairro', data.bairro);
                    fill('nome_cidade', data.localidade);
                    if (data.uf && $('sigla_estado')) $('sigla_estado').value = data.uf;

                    setStatus('Endereço preenchido. Confira e informe o número.');
                    if ($('num_endereco')) $('num_endereco').focus();
                })
                .catch(function () {
                    lastLookup = '';
                    setStatus('Não foi possível consultar o CEP agora. Preencha manualmente.', true);
                });
        }

        cep.addEventListener('input', lookup);
        cep.addEventListener('blur', lookup);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();