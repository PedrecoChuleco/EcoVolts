<?php
/**
 * dashboard.php — painel de EXEMPLO do EcoVolts (simulação).
 *
 * Os números e os botões são estáticos, só para parecer uma área logada real.
 * O que funciona de verdade:
 *   1) só abre se existir o cookie de sessão criado pelo cadastro.php;
 *   2) o botão "Sair" (formulário POST) apaga o cookie e volta ao cadastro.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// DECISÃO: impede o navegador de guardar esta página em cache; depois do
// "Sair", o botão Voltar não reabre o painel.
header('Cache-Control: no-store, no-cache, must-revalidate');

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Opções do cookie (as mesmas do cadastro.php, senão o navegador não o apaga). */
function opcoesCookie(int $expira): array
{
    return [
        'expires'  => $expira,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ];
}

/**
 * Lê o usuário do cookie. O cookie vem do navegador, então NÃO é confiável:
 * confere tipos e tamanhos antes de usar. Retorna null se não houver sessão.
 */
function usuarioLogado(): ?array
{
    $bruto = $_COOKIE['ecovolts_user'] ?? '';
    if (!is_string($bruto) || $bruto === '' || strlen($bruto) > 1000) {
        return null;
    }
    $dados = json_decode($bruto, true);
    if (!is_array($dados) || !is_string($dados['nome'] ?? null) || !is_string($dados['email'] ?? null)) {
        return null;
    }
    return ['nome' => mb_substr($dados['nome'], 0, 100), 'email' => mb_substr($dados['email'], 0, 254)];
}

/** Iniciais para o avatar: primeira letra do primeiro e do último nome. */
function iniciais(string $nome): string
{
    $partes = preg_split('/\s+/u', trim($nome), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if ($partes === []) {
        return '?';
    }
    $ini = mb_substr($partes[0], 0, 1);
    if (count($partes) > 1) {
        $ini .= mb_substr($partes[count($partes) - 1], 0, 1);
    }
    return mb_strtoupper($ini);
}

// SAIR: ação que muda estado, então usa POST (um link GET poderia ser
// disparado por outra página sem o usuário perceber).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'sair') {
    setcookie('ecovolts_user', '', opcoesCookie(time() - 3600));
    header('Location: cadastro.php');
    exit;
}

// Sem sessão -> volta ao cadastro (como uma área logada real faria).
$usuario = usuarioLogado();
if ($usuario === null) {
    header('Location: cadastro.php');
    exit;
}

$primeiroNome = explode(' ', trim($usuario['nome']))[0];

