// EcoVolts — protótipo: cadastro e login básicos em JavaScript puro.
// Os usuários ficam guardados só em memória (nesta aba). Ao recarregar a
// página, a lista de contas volta a ficar vazia — é só um protótipo.

(function () {
  "use strict";

  // "banco de dados" em memória: e-mail (minúsculo) -> dados do usuário
  const usuarios = {};
  let usuarioLogado = null;

  // ---------- helpers ----------
  function el(id) {
    return document.getElementById(id);
  }

  function mostrarErro(elemento, mensagem) {
    elemento.textContent = mensagem;
    elemento.classList.add("is-visible");
  }

  function limparErro(elemento) {
    elemento.textContent = "";
    elemento.classList.remove("is-visible");
  }

  function irPara(idTela) {
    el(idTela).checked = true;
  }

  function emailValido(valor) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
  }

  // ---------- cadastro ----------
  function handleCadastro() {
    const erro = el("cadastro-erro");
    limparErro(erro);

    const nome = el("c-nome").value.trim();
    const cpf = el("c-cpf").value.trim();
    const telefone = el("c-tel").value.trim();
    const cep = el("c-cep").value.trim();
    const cidade = el("c-cidade").value.trim();
    const bairro = el("c-bairro").value.trim();
    const rua = el("c-rua").value.trim();
    const email = el("c-email").value.trim();
    const senha1 = el("c-senha1").value;
    const senha2 = el("c-senha2").value;

    const camposObrigatorios = [nome, cpf, telefone, cep, cidade, bairro, rua, email, senha1, senha2];
    if (camposObrigatorios.some((campo) => campo === "")) {
      mostrarErro(erro, "Preencha todos os campos para criar sua conta.");
      return;
    }

    if (!emailValido(email)) {
      mostrarErro(erro, "Informe um e-mail válido.");
      return;
    }

    if (senha1 !== senha2) {
      mostrarErro(erro, "As senhas não coincidem.");
      return;
    }

    if (senha1.length < 6) {
      mostrarErro(erro, "A senha precisa ter pelo menos 6 caracteres.");
      return;
    }

    const chave = email.toLowerCase();
    if (usuarios[chave]) {
      mostrarErro(erro, "Já existe uma conta com este e-mail.");
      return;
    }

    usuarios[chave] = { nome, cpf, telefone, cep, cidade, bairro, rua, email, senha: senha1 };

    // já leva o e-mail preenchido para a tela de login
    el("l-email").value = email;
    el("l-senha").value = "";
    limparErro(el("login-erro"));

    irPara("s-login");
  }

  // ---------- login ----------
  function handleLogin() {
    const erro = el("login-erro");
    limparErro(erro);

    const email = el("l-email").value.trim();
    const senha = el("l-senha").value;

    if (email === "" || senha === "") {
      mostrarErro(erro, "Informe e-mail e senha.");
      return;
    }

    const usuario = usuarios[email.toLowerCase()];
    if (!usuario || usuario.senha !== senha) {
      mostrarErro(erro, "E-mail ou senha incorretos.");
      return;
    }

    usuarioLogado = usuario;
    preencherDadosDoUsuario(usuario);
    irPara("s-menu");
  }

  // ---------- logout ----------
  function handleLogout() {
    usuarioLogado = null;
    el("l-senha").value = "";
    limparErro(el("login-erro"));
    limparErro(el("cadastro-erro"));
  }

  // ---------- preencher telas com os dados de quem está logado ----------
  function preencherDadosDoUsuario(usuario) {
    const primeiroNome = usuario.nome.split(" ")[0];
    el("menu-saudacao").textContent = "Bem-vindo(a), " + primeiroNome;

    el("perfil-nome").textContent = usuario.nome;
    el("perfil-cpf").textContent = usuario.cpf;
    el("perfil-telefone").textContent = usuario.telefone;
    el("perfil-cep").textContent = usuario.cep;
    el("perfil-endereco").textContent = usuario.rua + " — " + usuario.bairro + ", " + usuario.cidade;

    el("relatorio-nome").textContent = usuario.nome;
    el("relatorio-endereco").textContent = usuario.rua + " — " + usuario.bairro + ", " + usuario.cidade;
  }

  // ---------- ligar tudo quando a página carregar ----------
  document.addEventListener("DOMContentLoaded", function () {
    el("btn-cadastrar").addEventListener("click", handleCadastro);
    el("btn-entrar").addEventListener("click", handleLogin);
    el("btn-sair").addEventListener("click", handleLogout);
  });
})();
