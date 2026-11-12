<?php
/**
 * Small "find or create" helpers for the normalized address tables
 * (Estado -> Cidade -> Bairro -> Endereco). Estado is fixed reference data
 * (seeded by database/schema.sql with all 27 Brazilian states), so it's
 * always a lookup. Cidade and Bairro grow organically as users fill in
 * their profile, so they're created on first use.
 */

/** Looks up an Estado by its 2-letter sigla. Returns null if not found. */
function findEstadoBySigla(PDO $db, string $sigla): ?array
{
    $stmt = $db->prepare('SELECT id_estado, nome_estado, sigla_estado FROM Estado WHERE sigla_estado = :sigla LIMIT 1');
    $stmt->execute(['sigla' => strtoupper(trim($sigla))]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/** Returns every Estado, ordered by name — for populating a <select>. */
function listEstados(PDO $db): array
{
    return $db->query('SELECT id_estado, nome_estado, sigla_estado FROM Estado ORDER BY nome_estado')->fetchAll();
}

function findOrCreateCidade(PDO $db, string $nomeCidade, int $idEstado): int
{
    $nomeCidade = trim($nomeCidade);

    $find = $db->prepare('SELECT id_cidade FROM Cidade WHERE nome_cidade = :nome AND id_estado = :estado LIMIT 1');
    $find->execute(['nome' => $nomeCidade, 'estado' => $idEstado]);
    $row = $find->fetch();

    if ($row) {
        return (int) $row['id_cidade'];
    }

    $insert = $db->prepare('INSERT INTO Cidade (nome_cidade, id_estado) VALUES (:nome, :estado)');
    $insert->execute(['nome' => $nomeCidade, 'estado' => $idEstado]);

    return (int) $db->lastInsertId();
}

function findOrCreateBairro(PDO $db, string $nomeBairro, int $idCidade): int
{
    $nomeBairro = trim($nomeBairro);

    $find = $db->prepare('SELECT id_bairro FROM Bairro WHERE nome_bairro = :nome AND id_cidade = :cidade LIMIT 1');
    $find->execute(['nome' => $nomeBairro, 'cidade' => $idCidade]);
    $row = $find->fetch();

    if ($row) {
        return (int) $row['id_bairro'];
    }

    $insert = $db->prepare('INSERT INTO Bairro (nome_bairro, id_cidade) VALUES (:nome, :cidade)');
    $insert->execute(['nome' => $nomeBairro, 'cidade' => $idCidade]);

    return (int) $db->lastInsertId();
}

/**
 * Creates or updates the user's "Residencial" address in one go: resolves
 * Cidade/Bairro (creating them if new), then either updates the Endereco
 * row already linked to this user or creates a new one and links it.
 *
 * $data keys: logradouro, num_endereco, complemento, cep, nome_cidade, id_estado, nome_bairro
 */
function upsertEnderecoResidencial(PDO $db, int $idUsuario, array $data): void
{
    $idCidade = findOrCreateCidade($db, $data['nome_cidade'], $data['id_estado']);
    $idBairro = findOrCreateBairro($db, $data['nome_bairro'], $idCidade);

    $existing = $db->prepare(
        "SELECT e.id_endereco
         FROM usuario_endereco ue
         JOIN Endereco e ON e.id_endereco = ue.id_endereco
         WHERE ue.id_usuario = :uid AND ue.tipo_endereco = 'Residencial'
         LIMIT 1"
    );
    $existing->execute(['uid' => $idUsuario]);
    $row = $existing->fetch();

    $params = [
        'logradouro'   => $data['logradouro'],
        'num_endereco' => $data['num_endereco'],
        'complemento'  => $data['complemento'],
        'id_bairro'    => $idBairro,
        'cep'          => $data['cep'],
    ];

    if ($row) {
        $params['id'] = $row['id_endereco'];
        $db->prepare(
            'UPDATE Endereco
             SET logradouro = :logradouro, num_endereco = :num_endereco,
                 complemento = :complemento, id_bairro = :id_bairro, cep = :cep
             WHERE id_endereco = :id'
        )->execute($params);

        return;
    }

    $db->prepare(
        'INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
         VALUES (:logradouro, :num_endereco, :complemento, :id_bairro, :cep)'
    )->execute($params);

    $idEndereco = (int) $db->lastInsertId();

    $db->prepare(
        "INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
         VALUES (:uid, :eid, 'Residencial')"
    )->execute(['uid' => $idUsuario, 'eid' => $idEndereco]);
}

/** Fetches the user's current "Residencial" address, joined down to Estado, for pre-filling a form. */
function getEnderecoResidencial(PDO $db, int $idUsuario): ?array
{
    $stmt = $db->prepare(
        "SELECT e.logradouro, e.num_endereco, e.complemento, e.cep,
                b.nome_bairro, c.nome_cidade, es.sigla_estado
         FROM usuario_endereco ue
         JOIN Endereco e  ON e.id_endereco = ue.id_endereco
         JOIN Bairro b    ON b.id_bairro   = e.id_bairro
         JOIN Cidade c    ON c.id_cidade   = b.id_cidade
         JOIN Estado es   ON es.id_estado  = c.id_estado
         WHERE ue.id_usuario = :uid AND ue.tipo_endereco = 'Residencial'
         LIMIT 1"
    );
    $stmt->execute(['uid' => $idUsuario]);
    $row = $stmt->fetch();

    return $row ?: null;
}