// Dados fictícios do gráfico (kWh por mês).
$meses     = ['Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out'];
$consumo   = [520, 510, 540, 560, 580, 600];
$geracao   = [380, 420, 510, 590, 640, 690];
$escalaMax = 800;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EcoVolts - Menu</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="layout">

  <aside>
    <div class="brand">
      <svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="8" fill="#FFDA21"/><g stroke="#FFDA21" stroke-width="2.5" stroke-linecap="round"><path d="M20 3v5M20 32v5M3 20h5M32 20h5M8 8l3.5 3.5M28.5 28.5L32 32M32 8l-3.5 3.5M11.5 28.5L8 32"/></g></svg>
      <span><b>Eco</b><b>Volts</b></span>
    </div>
    <nav>
      <p class="nav-titulo">Área da conta</p>
      <a href="#" class="ativo">Menu</a>
      <a href="#">Perfil</a>
      <a href="#">Orçamento</a>
      <a href="#">Relatório</a>
    </nav>
  </aside>

  <main>
    <div class="topo">
      <div>
        <!-- Tudo que veio do cookie passa por e() antes de ir para a tela (XSS). -->
        <h1>Olá, <?= e($primeiroNome) ?></h1>
        <p class="sub">Acompanhe sua geração de energia e seus orçamentos.</p>
      </div>
      <div class="usuario">
        <div class="avatar" aria-hidden="true"><?= e(iniciais($usuario['nome'])) ?></div>
        <div class="dados"><strong><?= e($usuario['nome']) ?></strong><span><?= e($usuario['email']) ?></span></div>
        <!-- Único botão funcional: POST apaga o cookie e volta ao cadastro. -->
        <form action="dashboard.php" method="post">
          <input type="hidden" name="acao" value="sair">
          <button type="submit" class="btn-sair">Sair</button>
        </form>
      </div>
    </div>

    <section class="cards">
      <div class="card"><p class="rot">Geração no mês</p><p class="val">690 <small>kWh</small></p><p class="var">▲ 7,8% vs. setembro</p></div>
      <div class="card"><p class="rot">Economia estimada</p><p class="val">R$ 412 <small>/mês</small></p><p class="var">▲ R$ 31 vs. setembro</p></div>
      <div class="card"><p class="rot">Potência instalada</p><p class="val">5,4 <small>kWp</small></p><p class="var" style="color:var(--muted)">12 módulos</p></div>
      <div class="card"><p class="rot">Orçamentos abertos</p><p class="val">2</p><p class="var" style="color:var(--muted)">1 aguardando resposta</p></div>
    </section>

    <section class="grade">
      <div class="card">
        <h2>Consumo × geração</h2>
        <p class="sub" style="font-size:13px">Últimos 6 meses, em kWh</p>
        <div class="legenda"><span><i style="background:#c9d4cc"></i>Consumo</span><span><i style="background:#404E44"></i>Geração</span></div>
        <svg class="grafico" viewBox="0 0 520 220" role="img" aria-label="Gráfico de consumo e geração mensal">
          <?php foreach ([0, 200, 400, 600, 800] as $linha): $y = 190 - ($linha / $escalaMax) * 160; ?>
            <line x1="38" x2="510" y1="<?= $y ?>" y2="<?= $y ?>" stroke="#e6ebe8"/>
            <text x="32" y="<?= $y + 4 ?>" text-anchor="end" font-size="11" fill="#6b7f72"><?= $linha ?></text>
          <?php endforeach; ?>
          <?php foreach ($meses as $i => $mes): $x = 58 + $i * 76; $hc = $consumo[$i] / $escalaMax * 160; $hg = $geracao[$i] / $escalaMax * 160; ?>
            <rect x="<?= $x ?>" y="<?= 190 - $hc ?>" width="26" height="<?= $hc ?>" rx="3" fill="#c9d4cc"/>
            <rect x="<?= $x + 30 ?>" y="<?= 190 - $hg ?>" width="26" height="<?= $hg ?>" rx="3" fill="#404E44"/>
            <text x="<?= $x + 28 ?>" y="209" text-anchor="middle" font-size="12" fill="#6b7f72"><?= e($mes) ?></text>
          <?php endforeach; ?>
        </svg>
      </div>

      <div class="card">
        <h2>Acompanhamento do projeto</h2>
        <ol class="passos">
          <li class="feito"><span class="bolinha">✓</span><div>Orçamento enviado<small>02/10/2026</small></div></li>
          <li class="atual"><span class="bolinha">2</span><div>Visita técnica agendada<small>12/11/2026 · 09:00</small></div></li>
          <li><span class="bolinha">3</span><div>Projeto e homologação<small>Previsão: 30 dias</small></div></li>
          <li><span class="bolinha">4</span><div>Instalação<small>A definir</small></div></li>
        </ol>
      </div>
    </section>

    <section class="card">
      <h2>Orçamentos recentes</h2>
      <table>
        <thead><tr><th>Nº</th><th>Descrição</th><th>Valor</th><th>Status</th></tr></thead>
        <tbody>
          <tr><td>ORC-2026-0142</td><td>Sistema fotovoltaico residencial 5,4 kWp</td><td>R$ 28.900,00</td><td><span class="selo s-analise">Em análise</span></td></tr>
          <tr><td>ORC-2026-0131</td><td>Instalação de inversor híbrido</td><td>R$ 6.450,00</td><td><span class="selo s-aprovado">Aprovado</span></td></tr>
          <tr><td>ORC-2026-0118</td><td>Manutenção preventiva e limpeza de módulos</td><td>R$ 480,00</td><td><span class="selo s-concluido">Concluído</span></td></tr>
        </tbody>
      </table>
    </section>
  </main>
</div>

<script>
  // Links e botões de exemplo não fazem nada (só o "Sair" é real).
  document.querySelectorAll('a[href="#"]').forEach(function (a) {
    a.addEventListener('click', function (ev) { ev.preventDefault(); });
  });
</script>
</body>
</html>
