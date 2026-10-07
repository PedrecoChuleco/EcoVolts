CREATE DATABASE IF NOT EXISTS ecovolts
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ecovolts;


-- ==========================================
-- TABELA Perfil
-- ==========================================

CREATE TABLE Perfil (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    nome_perfil VARCHAR(50) NOT NULL
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Usuario
-- ==========================================

CREATE TABLE Usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    id_perfil INT,
    email_usuario VARCHAR(150),
    nome_usuario VARCHAR(150) NOT NULL,
    cpf_usuario CHAR(11),
    cnpj_usuario CHAR(14),

    CONSTRAINT FK_Usuario_Perfil
        FOREIGN KEY (id_perfil)
        REFERENCES Perfil (id_perfil)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Estado
-- ==========================================

CREATE TABLE Estado (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nome_estado VARCHAR(100) NOT NULL,
    sigla_estado CHAR(2) NOT NULL
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Cidade
-- ==========================================

CREATE TABLE Cidade (
    id_cidade INT AUTO_INCREMENT PRIMARY KEY,
    nome_cidade VARCHAR(100) NOT NULL,
    id_estado INT NOT NULL,

    CONSTRAINT FK_Cidade_Estado
        FOREIGN KEY (id_estado)
        REFERENCES Estado (id_estado)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Bairro
-- ==========================================

CREATE TABLE Bairro (
    id_bairro INT AUTO_INCREMENT PRIMARY KEY,
    nome_bairro VARCHAR(100) NOT NULL,
    id_cidade INT NOT NULL,

    CONSTRAINT FK_Bairro_Cidade
        FOREIGN KEY (id_cidade)
        REFERENCES Cidade (id_cidade)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Endereco
-- ==========================================

CREATE TABLE Endereco (
    id_endereco INT AUTO_INCREMENT PRIMARY KEY,
    logradouro VARCHAR(150) NOT NULL,
    num_endereco VARCHAR(20),
    complemento VARCHAR(100),
    id_bairro INT NOT NULL,
    cep CHAR(8),

    CONSTRAINT FK_Endereco_Bairro
        FOREIGN KEY (id_bairro)
        REFERENCES Bairro (id_bairro)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Produto
-- ==========================================

CREATE TABLE Produto (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome_produto VARCHAR(150) NOT NULL,
    marca_produto VARCHAR(100),
    voltagem_produto INT,
    valorUn_produto DECIMAL(10,2),
    tam_produto INT,
    descricao_produto VARCHAR(500),
    fornecedor VARCHAR(150)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Estoque
-- ==========================================

CREATE TABLE Estoque (
    id_estoque INT AUTO_INCREMENT PRIMARY KEY,
    quantidade INT NOT NULL DEFAULT 0,
    id_produto INT NOT NULL,

    CONSTRAINT FK_Estoque_Produto
        FOREIGN KEY (id_produto)
        REFERENCES Produto (id_produto)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Telhado
-- ==========================================

CREATE TABLE Telhado (
    id_telhado INT AUTO_INCREMENT PRIMARY KEY,
    area_telhado DECIMAL(10,2),
    direcao_telhado VARCHAR(50)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Orcamento
-- ==========================================

CREATE TABLE Orcamento (
    id_orcamento INT AUTO_INCREMENT PRIMARY KEY,
    num_orcamento VARCHAR(20) NOT NULL,
    payback DECIMAL(10,2),
    valor_totalCE DECIMAL(12,2),
    valor_totalCEPI DECIMAL(12,2),
    data_emissao DATE,
    investimento DECIMAL(12,2),

    id_telhado INT,
    id_usuario_cliente INT NOT NULL,
    id_usuario_vendedor INT NOT NULL,

    CONSTRAINT FK_Orcamento_Telhado
        FOREIGN KEY (id_telhado)
        REFERENCES Telhado (id_telhado),

    CONSTRAINT FK_Orcamento_Cliente
        FOREIGN KEY (id_usuario_cliente)
        REFERENCES Usuario (id_usuario),

    CONSTRAINT FK_Orcamento_Vendedor
        FOREIGN KEY (id_usuario_vendedor)
        REFERENCES Usuario (id_usuario)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA MovimentacaoEstoque
-- ==========================================

CREATE TABLE MovimentacaoEstoque (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(30) NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATE,
    id_estoque INT NOT NULL,

    CONSTRAINT FK_MovimentacaoEstoque_Estoque
        FOREIGN KEY (id_estoque)
        REFERENCES Estoque (id_estoque)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Permissao
-- ==========================================

CREATE TABLE Permissao (
    id_permissao INT AUTO_INCREMENT PRIMARY KEY,
    nome_permissao VARCHAR(100) NOT NULL,
    desc_permissao VARCHAR(255)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Perfil_Permissao
-- ==========================================

CREATE TABLE Perfil_Permissao (
    id_perfil INT NOT NULL,
    id_permissao INT NOT NULL,

    PRIMARY KEY (id_perfil, id_permissao),

    CONSTRAINT FK_Perfil_Permissao_Perfil
        FOREIGN KEY (id_perfil)
        REFERENCES Perfil (id_perfil),

    CONSTRAINT FK_Perfil_Permissao_Permissao
        FOREIGN KEY (id_permissao)
        REFERENCES Permissao (id_permissao)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA usuario_endereco
-- ==========================================

CREATE TABLE usuario_endereco (
    id_usuario INT NOT NULL,
    id_endereco INT NOT NULL,
    tipo_endereco VARCHAR(30),

    PRIMARY KEY (id_usuario, id_endereco),

    CONSTRAINT FK_usuario_endereco_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario (id_usuario),

    CONSTRAINT FK_usuario_endereco_endereco
        FOREIGN KEY (id_endereco)
        REFERENCES Endereco (id_endereco)
) ENGINE=InnoDB;


-- ==========================================
-- TABELA Orcamento_Prod
-- ==========================================

CREATE TABLE Orcamento_Prod (
    id_orcamento INT NOT NULL,
    id_produto INT NOT NULL,
    qtd INT NOT NULL,
    valor_unitario DECIMAL(10,2),
    desconto DECIMAL(10,2),

    PRIMARY KEY (id_orcamento, id_produto),

    CONSTRAINT FK_Orcamento_Prod_Orcamento
        FOREIGN KEY (id_orcamento)
        REFERENCES Orcamento (id_orcamento),

    CONSTRAINT FK_Orcamento_Prod_Produto
        FOREIGN KEY (id_produto)
        REFERENCES Produto (id_produto)
) ENGINE=InnoDB;