// script.js

const itens = document.querySelectorAll('.item');
const listaItens = document.getElementById('lista-itens');
const totalSpan = document.getElementById('total');
const botaoFinalizar = document.getElementById('finalizar');
const reciboFinal = document.getElementById('recibo-final');
const reciboItens = document.getElementById('recibo-itens');
const totalFinal = document.getElementById('total-final');
const botaoNovaVenda = document.getElementById('nova-venda');

let carrinho = [];

// Atualiza a lista provisória conforme o usuário seleciona/desmarca
function atualizarListaProvisoria() {
  carrinho = [];
  let total = 0;
  let html = '<ul>';

  itens.forEach(item => {
    if (item.checked) {
      const nome = item.dataset.nome;
      const preco = parseFloat(item.dataset.preco);
      carrinho.push({ nome, preco });
      html += `<li>${nome} — R$ ${preco.toFixed(2)}</li>`;
      total += preco;
    }
  });

  html += '</ul>';

  if (carrinho.length > 0) {
    listaItens.innerHTML = html;
  } else {
    listaItens.innerHTML = '<p>Nenhum item selecionado.</p>';
  }
  
  totalSpan.textContent = total.toFixed(2).replace('.', ',');
}

// Gera o recibo final ao clicar em "Finalizar Compra"
function gerarReciboFinal() {
  if (carrinho.length === 0) {
    alert("Selecione ao menos um item antes de finalizar a compra!");
    return;
  }

  let total = 0;
  let html = '<ul>';

  carrinho.forEach(item => {
    html += `<li>${item.nome} — R$ ${item.preco.toFixed(2)}</li>`;
    total += item.preco;
  });

  html += '</ul>';

  reciboItens.innerHTML = html;
  totalFinal.textContent = total.toFixed(2).replace('.', ',');

  // Exibe o recibo e rola até ele
  reciboFinal.classList.remove('hidden');
  reciboFinal.scrollIntoView({ behavior: 'smooth' });
}

// Inicia uma nova venda
function novaVenda() {
    // Desmarca todos os checkboxes
    itens.forEach(item => (item.checked = false));
    // Limpa as variáveis e áreas da tela
    carrinho = [];
    listaItens.innerHTML = '<p>Nenhum item selecionado.</p>';
    totalSpan.textContent = '0,00';
    totalFinal.textContent = '0,00';
    reciboItens.innerHTML = '';
    reciboFinal.classList.add('hidden');
}


// Eventos
itens.forEach(item => item.addEventListener('change', atualizarListaProvisoria));
botaoFinalizar.addEventListener('click', gerarReciboFinal);
botaoNovaVenda.addEventListener('click', novaVenda);

// Inicializa
atualizarListaProvisoria();
