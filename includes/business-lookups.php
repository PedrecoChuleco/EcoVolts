<?php
/**
 * Pequenas consultas em cache sobre os dados de referência criados por
 * database/bd-ecovolts.sql. Ficam separadas de includes/db.php para deixar
 * claro que são específicas da EcoVolts, e não infraestrutura genérica de BD.
 */

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
 * Produto de referência (placa solar) usado para calcular e itemizar os orçamentos.
 *
 * Colunas devolvidas e seu uso no modelo de cálculo (ver orcamento-handler.php):
 *   id_produto       - item gravado em Orcamento_Prod
 *   nome_produto     - identificação
 *   valorUn_produto  - valor unitário (base do investimento)
 *   tam_produto      - P: potência da placa em Wp
 *   area_m2          - Ap: área ocupada por placa em m² (coluna nova em Produto)
 */
function getPainelSolarProduto(PDO $db): array
{
    static $produto = null;

    if ($produto !== null) {
        return $produto;
    }

    $consulta = $db->prepare(
        "SELECT id_produto, nome_produto, valorUn_produto, tam_produto, area_m2
         FROM Produto WHERE nome_produto = 'Painel Solar 550W Mono' LIMIT 1"
    );
    $consulta->execute();
    $linha = $consulta->fetch();

    if (!$linha) {
        throw new RuntimeException(
            'Produto "Painel Solar 550W Mono" não encontrado. Rode database/bd-ecovolts.sql para criá-lo.'
        );
    }

    return $produto = $linha;
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
