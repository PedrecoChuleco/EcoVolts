-- EcoVolts — migração para contas de Vendedor e áreas comerciais
-- Execute uma vez em uma base que já possui o schema.sql anterior.
-- MySQL / MariaDB

USE ecovolts;

INSERT INTO Permissao (nome_permissao, desc_permissao) VALUES
    ('editar_orcamentos',             'Editar o histórico de orçamentos'),
    ('gerenciar_estoque',              'Editar produtos, ajustar estoque e gerenciar movimentações'),
    ('consultar_orcamentos',           'Consultar orçamentos de todos os clientes'),
    ('consultar_clientes',             'Consultar clientes e seus históricos de orçamentos'),
    ('consultar_estoque',              'Consultar o estoque de produtos'),
    ('consultar_movimentacao_estoque', 'Consultar a movimentação do estoque')
ON DUPLICATE KEY UPDATE desc_permissao = VALUES(desc_permissao);

-- Vendedor: somente consulta nas novas áreas.
-- Mantém simular_orcamento/ver_relatorio/editar_perfil.
INSERT INTO Perfil_Permissao (id_perfil, id_permissao)
SELECT p.id_perfil, perm.id_permissao
FROM Perfil p
JOIN Permissao perm
  ON perm.nome_permissao IN (
      'consultar_orcamentos',
      'consultar_clientes',
      'consultar_estoque',
      'consultar_movimentacao_estoque'
  )
WHERE p.nome_perfil = 'Vendedor'
ON DUPLICATE KEY UPDATE id_perfil = VALUES(id_perfil);

-- A edição do histórico fica somente para Administrador.
INSERT INTO Perfil_Permissao (id_perfil, id_permissao)
SELECT p.id_perfil, perm.id_permissao
FROM Perfil p
JOIN Permissao perm
  ON perm.nome_permissao = 'editar_orcamentos'
WHERE p.nome_perfil = 'Administrador'
ON DUPLICATE KEY UPDATE id_perfil = VALUES(id_perfil);

-- Administrador recebe também as permissões de consulta.
INSERT INTO Perfil_Permissao (id_perfil, id_permissao)
SELECT p.id_perfil, perm.id_permissao
FROM Perfil p
JOIN Permissao perm
  ON perm.nome_permissao IN (
      'consultar_orcamentos',
      'consultar_clientes',
      'consultar_estoque',
      'consultar_movimentacao_estoque',
      'editar_orcamentos',
      'gerenciar_estoque'
  )
WHERE p.nome_perfil = 'Administrador'
ON DUPLICATE KEY UPDATE id_perfil = VALUES(id_perfil);

-- O Vendedor não terá a permissão ampla de gerenciar_orcamentos:
DELETE pp
FROM Perfil_Permissao pp
JOIN Perfil p ON p.id_perfil = pp.id_perfil
JOIN Permissao perm ON perm.id_permissao = pp.id_permissao
WHERE p.nome_perfil = 'Vendedor'
  AND perm.nome_permissao = 'gerenciar_orcamentos';

-- Garante que o Vendedor permaneça somente com consulta de estoque.
DELETE pp
FROM Perfil_Permissao pp
JOIN Perfil p ON p.id_perfil = pp.id_perfil
JOIN Permissao perm ON perm.id_permissao = pp.id_permissao
WHERE p.nome_perfil = 'Vendedor'
  AND perm.nome_permissao = 'gerenciar_estoque';
