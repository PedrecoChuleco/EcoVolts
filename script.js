/*
 * EcoVolts - Atividade de Login
 * Estrutura de cadastro do usuário e entrada no sistema.
 */

/* Estrutura de cadastro do usuário */
class Usuario {
    constructor(nome, email, senha) {
        this.nome = nome;
        this.email = email;
        this.senha = senha;
    }
}

/* Usuários cadastrados no sistema */
let usuarios = JSON.parse(localStorage.getItem("ecovolts_usuarios")) || [
    new Usuario("Usuário Teste", "teste@ecovolts.com", "123456")
];

function salvarUsuarios() {
    localStorage.setItem("ecovolts_usuarios", JSON.stringify(usuarios));
}

/* Entrada do sistema através do clique no botão Entrar */
document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim().toLowerCase();
    const senha = document.getElementById("senha").value;
    const mensagem = document.getElementById("mensagemLogin");

    const usuario = usuarios.find(function(u) {
        return u.email.toLowerCase() === email && u.senha === senha;
    });

    if (usuario) {
        mensagem.textContent = "Login realizado com sucesso! Bem-vindo(a), " + usuario.nome + ".";
        mensagem.className = "mensagem sucesso";
        console.log("Entrada do sistema:", usuario);
    } else {
        mensagem.textContent = "E-mail ou senha incorretos.";
        mensagem.className = "mensagem erro";
    }
});

/* Cadastro de usuário */
const modal = document.getElementById("modal");

document.getElementById("abrirCadastro").addEventListener("click", function() {
    modal.classList.remove("escondido");
});

document.getElementById("fecharCadastro").addEventListener("click", function() {
    modal.classList.add("escondido");
});

modal.addEventListener("click", function(event) {
    if (event.target === modal) modal.classList.add("escondido");
});

document.getElementById("cadastroForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const nome = document.getElementById("nome").value.trim();
    const email = document.getElementById("emailCadastro").value.trim().toLowerCase();
    const senha = document.getElementById("senhaCadastro").value;
    const mensagem = document.getElementById("mensagemCadastro");

    if (usuarios.some(function(u) { return u.email.toLowerCase() === email; })) {
        mensagem.textContent = "Este e-mail já está cadastrado.";
        mensagem.className = "mensagem erro";
        return;
    }

    usuarios.push(new Usuario(nome, email, senha));
    salvarUsuarios();

    mensagem.textContent = "Cadastro realizado com sucesso!";
    mensagem.className = "mensagem sucesso";
    document.getElementById("cadastroForm").reset();

    setTimeout(function() {
        modal.classList.add("escondido");
        document.getElementById("email").value = email;
    }, 900);
});
