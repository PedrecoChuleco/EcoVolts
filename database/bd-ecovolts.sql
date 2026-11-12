-- ============================================================================
-- EcoVolts — banco de dados completo (MySQL / MariaDB)       bd-ecovolts.sql
--
-- UM ÚNICO ARQUIVO que substitui:
--   * BD-CREATE-MYSQL.sql         modelo original (estrutura base das 15 tabelas)
--   * schema.sql                  estrutura ajustada + dados iniciais
--   * migration-vendedores.sql    permissões das contas de Vendedor / áreas comerciais
--
-- Serve para os dois casos, e pode ser executado quantas vezes quiser:
--   1) SERVIDOR NOVO ........ cria o banco, as tabelas e todos os dados iniciais.
--   2) BANCO JÁ EXISTENTE ... (criado a partir do schema.sql anterior) mantém seus
--                             dados e apenas aplica as permissões novas.
--
-- Rode com:   mysql -u root -p < bd-ecovolts.sql
--   ou no phpMyAdmin: aba "Importar" -> escolha este arquivo (charset utf-8).
--
-- Ajustes em relação ao modelo original (BD-CREATE-MYSQL.sql):
--   - Usuario:  `login` UNIQUE e com 150 caracteres (cabe e-mail); novas colunas
--               `ativo`, `is_system`, `criado_em`.
--   - Telhado:  `area_estimada`, `direcao_estimada`, `comporta_placas`.
--   - Dados iniciais: perfis, 27 estados, catálogo/estoque, permissões e a conta
--               de sistema "Vendedor Online" (não faz login).
--
-- ATENÇÃO: este arquivo NÃO converte um banco criado direto do BD-CREATE-MYSQL.sql
-- original (que não tem as colunas acima). Nesse caso, apague o banco
-- (DROP DATABASE ecovolts;) e rode este arquivo.
--
-- PERMISSÕES POR PERFIL
--   Permissão                        Cliente  Vendedor  Administrador
--   simular_orcamento                   x        x          x
--   ver_relatorio                       x        x          x
--   editar_perfil                       x        x          x
--   consultar_orcamentos                         x          x
--   consultar_clientes                           x          x
--   consultar_estoque                            x          x
--   consultar_movimentacao_estoque               x          x
--   cadastrar_clientes                           x          x
--   editar_clientes                              x          x
--   editar_orcamentos                                       x
--   gerenciar_orcamentos                                    x
--   gerenciar_estoque                                       x
--   administrar_usuarios                                    x
-- ============================================================================

CREATE DATABASE IF NOT EXISTS ecovolts
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ecovolts;

-- Garante que acentos (Amapá, Ceará, "Universitário"...) entrem corretamente
-- mesmo se o cliente MySQL estiver com outro charset padrão.
SET NAMES utf8mb4;

-- ==========================================
-- TABELA Perfil (papéis de usuário)
-- ==========================================

