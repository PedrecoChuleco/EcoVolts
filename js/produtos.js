// Lista de produtos dinâmica: busca em tempo real (GET), detalhe (GET ?id=)
// e exclusão (POST JSON), tudo com fetch() e sem recarregar a página.

const campoBusca = document.getElementById('busca');
const corpoTabela = document.getElementById('corpo-tabela');
const areaStatus = document.getElementById('status');
const painel = document.getElementById('detalhe');

const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

// ---------- helpers ----------

// Mostra uma mensagem de estado: carregando | vazio | erro | sucesso
function mostrarStatus(tipo, texto) {
    const cores = { carregando: 'text-secondary', vazio: 'text-muted', erro: 'text-danger', sucesso: 'text-success' };
    areaStatus.className = 'small my-2 ' + (cores[tipo] || '');
    areaStatus.textContent = texto;
}

// Cria um elemento com textContent (seguro contra XSS, sem innerHTML).
function el(tag, texto, classe) {
    const e = document.createElement(tag);
    if (texto !== undefined) e.textContent = texto;
    if (classe) e.className = classe;
    return e;
}

// Chama a API e já trata HTTP != 2xx, devolvendo o JSON.
async function chamarApi(url, opcoes) {
    const resp = await fetch(url, opcoes);
    let dados = null;
    try { dados = await resp.json(); } catch (_) { /* corpo não era JSON */ }
    if (!resp.ok) {
        throw new Error((dados && dados.erro) || 'Erro HTTP ' + resp.status);
    }
    return dados;
}

// ---------- busca em tempo real ----------

let contadorBusca = 0; // descarta respostas antigas que cheguem atrasadas

async function buscar(termo) {
    const minhaBusca = ++contadorBusca;
    mostrarStatus('carregando', 'Buscando...');
    try {
        const dados = await chamarApi('api/produtos.php?q=' + encodeURIComponent(termo));
        if (minhaBusca !== contadorBusca) return;
        renderizar(dados.produtos);
        if (dados.total === 0) {
            mostrarStatus('vazio', termo ? 'Nenhum produto encontrado para "' + termo + '".' : 'Nenhum produto cadastrado.');
        } else {
            mostrarStatus('sucesso', dados.total + ' produto(s) encontrado(s).');
        }
    } catch (erro) {
        if (minhaBusca !== contadorBusca) return;
        corpoTabela.replaceChildren();
        mostrarStatus('erro', 'Não foi possível carregar: ' + erro.message);
    }
}

function renderizar(produtos) {
    corpoTabela.replaceChildren();
    for (const p of produtos) {
        const tr = document.createElement('tr');
        tr.append(el('td', p.id), el('td', p.nome), el('td', moeda.format(p.valor)), el('td', p.descricao || ''));

        const acoes = el('td');
        const btnVer = el('button', 'Detalhes', 'btn btn-sm btn-outline-primary me-1');
        btnVer.addEventListener('click', () => abrirDetalhe(p.id));

        const editar = el('a', 'Editar', 'btn btn-sm btn-outline-secondary me-1');
        editar.href = 'formproduto.php?id=' + p.id;

        const btnExcluir = el('button', 'Excluir', 'btn btn-sm btn-outline-danger');
        btnExcluir.addEventListener('click', () => excluir(p.id, p.nome, tr));

        acoes.append(btnVer, editar, btnExcluir);
        tr.append(acoes);
        corpoTabela.append(tr);
    }
}

// Debounce: só busca 300 ms depois da última tecla.
let temporizador;
campoBusca.addEventListener('input', () => {
    clearTimeout(temporizador);
    temporizador = setTimeout(() => buscar(campoBusca.value.trim()), 300);
});

// ---------- detalhe ----------

async function abrirDetalhe(id) {
    painel.hidden = false;
    painel.replaceChildren(el('p', 'Carregando detalhes...', 'text-secondary mb-0'));
    try {
        const { produto } = await chamarApi('api/produtos.php?id=' + id);

        const fechar = el('button', 'Fechar', 'btn btn-sm btn-light float-end');
        fechar.addEventListener('click', () => { painel.hidden = true; });

        painel.replaceChildren(
            fechar,
            el('h4', produto.nome),
            el('p', produto.descricao || 'Sem descrição.', 'text-muted'),
            el('p', 'Preço: ' + moeda.format(produto.valor), 'mb-1'),
            el('p', 'Unidades vendidas: ' + produto.total_vendido, 'mb-0')
        );
    } catch (erro) {
        painel.replaceChildren(el('p', 'Erro: ' + erro.message, 'text-danger mb-0'));
    }
}

// ---------- exclusão via POST ----------

async function excluir(id, nome, linha) {
    if (!confirm('Deseja realmente excluir "' + nome + '"?')) return;
    try {
        await chamarApi('api/produto_excluir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        linha.remove();
        painel.hidden = true;
        mostrarStatus('sucesso', '"' + nome + '" excluído.');
    } catch (erro) {
        mostrarStatus('erro', erro.message);
    }
}

buscar(''); // carga inicial
