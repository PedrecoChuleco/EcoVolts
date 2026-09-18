<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
exigirLogin(); // bloqueia quem não estiver logado

$usuarioId = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT * FROM registros WHERE usuario_id = ? ORDER BY data_registro DESC, id DESC');
$stmt->execute([$usuarioId]);
$registros = $stmt->fetchAll();

$erro    = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoVolts - Painel</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="topo-dashboard">
    <nav class="nav-dashboard">
        <span class="marca">EcoVolts</span>
        <div class="usuario-info">
            <span>Olá, <?= h($_SESSION['usuario_nome']) ?></span>
            <a href="logout.php" class="btn-sair">Sair</a>
        </div>
    </nav>
</header>

<main class="conteudo-dashboard">

    <section class="cabecalho-secao">
        <h1>Meus Registros de Energia Solar</h1>
        <button id="btnNovoRegistro" type="button">+ Novo Registro</button>
    </section>

    <?php if ($erro || $sucesso): ?>
        <p class="mensagem <?= $erro ? 'erro' : 'sucesso' ?> mensagem-topo"><?= h($erro ?: $sucesso) ?></p>
    <?php endif; ?>

    <div class="tabela-wrapper">
        <table class="tabela-registros">
            <thead>
                <tr>
                    <th>Equipamento</th>
                    <th>Potência (kWh)</th>
                    <th>Data</th>
                    <th>Observação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($registros)): ?>
                <tr><td colspan="5" class="vazio">Nenhum registro cadastrado ainda.</td></tr>
            <?php else: ?>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td data-rotulo="Equipamento"><?= h($r['nome_equipamento']) ?></td>
                        <td data-rotulo="Potência"><?= h($r['potencia_kwh']) ?></td>
                        <td data-rotulo="Data"><?= h(date('d/m/Y', strtotime($r['data_registro']))) ?></td>
                        <td data-rotulo="Observação"><?= h($r['observacao']) ?></td>
                        <td class="acoes" data-rotulo="Ações">
                            <button type="button" class="btn-editar"
                                data-id="<?= h((string) $r['id']) ?>"
                                data-nome="<?= h($r['nome_equipamento']) ?>"
                                data-potencia="<?= h((string) $r['potencia_kwh']) ?>"
                                data-data="<?= h($r['data_registro']) ?>"
                                data-obs="<?= h($r['observacao']) ?>">Editar</button>

                            <form class="form-excluir" action="includes/processa_excluir_item.php" method="POST">
                                <input type="hidden" name="id" value="<?= h((string) $r['id']) ?>">
                                <button type="submit" class="btn-excluir">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<footer class="rodape rodape-dashboard">EcoVolts • ODS 9 • Energia limpa e inovação</footer>

<div id="modalRegistro" class="modal escondido">
    <div class="modal-card">
        <button id="fecharModalRegistro" class="fechar" type="button">×</button>
        <h2 id="tituloModalRegistro">Novo Registro</h2>
        <p class="sub">Preencha os dados do registro de energia.</p>

        <form id="formRegistro" action="includes/processa_criar_item.php" method="POST" novalidate>
            <input type="hidden" id="registroId" name="id" value="">

            <label for="nomeEquipamento">Equipamento</label>
            <input id="nomeEquipamento" name="nome_equipamento" type="text" placeholder="Ex: Painel Solar 450W" required>

            <label for="potencia">Potência gerada (kWh)</label>
            <input id="potencia" name="potencia_kwh" type="number" step="0.01" min="0" placeholder="Ex: 12.5" required>

            <label for="data">Data do registro</label>
            <input id="data" name="data_registro" type="date" required>

            <label for="observacao">Observação</label>
            <input id="observacao" name="observacao" type="text" placeholder="Opcional">

            <button type="submit">Salvar</button>
            <p id="mensagemRegistro" class="mensagem"></p>
        </form>
    </div>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>
