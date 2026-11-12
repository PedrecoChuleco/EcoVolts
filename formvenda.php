<?php
    require_once 'conexao.php';
    include("protect.php");

    $id = $_GET['id'] ?? null;
    $venda = ['cliente_id' => '', 'data_venda' => date('Y-m-d'), 'valor_total' => 0];
    $itens_venda = [];
   
    // Buscar todos os clientes
    $stmt = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar todos os produtos
    $stmt = $pdo->query("SELECT id, nome, valor FROM produtos ORDER BY nome");
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar formulário
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cliente_id = $_POST['cliente_id'];
        $data_venda = $_POST['data_venda'];
        $produtos_selecionados = $_POST['produto_id'] ?? [];
        $quantidades = $_POST['quantidade'] ?? [];
        $precos = $_POST['preco_unitario'] ?? [];
        
        // Calcular valor total
        $valor_total = 0;
        for($i = 0; $i < count($produtos_selecionados); $i++) {
            if(!empty($produtos_selecionados[$i]) && $quantidades[$i] > 0) {
                $valor_total += $quantidades[$i] * $precos[$i];
            }
        }
        
        try {
            $pdo->beginTransaction();            
           
            // Inserir nova venda
            $stmt = $pdo->prepare("INSERT INTO vendas (cliente_id, data_venda, valor_total) VALUES (?, ?, ?)");
            $stmt->execute([$cliente_id, $data_venda, $valor_total]);
            $venda_id = $pdo->lastInsertId();
           
            
            // Inserir itens da venda
            $stmt = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            
            for($i = 0; $i < count($produtos_selecionados); $i++) {
                if(!empty($produtos_selecionados[$i]) && $quantidades[$i] > 0) {
                    $produto_id = $produtos_selecionados[$i];
                    $quantidade = $quantidades[$i];
                    $preco_unitario = $precos[$i];
                    $subtotal = $quantidade * $preco_unitario;
                    
                    $stmt->execute([$venda_id, $produto_id, $quantidade, $preco_unitario, $subtotal]);
                }
            }
            
            $pdo->commit();
            header("Location: vendas.php");
            exit;
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao processar venda: " . $e->getMessage();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Venda</title>   
</head>
<body>
    <h1>Cadastrar Venda</h1>
    
    <?php if(isset($erro)): ?>
        <p><?= $erro ?></p>
    <?php endif; ?>
    
    <form method="POST" id="formVenda">
        <p>
            <label>Cliente:</label><br>
            <select name="cliente_id" required style="width: 300px;">
                <option value="">Selecione um cliente</option>
                <?php foreach($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>">
                        <?= htmlspecialchars($cliente['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        
        <p>
            <label>Data da Venda:</label><br>
            <input type="date" name="data_venda" value="<?= htmlspecialchars($venda['data_venda']) ?>" required>
        </p>
        
        <h3>Produtos</h3>
        <div id="itens-container">           
                <div class="item-venda">
                    <label>Produto:</label>
                    <select name="produto_id[]" class="produto-select" required style="width: 250px;">
                        <option value="">Selecione um produto</option>
                        <?php foreach($produtos as $produto): ?>
                            <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['valor'] ?>">
                                <?= htmlspecialchars($produto['nome']) ?> - R$ <?= number_format($produto['valor'], 2, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Quantidade:</label>
                    <input type="number" name="quantidade[]" class="quantidade" value="1" min="1" required style="width: 80px;">
                    
                    <label>Preço Unit.:</label>
                    <input type="number" name="preco_unitario[]" class="preco" value="0" step="0.01" required style="width: 100px;" readonly>
                    
                    <span class="btn-remover" onclick="removerItem(this)"> Remover</span>
                </div>
        </div>
        
        <p>
            <button type="button" onclick="adicionarItem()"> Adicionar Produto</button>
        </p>
        
        <p>
            <strong>Valor Total: R$ <span id="total">0,00</span></strong>
        </p>
        
        <p>
            <button type="submit">Cadastrar</button>
            <a href="vendas.php">Cancelar</a>
        </p>
    </form>

    <script>
        const produtosData = <?= json_encode($produtos) ?>;
        
        function adicionarItem() {
            const container = document.getElementById('itens-container');
            const novoItem = document.createElement('div');
            novoItem.className = 'item-venda';
            
            let optionsProdutos = '<option value="">Selecione um produto</option>';
            produtosData.forEach(p => {
                optionsProdutos += `<option value="${p.id}" data-preco="${p.valor}">${p.nome} - R$ ${Number(p.valor).toFixed(2).replace('.', ',')}</option>`;
            });
            
            novoItem.innerHTML = `
                <label>Produto:</label>
                <select name="produto_id[]" class="produto-select" required style="width: 250px;">
                    ${optionsProdutos}
                </select>
                
                <label>Quantidade:</label>
                <input type="number" name="quantidade[]" class="quantidade" value="1" min="1" required style="width: 80px;">
                
                <label>Preço Unit.:</label>
                <input type="number" name="preco_unitario[]" class="preco" value="0" step="0.01" required style="width: 100px;" readonly>
                
                <span class="btn-remover" onclick="removerItem(this)"> Remover</span>
            `;
            
            container.appendChild(novoItem);
            atualizarEventos();
        }
        
        function removerItem(btn) {
            const itens = document.querySelectorAll('.item-venda');
            if(itens.length > 1) {
                btn.parentElement.remove();
                calcularTotal();
            } else {
                alert('Deve haver pelo menos um produto na venda!');
            }
        }
        
        function calcularTotal() {
            let total = 0;
            const itens = document.querySelectorAll('.item-venda');
            
            itens.forEach(item => {
                const quantidade = parseFloat(item.querySelector('.quantidade').value) || 0;
                const preco = parseFloat(item.querySelector('.preco').value) || 0;
                total += quantidade * preco;
            });
            
            document.getElementById('total').textContent = total.toFixed(2).replace('.', ',');
        }
        
        function atualizarEventos() {
            document.querySelectorAll('.produto-select').forEach(select => {
                select.removeEventListener('change', handleProdutoChange);
                select.addEventListener('change', handleProdutoChange);
            });
            
            document.querySelectorAll('.quantidade').forEach(input => {
                input.removeEventListener('input', calcularTotal);
                input.addEventListener('input', calcularTotal);
            });
        }
        
        function handleProdutoChange(e) {
            const option = e.target.selectedOptions[0];
            const preco = option.getAttribute('data-preco') || 0;
            const itemDiv = e.target.closest('.item-venda');
            itemDiv.querySelector('.preco').value = preco;
            calcularTotal();
        }
        
        // Inicializar eventos
        atualizarEventos();
        calcularTotal();
    </script>
</body>
</html>