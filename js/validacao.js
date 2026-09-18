/*
 * EcoVolts - Validação de formulários (client-side)
 * Este script NÃO substitui a validação do PHP no servidor,
 * ele apenas dá um retorno mais rápido ao usuário no navegador.
 */

document.addEventListener('DOMContentLoaded', function () {
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    /* ---------- Formulário de login ---------- */
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function (evento) {
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value;
            const mensagem = document.getElementById('mensagemLogin');

            if (email === '' || senha === '') {
                evento.preventDefault();
                mensagem.textContent = 'Preencha todos os campos.';
                mensagem.className = 'mensagem erro';
                return;
            }

            if (!regexEmail.test(email)) {
                evento.preventDefault();
                mensagem.textContent = 'Digite um e-mail válido.';
                mensagem.className = 'mensagem erro';
                return;
            }

            // Passou na validação: o formulário segue normalmente
            // para includes/processa_login.php
        });
    }

    /* ---------- Formulário de cadastro ---------- */
    const cadastroForm = document.getElementById('cadastroForm');

    if (cadastroForm) {
        cadastroForm.addEventListener('submit', function (evento) {
            const nome = document.getElementById('nome').value.trim();
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmarSenha').value;
            const mensagem = document.getElementById('mensagemCadastro');

            if (nome === '' || email === '' || senha === '' || confirmarSenha === '') {
                evento.preventDefault();
                mensagem.textContent = 'Preencha todos os campos.';
                mensagem.className = 'mensagem erro';
                return;
            }

            if (!regexEmail.test(email)) {
                evento.preventDefault();
                mensagem.textContent = 'Digite um e-mail válido.';
                mensagem.className = 'mensagem erro';
                return;
            }

            if (senha.length < 6) {
                evento.preventDefault();
                mensagem.textContent = 'A senha deve ter no mínimo 6 caracteres.';
                mensagem.className = 'mensagem erro';
                return;
            }

            if (senha !== confirmarSenha) {
                evento.preventDefault();
                mensagem.textContent = 'As senhas não coincidem.';
                mensagem.className = 'mensagem erro';
                return;
            }

            // Passou na validação: segue para includes/processa_cadastro.php
        });
    }
});
