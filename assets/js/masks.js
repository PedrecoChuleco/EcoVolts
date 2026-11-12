/**
 * Máscaras de entrada.
 *
 * Uso: basta o campo ter um destes ids: cpf, cnpj, cep.
 * O servidor recebe o valor mascarado; os handlers PHP já removem
 * tudo que não é dígito (preg_replace('/\D/', '')) antes de validar e gravar.
 */
(function () {
    'use strict';

    var onlyDigits = function (v) { return String(v || '').replace(/\D/g, ''); };

    var masks = {
        cpf: function (v) {
            return onlyDigits(v).slice(0, 11)
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        },
        cnpj: function (v) {
            return onlyDigits(v).slice(0, 14)
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');
        },
        cep: function (v) {
            return onlyDigits(v).slice(0, 8).replace(/^(\d{5})(\d)/, '$1-$2');
        }
    };

    function applyMask(input, fn) {
        if (!input) return;
        input.value = fn(input.value); // valores salvos (só dígitos) já aparecem formatados
        input.addEventListener('input', function () {
            input.value = fn(input.value);
        });
    }

    function init() {
        Object.keys(masks).forEach(function (id) {
            applyMask(document.getElementById(id), masks[id]);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();