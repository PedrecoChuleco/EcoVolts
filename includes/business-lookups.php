<?php
/**
 * A couple of small, cached lookups against reference data seeded by
 * database/schema.sql. Kept separate from includes/db.php so it's obvious
 * these are EcoVolts-specific, not generic DB plumbing.
 */

/**
 * The "Vendedor Online" system account used as id_usuario_vendedor for
 * every quote the public simulator generates, until a human salesperson
 * takes over the lead. See schema.sql for why this account can't log in.
 */
function getOnlineVendorId(PDO $db): int
{
    static $id = null;

    if ($id !== null) {
        return $id;
    }

    $stmt = $db->prepare(
        "SELECT id_usuario FROM Usuario
         WHERE login = 'vendedor.online@ecovolts.com.br' AND is_system = 1
         LIMIT 1"
    );
    $stmt->execute();
    $row = $stmt->fetch();

    if (!$row) {
        throw new RuntimeException(
            'Conta "Vendedor Online" não encontrada. Rode database/schema.sql para criá-la.'
        );
    }

    return $id = (int) $row['id_usuario'];
}

/** The reference solar panel product used to itemize generated quotes. */
function getPainelSolarProduto(PDO $db): array
{
    static $produto = null;

    if ($produto !== null) {
        return $produto;
    }

    $stmt = $db->prepare(
        "SELECT id_produto, nome_produto, valorUn_produto
         FROM Produto WHERE nome_produto = 'Painel Solar 550W Mono' LIMIT 1"
    );
    $stmt->execute();
    $row = $stmt->fetch();

    if (!$row) {
        throw new RuntimeException(
            'Produto "Painel Solar 550W Mono" não encontrado. Rode database/schema.sql para criá-lo.'
        );
    }

    return $produto = $row;
}

/** The id_perfil for a given role name (Cliente / Vendedor / Administrador). */
function getPerfilId(PDO $db, string $nomePerfil): int
{
    $stmt = $db->prepare('SELECT id_perfil FROM Perfil WHERE nome_perfil = :nome LIMIT 1');
    $stmt->execute(['nome' => $nomePerfil]);
    $row = $stmt->fetch();

    if (!$row) {
        throw new RuntimeException("Perfil \"{$nomePerfil}\" não encontrado. Rode database/schema.sql.");
    }

    return (int) $row['id_perfil'];
}
