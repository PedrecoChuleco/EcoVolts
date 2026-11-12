<?php
/**
 * Pequenas consultas em cache sobre os dados de referência criados por
 * database/bd-ecovolts.sql. Ficam separadas de includes/db.php para deixar
 * claro que são específicas da EcoVolts, e não infraestrutura genérica de BD.
 */

/**
 * Nomes EXATOS dos produtos de referência usados nos orçamentos gerados pelo
 * simulador. Se um deles for renomeado/apagado no cadastro de produtos, o
 * simulador acusa o erro (ver getProdutoPorNome).
 */
const PRODUTO_PAINEL       = 'Painel Solar 550W Mono';
const PRODUTO_INVERSOR     = 'Inversor Solar 5kWp';
const PRODUTO_CABO         = 'Cabo Solar 6 mm (por metro)';
const PRODUTO_MAO_DE_OBRA  = 'Mão de Obra de Instalação (por placa)';

/**
 * Conta de sistema "Vendedor Online", usada como id_usuario_vendedor em todo
 * orçamento gerado pelo simulador público, até um vendedor humano assumir o
 * atendimento. Ver o SQL do banco para entender por que ela não faz login.
 */
function getOnlineVendorId(PDO $db): int
{
    static $id = null;

    if ($id !== null) {
        return $id;
    }

    $consulta = $db->prepare(
        "SELECT id_usuario FROM Usuario
         WHERE login = 'vendedor.online@ecovolts.com.br' AND is_system = 1
         LIMIT 1"
    );
    $consulta->execute();
    $linha = $consulta->fetch();

    if (!$linha) {
        throw new RuntimeException(
            'Conta "Vendedor Online" não encontrada. Rode database/bd-ecovolts.sql para criá-la.'
        );
    }

    return $id = (int) $linha['id_usuario'];
}

/**
 * Busca um produto de referência pelo nome exato.
 *
 * Colunas devolvidas e seu uso no modelo de cálculo (ver simulador/orcamento-handler.php):
 *   id_produto       - item gravado em Orcamento_Prod
 *   nome_produto     - identificação
 *   valorUn_produto  - valor unitário (base do investimento)
 *   tam_produto      - potência em W (placa = P; inversor = capacidade)
 *   area_m2          - Ap: área ocupada por placa em m² (coluna de Produto)
 */
function getProdutoPorNome(PDO $db, string $nome): array
{
    static $cache = [];

    if (isset($cache[$nome])) {
        return $cache[$nome];
    }

    $consulta = $db->prepare(
        'SELECT id_produto, nome_produto, valorUn_produto, tam_produto, area_m2
         FROM Produto WHERE nome_produto = :nome LIMIT 1'
    );
    $consulta->execute(['nome' => $nome]);
    $linha = $consulta->fetch();

    if (!$linha) {
        throw new RuntimeException(
            "Produto \"{$nome}\" não encontrado. Cadastre-o (ou rode o SQL de produtos do orçamento) com esse nome exato."
        );
    }

    return $cache[$nome] = $linha;
}

/** Placa solar de referência (atalho para getProdutoPorNome). */
function getPainelSolarProduto(PDO $db): array
{
    return getProdutoPorNome($db, PRODUTO_PAINEL);
}

/** O id_perfil para um nome de papel (Cliente / Vendedor / Administrador). */
function getPerfilId(PDO $db, string $nomePerfil): int
{
    $consulta = $db->prepare('SELECT id_perfil FROM Perfil WHERE nome_perfil = :nome LIMIT 1');
    $consulta->execute(['nome' => $nomePerfil]);
    $linha = $consulta->fetch();

    if (!$linha) {
        throw new RuntimeException("Perfil \"{$nomePerfil}\" não encontrado. Rode database/bd-ecovolts.sql.");
    }

    return (int) $linha['id_perfil'];
}

function getClienteParaOrcamento(PDO $db, int $id): ?array
{
    $consulta = $db->prepare(
        "SELECT u.id_usuario, u.nome_usuario
         FROM Usuario u JOIN Perfil p ON p.id_perfil = u.id_perfil
         WHERE u.id_usuario = :id AND p.nome_perfil = 'Cliente' AND u.ativo = 1 AND u.is_system = 0
         LIMIT 1"
    );
    $consulta->execute(['id' => $id]);
    return $consulta->fetch() ?: null;
}