CREATE TABLE IF NOT EXISTS Perfil (
    id_perfil   INT AUTO_INCREMENT PRIMARY KEY,
    nome_perfil VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Usuario
-- ==========================================

CREATE TABLE IF NOT EXISTS Usuario (
    id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
    login          VARCHAR(150) NOT NULL,
    senha          VARCHAR(255) NOT NULL,
    id_perfil      INT,
    email_usuario  VARCHAR(150),
    nome_usuario   VARCHAR(150) NOT NULL,
    cpf_usuario    CHAR(11),
    cnpj_usuario   CHAR(14),
    ativo          TINYINT(1)  NOT NULL DEFAULT 1,
    is_system      TINYINT(1)  NOT NULL DEFAULT 0,
    criado_em      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_usuario_login (login),

    CONSTRAINT FK_Usuario_Perfil
        FOREIGN KEY (id_perfil)
        REFERENCES Perfil (id_perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Estado
-- ==========================================

CREATE TABLE IF NOT EXISTS Estado (
    id_estado    INT AUTO_INCREMENT PRIMARY KEY,
    nome_estado  VARCHAR(100) NOT NULL,
    sigla_estado CHAR(2) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Cidade
-- ==========================================

CREATE TABLE IF NOT EXISTS Cidade (
    id_cidade   INT AUTO_INCREMENT PRIMARY KEY,
    nome_cidade VARCHAR(100) NOT NULL,
    id_estado   INT NOT NULL,

    UNIQUE KEY uq_cidade_estado (nome_cidade, id_estado),

    CONSTRAINT FK_Cidade_Estado
        FOREIGN KEY (id_estado)
        REFERENCES Estado (id_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Bairro
-- ==========================================

CREATE TABLE IF NOT EXISTS Bairro (
    id_bairro   INT AUTO_INCREMENT PRIMARY KEY,
    nome_bairro VARCHAR(100) NOT NULL,
    id_cidade   INT NOT NULL,

    UNIQUE KEY uq_bairro_cidade (nome_bairro, id_cidade),

    CONSTRAINT FK_Bairro_Cidade
        FOREIGN KEY (id_cidade)
        REFERENCES Cidade (id_cidade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Endereco
-- ==========================================

CREATE TABLE IF NOT EXISTS Endereco (
    id_endereco   INT AUTO_INCREMENT PRIMARY KEY,
    logradouro    VARCHAR(150) NOT NULL,
    num_endereco  VARCHAR(20),
    complemento   VARCHAR(100),
    id_bairro     INT NOT NULL,
    cep           CHAR(8),

    CONSTRAINT FK_Endereco_Bairro
        FOREIGN KEY (id_bairro)
        REFERENCES Bairro (id_bairro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA usuario_endereco
-- ==========================================

CREATE TABLE IF NOT EXISTS usuario_endereco (
    id_usuario     INT NOT NULL,
    id_endereco    INT NOT NULL,
    tipo_endereco  VARCHAR(30) DEFAULT 'Residencial',

    PRIMARY KEY (id_usuario, id_endereco),

    CONSTRAINT FK_usuario_endereco_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario (id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT FK_usuario_endereco_endereco
        FOREIGN KEY (id_endereco)
        REFERENCES Endereco (id_endereco)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Produto
-- ==========================================

CREATE TABLE IF NOT EXISTS Produto (
    id_produto         INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto       VARCHAR(150) NOT NULL,
    marca_produto      VARCHAR(100),
    voltagem_produto   INT,
    valorUn_produto    DECIMAL(10,2),
    tam_produto        INT,
    descricao_produto  VARCHAR(500),
    fornecedor         VARCHAR(150)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Estoque
-- ==========================================

CREATE TABLE IF NOT EXISTS Estoque (
    id_estoque  INT AUTO_INCREMENT PRIMARY KEY,
    quantidade  INT NOT NULL DEFAULT 0,
    id_produto  INT NOT NULL,

    CONSTRAINT FK_Estoque_Produto
        FOREIGN KEY (id_produto)
        REFERENCES Produto (id_produto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA MovimentacaoEstoque
-- ==========================================

CREATE TABLE IF NOT EXISTS MovimentacaoEstoque (
    id_movimentacao     INT AUTO_INCREMENT PRIMARY KEY,
    tipo                VARCHAR(30) NOT NULL,
    quantidade          INT NOT NULL,
    data_movimentacao   DATE,
    id_estoque          INT NOT NULL,

    CONSTRAINT FK_MovimentacaoEstoque_Estoque
        FOREIGN KEY (id_estoque)
        REFERENCES Estoque (id_estoque)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Telhado
-- ==========================================

CREATE TABLE IF NOT EXISTS Telhado (
    id_telhado        INT AUTO_INCREMENT PRIMARY KEY,
    area_telhado      DECIMAL(10,2),
    direcao_telhado   VARCHAR(50),
    -- TRUE quando o simulador teve que estimar o valor (cliente respondeu "Não sabe").
    area_estimada     TINYINT(1) NOT NULL DEFAULT 0,
    direcao_estimada  TINYINT(1) NOT NULL DEFAULT 0,
    -- Resultado já calculado no momento da simulação (orcamento-handler.php),
    -- em vez de ser re-derivado depois com as mesmas constantes do modelo de
    -- cálculo duplicadas em outro arquivo.
    comporta_placas   TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Orcamento
-- ==========================================

CREATE TABLE IF NOT EXISTS Orcamento (
    id_orcamento          INT AUTO_INCREMENT PRIMARY KEY,
    num_orcamento         VARCHAR(20) NOT NULL,
    payback               DECIMAL(10,2),
    valor_totalCE         DECIMAL(12,2),
    valor_totalCEPI       DECIMAL(12,2),
    data_emissao          DATE,
    investimento          DECIMAL(12,2),

    id_telhado            INT,
    id_usuario_cliente    INT NOT NULL,
    id_usuario_vendedor   INT NOT NULL,

    CONSTRAINT FK_Orcamento_Telhado
        FOREIGN KEY (id_telhado)
        REFERENCES Telhado (id_telhado),

    CONSTRAINT FK_Orcamento_Cliente
        FOREIGN KEY (id_usuario_cliente)
        REFERENCES Usuario (id_usuario),

    CONSTRAINT FK_Orcamento_Vendedor
        FOREIGN KEY (id_usuario_vendedor)
        REFERENCES Usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Orcamento_Prod
-- ==========================================

CREATE TABLE IF NOT EXISTS Orcamento_Prod (
    id_orcamento    INT NOT NULL,
    id_produto      INT NOT NULL,
    qtd             INT NOT NULL,
    valor_unitario  DECIMAL(10,2),
    desconto        DECIMAL(10,2) DEFAULT 0.00,

    PRIMARY KEY (id_orcamento, id_produto),

    CONSTRAINT FK_Orcamento_Prod_Orcamento
        FOREIGN KEY (id_orcamento)
        REFERENCES Orcamento (id_orcamento)
        ON DELETE CASCADE,

    CONSTRAINT FK_Orcamento_Prod_Produto
        FOREIGN KEY (id_produto)
        REFERENCES Produto (id_produto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Permissao
-- ==========================================

CREATE TABLE IF NOT EXISTS Permissao (
    id_permissao    INT AUTO_INCREMENT PRIMARY KEY,
    nome_permissao  VARCHAR(100) NOT NULL UNIQUE,
    desc_permissao  VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABELA Perfil_Permissao
-- ==========================================

CREATE TABLE IF NOT EXISTS Perfil_Permissao (
    id_perfil     INT NOT NULL,
    id_permissao  INT NOT NULL,

    PRIMARY KEY (id_perfil, id_permissao),

    CONSTRAINT FK_Perfil_Permissao_Perfil
        FOREIGN KEY (id_perfil)
        REFERENCES Perfil (id_perfil),

    CONSTRAINT FK_Perfil_Permissao_Permissao
        FOREIGN KEY (id_permissao)
        REFERENCES Permissao (id_permissao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- DADOS INICIAIS (seed) — todos idempotentes: reexecutar NÃO duplica nada
-- ============================================================================

-- Perfis (papéis)
INSERT INTO Perfil (nome_perfil) VALUES
    ('Cliente'), ('Vendedor'), ('Administrador')
ON DUPLICATE KEY UPDATE nome_perfil = nome_perfil;

-- Estados brasileiros
INSERT INTO Estado (nome_estado, sigla_estado) VALUES
    ('Acre','AC'), ('Alagoas','AL'), ('Amapá','AP'), ('Amazonas','AM'),
    ('Bahia','BA'), ('Ceará','CE'), ('Distrito Federal','DF'), ('Espírito Santo','ES'),
    ('Goiás','GO'), ('Maranhão','MA'), ('Mato Grosso','MT'), ('Mato Grosso do Sul','MS'),
    ('Minas Gerais','MG'), ('Pará','PA'), ('Paraíba','PB'), ('Paraná','PR'),
    ('Pernambuco','PE'), ('Piauí','PI'), ('Rio de Janeiro','RJ'), ('Rio Grande do Norte','RN'),
    ('Rio Grande do Sul','RS'), ('Rondônia','RO'), ('Roraima','RR'), ('Santa Catarina','SC'),
    ('São Paulo','SP'), ('Sergipe','SE'), ('Tocantins','TO')
ON DUPLICATE KEY UPDATE nome_estado = nome_estado;

-- Sede da EcoVolts: Araras - SP (ver endereço no rodapé do site)
INSERT INTO Cidade (nome_cidade, id_estado)
SELECT 'Araras', id_estado FROM Estado WHERE sigla_estado = 'SP'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;

INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Jardim Universitário', id_cidade FROM Cidade WHERE nome_cidade = 'Araras'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;

-- ----------------------------------------------------------------------------
-- Catálogo básico de produtos (usado para compor os itens do orçamento).
-- Produto não tem chave única, então cada INSERT só acontece se ainda não
-- existir um produto com o mesmo nome (senão cada reexecução duplicaria).
-- ----------------------------------------------------------------------------
INSERT INTO Produto (nome_produto, marca_produto, voltagem_produto, valorUn_produto, tam_produto, descricao_produto, fornecedor)
SELECT 'Painel Solar 550W Mono', 'EcoVolts Solar', 48, 899.90, 550,
       'Painel fotovoltaico monocristalino 550W, usado como referência de preço unitário nos orçamentos gerados pelo simulador.',
       'EcoVolts Distribuidora'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM Produto WHERE nome_produto = 'Painel Solar 550W Mono');

INSERT INTO Produto (nome_produto, marca_produto, voltagem_produto, valorUn_produto, tam_produto, descricao_produto, fornecedor)
SELECT 'Inversor Solar 5kWp', 'EcoVolts Solar', 220, 3499.00, NULL,
       'Inversor grid-tie 5kWp, string única.',
       'EcoVolts Distribuidora'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM Produto WHERE nome_produto = 'Inversor Solar 5kWp');

INSERT INTO Produto (nome_produto, marca_produto, voltagem_produto, valorUn_produto, tam_produto, descricao_produto, fornecedor)
SELECT 'Estrutura de Fixação (telhado cerâmico)', 'EcoVolts Solar', NULL, 159.90, NULL,
       'Kit de fixação por placa para telhado cerâmico.',
       'EcoVolts Distribuidora'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM Produto WHERE nome_produto = 'Estrutura de Fixação (telhado cerâmico)');

-- Estoque inicial (quantidades ilustrativas; ajuste conforme o estoque real).
-- Só cria a linha de estoque se o produto ainda não tiver nenhuma — nunca
-- sobrescreve uma quantidade que já foi alterada.
INSERT INTO Estoque (quantidade, id_produto)
SELECT 400, p.id_produto FROM Produto p
WHERE p.nome_produto = 'Painel Solar 550W Mono'
  AND NOT EXISTS (SELECT 1 FROM Estoque e WHERE e.id_produto = p.id_produto);

INSERT INTO Estoque (quantidade, id_produto)
SELECT 40, p.id_produto FROM Produto p
WHERE p.nome_produto = 'Inversor Solar 5kWp'
  AND NOT EXISTS (SELECT 1 FROM Estoque e WHERE e.id_produto = p.id_produto);

INSERT INTO Estoque (quantidade, id_produto)
SELECT 1000, p.id_produto FROM Produto p
WHERE p.nome_produto = 'Estrutura de Fixação (telhado cerâmico)'
  AND NOT EXISTS (SELECT 1 FROM Estoque e WHERE e.id_produto = p.id_produto);

-- Registra a entrada inicial de estoque — apenas dos 3 produtos acima e apenas
-- se o estoque deles ainda não tiver nenhuma movimentação registrada.
INSERT INTO MovimentacaoEstoque (tipo, quantidade, data_movimentacao, id_estoque)
SELECT 'entrada', e.quantidade, CURDATE(), e.id_estoque
FROM Estoque e
JOIN Produto p ON p.id_produto = e.id_produto
WHERE p.nome_produto IN ('Painel Solar 550W Mono', 'Inversor Solar 5kWp', 'Estrutura de Fixação (telhado cerâmico)')
  AND NOT EXISTS (SELECT 1 FROM MovimentacaoEstoque m WHERE m.id_estoque = e.id_estoque);

-- ----------------------------------------------------------------------------
-- Permissões e associação por perfil
-- (inclui o que antes estava em migration-vendedores.sql)
-- ----------------------------------------------------------------------------
INSERT INTO Permissao (nome_permissao, desc_permissao) VALUES
    ('simular_orcamento',             'Simular um novo orçamento de energia solar'),
    ('ver_relatorio',                 'Ver o relatório/resultado de um orçamento'),
    ('editar_perfil',                 'Editar os próprios dados cadastrais'),
    ('gerenciar_orcamentos',          'Gerenciar orçamentos de todos os clientes'),
    ('editar_orcamentos',             'Editar o histórico de orçamentos'),
    ('consultar_orcamentos',          'Consultar orçamentos de todos os clientes'),
    ('consultar_clientes',            'Consultar clientes e seus históricos de orçamentos'),
    ('cadastrar_clientes',            'Cadastrar novos clientes'),
    ('editar_clientes',               'Editar os dados cadastrais dos clientes (nome, contato, CPF/CNPJ e endereço)'),
    ('gerenciar_estoque',             'Editar produtos, ajustar estoque e gerenciar movimentações'),
    ('consultar_estoque',             'Consultar o estoque de produtos'),
    ('consultar_movimentacao_estoque','Consultar a movimentação do estoque'),
    ('administrar_usuarios',          'Criar, editar e desativar usuários do sistema')
ON DUPLICATE KEY UPDATE desc_permissao = VALUES(desc_permissao);

-- Cliente: simula orçamento, vê o próprio relatório e edita o próprio perfil.
INSERT INTO Perfil_Permissao (id_perfil, id_permissao)
SELECT p.id_perfil, perm.id_permissao
FROM Perfil p, Permissao perm
WHERE p.nome_perfil = 'Cliente'
  AND perm.nome_permissao IN ('simular_orcamento', 'ver_relatorio', 'editar_perfil')
ON DUPLICATE KEY UPDATE id_perfil = VALUES(id_perfil);

-- Vendedor: o mesmo do Cliente + consulta nas áreas comerciais + cadastro e
-- edição de clientes (sem permissão de estoque além de consulta).
INSERT INTO Perfil_Permissao (id_perfil, id_permissao)
SELECT p.id_perfil, perm.id_permissao
FROM Perfil p, Permissao perm
WHERE p.nome_perfil = 'Vendedor'
  AND perm.nome_permissao IN (
      'simular_orcamento',
      'ver_relatorio',
      'editar_perfil',
      'consultar_orcamentos',
      'consultar_clientes',
      'cadastrar_clientes',
      'editar_clientes',
      'consultar_estoque',
      'consultar_movimentacao_estoque'
  )
ON DUPLICATE KEY UPDATE id_perfil = VALUES(id_perfil);

-- Administrador: todas as permissões (inclusive as que forem criadas no futuro
-- e reexecutando este arquivo).
INSERT INTO Perfil_Permissao (id_perfil, id_permissao)
SELECT p.id_perfil, perm.id_permissao
FROM Perfil p, Permissao perm
WHERE p.nome_perfil = 'Administrador'
ON DUPLICATE KEY UPDATE id_perfil = VALUES(id_perfil);

-- ----------------------------------------------------------------------------
-- Revogação (vinda do migration-vendedores.sql): em bancos criados pela versão
-- anterior do schema, o Vendedor recebia `gerenciar_orcamentos`. Ele passa a
-- ser só consulta e a edição fica com o Administrador.
--
-- IMPORTANTE: como este arquivo pode ser reexecutado, esta limpeza também roda
-- de novo — se você conceder manualmente `gerenciar_orcamentos` ou
-- `gerenciar_estoque` ao Vendedor, reexecutar o arquivo vai revogar. Se quiser
-- mudar essa política, altere as permissões acima e remova este bloco.
-- (WHERE em colunas-chave: também funciona com o "safe update mode" do Workbench.)
-- ----------------------------------------------------------------------------
DELETE FROM Perfil_Permissao
WHERE id_perfil = (SELECT id_perfil FROM Perfil WHERE nome_perfil = 'Vendedor')
  AND id_permissao IN (
      SELECT id_permissao FROM Permissao
      WHERE nome_permissao IN ('gerenciar_orcamentos', 'gerenciar_estoque')
  );

-- ----------------------------------------------------------------------------
-- Conta de sistema "Vendedor Online": usada como vendedor nos orçamentos
-- gerados pelo simulador do site, até que um vendedor humano assuma o caso.
-- `is_system = 1` faz login-handler.php recusar login nessa conta mesmo que
-- alguém descubra/adivinhe uma senha (ela nasce com um hash aleatório
-- descartável, ninguém sabe a senha de qualquer forma).
-- ----------------------------------------------------------------------------
INSERT INTO Usuario (login, senha, id_perfil, email_usuario, nome_usuario, ativo, is_system)
SELECT
    'vendedor.online@ecovolts.com.br',
    '$2y$10$QjkBp8Kv3koCfUXGagFM1.XWolBXMG85pObX22bGDD4z1kyvLEXDC',
    (SELECT id_perfil FROM Perfil WHERE nome_perfil = 'Vendedor'),
    'vendedor.online@ecovolts.com.br',
    'Vendedor Online (Simulador)',
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM Usuario WHERE login = 'vendedor.online@ecovolts.com.br'
);

CREATE USER 'ecovolts_app'@'localhost' IDENTIFIED BY 'segredo';
GRANT SELECT, INSERT, UPDATE, DELETE ON ecovolts.* TO 'ecovolts_app'@'localhost';
FLUSH PRIVILEGES;

-- C:\xampp\php\php.exe database\criar-admin.php admin@admin.com "admin" "admin123" 