-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 04/11/2026 às 23:16
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ecovolts`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `bairro`
--

CREATE TABLE `bairro` (
  `id_bairro` int(11) NOT NULL,
  `nome_bairro` varchar(100) NOT NULL,
  `id_cidade` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `bairro`
--

INSERT INTO `bairro` (`id_bairro`, `nome_bairro`, `id_cidade`) VALUES
(2, 'Centro', 2),
(4, 'Centro', 4),
(5, 'Centro', 5),
(6, 'Centro', 6),
(1, 'Jardim Universitário', 1),
(7, 'Rodinei', 7),
(3, 'Serelepe', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cidade`
--

CREATE TABLE `cidade` (
  `id_cidade` int(11) NOT NULL,
  `nome_cidade` varchar(100) NOT NULL,
  `id_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cidade`
--

INSERT INTO `cidade` (`id_cidade`, `nome_cidade`, `id_estado`) VALUES
(1, 'Araras', 25),
(5, 'b9', 5),
(2, 'Cordeiro', 25),
(4, 'Dinossauro', 1),
(3, 'Leme', 25),
(6, 'Longe', 7),
(7, 'Pintopolis', 21);

-- --------------------------------------------------------

--
-- Estrutura para tabela `endereco`
--

CREATE TABLE `endereco` (
  `id_endereco` int(11) NOT NULL,
  `logradouro` varchar(150) NOT NULL,
  `num_endereco` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `id_bairro` int(11) NOT NULL,
  `cep` char(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `endereco`
--

INSERT INTO `endereco` (`id_endereco`, `logradouro`, `num_endereco`, `complemento`, `id_bairro`, `cep`) VALUES
(1, 'abc', '123', 'acbde', 2, '13490021'),
(2, 'Casa', '70', NULL, 3, '13611215'),
(3, 'Muito longe', '1000', NULL, 4, '13131313'),
(4, 'abcd', '321', NULL, 5, '98765432'),
(5, 'abcd', '987', 'acbde', 6, '12345678'),
(6, 'Não da', '9', NULL, 7, '12345678');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estado`
--

CREATE TABLE `estado` (
  `id_estado` int(11) NOT NULL,
  `nome_estado` varchar(100) NOT NULL,
  `sigla_estado` char(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estado`
--

INSERT INTO `estado` (`id_estado`, `nome_estado`, `sigla_estado`) VALUES
(1, 'Acre', 'AC'),
(2, 'Alagoas', 'AL'),
(3, 'Amapá', 'AP'),
(4, 'Amazonas', 'AM'),
(5, 'Bahia', 'BA'),
(6, 'Ceará', 'CE'),
(7, 'Distrito Federal', 'DF'),
(8, 'Espírito Santo', 'ES'),
(9, 'Goiás', 'GO'),
(10, 'Maranhão', 'MA'),
(11, 'Mato Grosso', 'MT'),
(12, 'Mato Grosso do Sul', 'MS'),
(13, 'Minas Gerais', 'MG'),
(14, 'Pará', 'PA'),
(15, 'Paraíba', 'PB'),
(16, 'Paraná', 'PR'),
(17, 'Pernambuco', 'PE'),
(18, 'Piauí', 'PI'),
(19, 'Rio de Janeiro', 'RJ'),
(20, 'Rio Grande do Norte', 'RN'),
(21, 'Rio Grande do Sul', 'RS'),
(22, 'Rondônia', 'RO'),
(23, 'Roraima', 'RR'),
(24, 'Santa Catarina', 'SC'),
(25, 'São Paulo', 'SP'),
(26, 'Sergipe', 'SE'),
(27, 'Tocantins', 'TO');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id_estoque` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 0,
  `id_produto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estoque`
--

INSERT INTO `estoque` (`id_estoque`, `quantidade`, `id_produto`) VALUES
(1, 400, 1),
(2, 40, 2),
(3, 1000, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentacaoestoque`
--

CREATE TABLE `movimentacaoestoque` (
  `id_movimentacao` int(11) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `data_movimentacao` date DEFAULT NULL,
  `id_estoque` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentacaoestoque`
--

INSERT INTO `movimentacaoestoque` (`id_movimentacao`, `tipo`, `quantidade`, `data_movimentacao`, `id_estoque`) VALUES
(1, 'entrada', 400, '2026-10-28', 1),
(2, 'entrada', 40, '2026-10-28', 2),
(3, 'entrada', 1000, '2026-10-28', 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamento`
--

CREATE TABLE `orcamento` (
  `id_orcamento` int(11) NOT NULL,
  `num_orcamento` varchar(20) NOT NULL,
  `payback` decimal(10,2) DEFAULT NULL,
  `valor_totalCE` decimal(12,2) DEFAULT NULL,
  `valor_totalCEPI` decimal(12,2) DEFAULT NULL,
  `data_emissao` date DEFAULT NULL,
  `investimento` decimal(12,2) DEFAULT NULL,
  `padrao_ligacao` varchar(20) DEFAULT NULL,
  `padrao_estimado` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'aberto',
  `id_telhado` int(11) DEFAULT NULL,
  `id_usuario_cliente` int(11) NOT NULL,
  `id_usuario_vendedor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `orcamento`
--

INSERT INTO `orcamento` (`id_orcamento`, `num_orcamento`, `payback`, `valor_totalCE`, `valor_totalCEPI`, `data_emissao`, `investimento`, `padrao_ligacao`, `padrao_estimado`, `status`, `id_telhado`, `id_usuario_cliente`, `id_usuario_vendedor`) VALUES
(1, 'ORC-000001', 34.50, 450.00, 39.46, '2026-10-28', 14178.20, 'Bifasico', 0, 'recusado', 1, 3, 2),
(2, 'ORC-000002', 44.20, 300.00, 39.46, '2026-10-28', 11508.40, 'Bifasico', 0, 'aberto', 2, 4, 2),
(3, 'ORC-000003', 51.10, 500.00, 23.68, '2026-10-28', 24351.70, 'Monofasico', 0, 'enviado', 3, 5, 1),
(4, 'ORC-000004', 42.00, 250.00, 39.46, '2026-10-28', 8838.60, 'Bifasico', 1, 'negociacao', 4, 6, 1),
(5, 'ORC-000005', 36.80, 2500.00, 78.93, '2026-10-28', 89073.90, 'Trifasico', 0, 'aprovado', 5, 7, 2),
(6, 'ORC-000006', 46.30, 5000.00, 4809.26, '2026-10-28', 8838.60, 'Monofasico', 0, 'recusado', 6, 8, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamento_prod`
--

CREATE TABLE `orcamento_prod` (
  `id_orcamento` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `qtd` int(11) NOT NULL,
  `valor_unitario` decimal(10,2) DEFAULT NULL,
  `desconto` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `orcamento_prod`
--

INSERT INTO `orcamento_prod` (`id_orcamento`, `id_produto`, `qtd`, `valor_unitario`, `desconto`) VALUES
(1, 1, 8, 899.90, 0.00),
(1, 2, 1, 3499.00, 0.00),
(1, 4, 80, 8.50, 0.00),
(1, 5, 8, 350.00, 0.00),
(2, 1, 6, 899.90, 0.00),
(2, 2, 1, 3499.00, 0.00),
(2, 4, 60, 8.50, 0.00),
(2, 5, 6, 350.00, 0.00),
(3, 1, 13, 899.90, 0.00),
(3, 2, 2, 3499.00, 0.00),
(3, 4, 130, 8.50, 0.00),
(3, 5, 13, 350.00, 0.00),
(4, 1, 4, 899.90, 0.00),
(4, 2, 1, 3499.00, 0.00),
(4, 4, 40, 8.50, 0.00),
(4, 5, 4, 350.00, 0.00),
(5, 1, 51, 899.90, 0.00),
(5, 2, 6, 3499.00, 0.00),
(5, 4, 510, 8.50, 0.00),
(5, 5, 51, 350.00, 0.00),
(6, 1, 4, 899.90, 0.00),
(6, 2, 1, 3499.00, 0.00),
(6, 4, 40, 8.50, 0.00),
(6, 5, 4, 350.00, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfil`
--

CREATE TABLE `perfil` (
  `id_perfil` int(11) NOT NULL,
  `nome_perfil` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `perfil`
--

INSERT INTO `perfil` (`id_perfil`, `nome_perfil`) VALUES
(3, 'Administrador'),
(1, 'Cliente'),
(2, 'Vendedor');

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfil_permissao`
--

CREATE TABLE `perfil_permissao` (
  `id_perfil` int(11) NOT NULL,
  `id_permissao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `perfil_permissao`
--

INSERT INTO `perfil_permissao` (`id_perfil`, `id_permissao`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(2, 3),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 11),
(2, 12),
(2, 14),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14);

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissao`
--

CREATE TABLE `permissao` (
  `id_permissao` int(11) NOT NULL,
  `nome_permissao` varchar(100) NOT NULL,
  `desc_permissao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `permissao`
--

INSERT INTO `permissao` (`id_permissao`, `nome_permissao`, `desc_permissao`) VALUES
(1, 'simular_orcamento', 'Simular um novo orçamento de energia solar'),
(2, 'ver_relatorio', 'Ver o relatório/resultado de um orçamento'),
(3, 'editar_perfil', 'Editar os próprios dados cadastrais'),
(4, 'gerenciar_orcamentos', 'Gerenciar orçamentos de todos os clientes'),
(5, 'editar_orcamentos', 'Editar o histórico de orçamentos'),
(6, 'consultar_orcamentos', 'Consultar orçamentos de todos os clientes'),
(7, 'consultar_clientes', 'Consultar clientes e seus históricos de orçamentos'),
(8, 'cadastrar_clientes', 'Cadastrar novos clientes'),
(9, 'editar_clientes', 'Editar os dados cadastrais dos clientes (nome, contato, CPF/CNPJ e endereço)'),
(10, 'gerenciar_estoque', 'Editar produtos, ajustar estoque e gerenciar movimentações'),
(11, 'consultar_estoque', 'Consultar o estoque de produtos'),
(12, 'consultar_movimentacao_estoque', 'Consultar a movimentação do estoque'),
(13, 'administrar_usuarios', 'Criar, editar e desativar usuários do sistema'),
(14, 'alterar_status_orcamentos', 'Alterar o status dos orçamentos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

CREATE TABLE `produto` (
  `id_produto` int(11) NOT NULL,
  `nome_produto` varchar(150) NOT NULL,
  `marca_produto` varchar(100) DEFAULT NULL,
  `voltagem_produto` int(11) DEFAULT NULL,
  `valorUn_produto` decimal(10,2) DEFAULT NULL,
  `tam_produto` int(11) DEFAULT NULL,
  `area_m2` decimal(5,2) DEFAULT NULL,
  `descricao_produto` varchar(500) DEFAULT NULL,
  `fornecedor` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto`
--

INSERT INTO `produto` (`id_produto`, `nome_produto`, `marca_produto`, `voltagem_produto`, `valorUn_produto`, `tam_produto`, `area_m2`, `descricao_produto`, `fornecedor`) VALUES
(1, 'Painel Solar 550W Mono', 'EcoVolts Solar', 48, 899.90, 550, 2.50, 'Painel fotovoltaico monocristalino 550W, usado como referência de preço unitário nos orçamentos gerados pelo simulador.', 'EcoVolts Distribuidora'),
(2, 'Inversor Solar 5kWp', 'EcoVolts Solar', 220, 3499.00, 5000, NULL, 'Inversor grid-tie 5kWp, string única.', 'EcoVolts Distribuidora'),
(3, 'Estrutura de Fixação (telhado cerâmico)', 'EcoVolts Solar', NULL, 159.90, NULL, NULL, 'Kit de fixação por placa para telhado cerâmico.', 'EcoVolts Distribuidora'),
(4, 'Cabo Solar 6 mm (por metro)', 'EcoVolts Solar', NULL, 8.50, NULL, NULL, 'Cabo solar 6 mm, cobrado por metro instalado.', 'EcoVolts Distribuidora'),
(5, 'Mão de Obra de Instalação (por placa)', 'EcoVolts', NULL, 350.00, NULL, NULL, 'Serviço de instalação, cobrado por placa instalada.', 'EcoVolts');

-- --------------------------------------------------------

--
-- Estrutura para tabela `telhado`
--

CREATE TABLE `telhado` (
  `id_telhado` int(11) NOT NULL,
  `area_telhado` decimal(10,2) DEFAULT NULL,
  `direcao_telhado` varchar(50) DEFAULT NULL,
  `area_estimada` tinyint(1) NOT NULL DEFAULT 0,
  `direcao_estimada` tinyint(1) NOT NULL DEFAULT 0,
  `comporta_placas` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `telhado`
--

INSERT INTO `telhado` (`id_telhado`, `area_telhado`, `direcao_telhado`, `area_estimada`, `direcao_estimada`, `comporta_placas`) VALUES
(1, 75.00, 'Norte', 0, 0, 1),
(2, 50.00, 'Leste', 0, 0, 1),
(3, 50.00, 'Sul', 0, 0, 1),
(4, NULL, 'Norte', 1, 1, 1),
(5, 1000.00, 'Oeste', 0, 0, 1),
(6, 10.00, 'Leste', 0, 0, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `login` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `id_perfil` int(11) DEFAULT NULL,
  `email_usuario` varchar(150) DEFAULT NULL,
  `nome_usuario` varchar(150) NOT NULL,
  `cpf_usuario` char(11) DEFAULT NULL,
  `cnpj_usuario` char(14) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `login`, `senha`, `id_perfil`, `email_usuario`, `nome_usuario`, `cpf_usuario`, `cnpj_usuario`, `ativo`, `is_system`, `criado_em`) VALUES
(1, 'vendedor.online@ecovolts.com.br', '$2y$10$QjkBp8Kv3koCfUXGagFM1.XWolBXMG85pObX22bGDD4z1kyvLEXDC', 2, 'vendedor.online@ecovolts.com.br', 'Vendedor Online (Simulador)', NULL, NULL, 1, 1, '2026-10-28 22:55:16'),
(2, 'admin@admin.com', '$2y$10$WL6WkBdW4xvXHPRtFpQp6O..CufwzwMGfJlecCgBKk2HZusmqTIcC', 3, 'admin@admin.com', 'admin', NULL, NULL, 1, 0, '2026-10-28 23:01:36'),
(3, 'estevan.moraes@alunos.fho.edu.br', '$2y$10$0W3/dNcZyHxSuNDj1mVT.eQbgwDi75JnTZPOIl4URq3huU.CPUnX6', 1, 'estevan.moraes@alunos.fho.edu.br', 'Estevan Granusso', NULL, NULL, 1, 0, '2026-10-28 23:09:55'),
(4, 'kauanvinicius010@alunos.fho.edu.br', '$2y$10$7hTK9oIxk6S3ohDyNzGmgO5s5/ZYtghYGZ/cE9N1.YwKvjXK1oSkO', 1, 'kauanvinicius010@alunos.fho.edu.br', 'Kauan Vinicius da Silva Rodrigues', '48553900894', NULL, 1, 0, '2026-10-28 23:16:09'),
(5, 'LH0@teste.com', '$2y$10$q3mvDdPviZRHLCknA6sh6uZWXA2MZafggSmB9h8Xd8hHhxeRNU3ae', 1, 'LH0@teste.com', 'LH', '12345678910', NULL, 1, 0, '2026-10-28 23:22:03'),
(6, 'LH1@teste.com', '$2y$10$br/ADy6OF3mTF2xUY3sn5eywl75mAkEaA/dBp6wN9VqOfqgjgWOAK', 1, 'LH1@teste.com', 'LH1@teste.com', NULL, '23456789101213', 1, 0, '2026-10-28 23:26:02'),
(7, 'LH2@teste.com', '$2y$10$NpJ3ig.RmJhFYpHsMWvKU.5stDYD8cZMYDARdOiNlNAp6FYG8pG0y', 1, 'LH2@teste.com', 'LH', NULL, '12345678910111', 1, 0, '2026-10-28 23:31:32'),
(8, 'Impossivel@teste.com', '$2y$10$N33dzn3mE88NFSpfKzQTvOGDkUJT7dlPDXLAJsEDiZh7ILc3Zw95a', 1, 'Impossivel@teste.com', 'Impossivel', NULL, '98765432101234', 1, 0, '2026-10-28 23:36:14');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_endereco`
--

CREATE TABLE `usuario_endereco` (
  `id_usuario` int(11) NOT NULL,
  `id_endereco` int(11) NOT NULL,
  `tipo_endereco` varchar(30) DEFAULT 'Residencial'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuario_endereco`
--

INSERT INTO `usuario_endereco` (`id_usuario`, `id_endereco`, `tipo_endereco`) VALUES
(3, 1, 'Residencial'),
(4, 2, 'Residencial'),
(5, 3, 'Residencial'),
(6, 4, 'Residencial'),
(7, 5, 'Residencial'),
(8, 6, 'Residencial');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `bairro`
--
ALTER TABLE `bairro`
  ADD PRIMARY KEY (`id_bairro`),
  ADD UNIQUE KEY `uq_bairro_cidade` (`nome_bairro`,`id_cidade`),
  ADD KEY `FK_Bairro_Cidade` (`id_cidade`);

--
-- Índices de tabela `cidade`
--
ALTER TABLE `cidade`
  ADD PRIMARY KEY (`id_cidade`),
  ADD UNIQUE KEY `uq_cidade_estado` (`nome_cidade`,`id_estado`),
  ADD KEY `FK_Cidade_Estado` (`id_estado`);

--
-- Índices de tabela `endereco`
--
ALTER TABLE `endereco`
  ADD PRIMARY KEY (`id_endereco`),
  ADD KEY `FK_Endereco_Bairro` (`id_bairro`);

--
-- Índices de tabela `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`),
  ADD UNIQUE KEY `sigla_estado` (`sigla_estado`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id_estoque`),
  ADD KEY `FK_Estoque_Produto` (`id_produto`);

--
-- Índices de tabela `movimentacaoestoque`
--
ALTER TABLE `movimentacaoestoque`
  ADD PRIMARY KEY (`id_movimentacao`),
  ADD KEY `FK_MovimentacaoEstoque_Estoque` (`id_estoque`);

--
-- Índices de tabela `orcamento`
--
ALTER TABLE `orcamento`
  ADD PRIMARY KEY (`id_orcamento`),
  ADD KEY `FK_Orcamento_Telhado` (`id_telhado`),
  ADD KEY `FK_Orcamento_Cliente` (`id_usuario_cliente`),
  ADD KEY `FK_Orcamento_Vendedor` (`id_usuario_vendedor`);

--
-- Índices de tabela `orcamento_prod`
--
ALTER TABLE `orcamento_prod`
  ADD PRIMARY KEY (`id_orcamento`,`id_produto`),
  ADD KEY `FK_Orcamento_Prod_Produto` (`id_produto`);

--
-- Índices de tabela `perfil`
--
ALTER TABLE `perfil`
  ADD PRIMARY KEY (`id_perfil`),
  ADD UNIQUE KEY `nome_perfil` (`nome_perfil`);

--
-- Índices de tabela `perfil_permissao`
--
ALTER TABLE `perfil_permissao`
  ADD PRIMARY KEY (`id_perfil`,`id_permissao`),
  ADD KEY `FK_Perfil_Permissao_Permissao` (`id_permissao`);

--
-- Índices de tabela `permissao`
--
ALTER TABLE `permissao`
  ADD PRIMARY KEY (`id_permissao`),
  ADD UNIQUE KEY `nome_permissao` (`nome_permissao`);

--
-- Índices de tabela `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`id_produto`);

--
-- Índices de tabela `telhado`
--
ALTER TABLE `telhado`
  ADD PRIMARY KEY (`id_telhado`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuario_login` (`login`),
  ADD KEY `FK_Usuario_Perfil` (`id_perfil`);

--
-- Índices de tabela `usuario_endereco`
--
ALTER TABLE `usuario_endereco`
  ADD PRIMARY KEY (`id_usuario`,`id_endereco`),
  ADD KEY `FK_usuario_endereco_endereco` (`id_endereco`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `bairro`
--
ALTER TABLE `bairro`
  MODIFY `id_bairro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `cidade`
--
ALTER TABLE `cidade`
  MODIFY `id_cidade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `endereco`
--
ALTER TABLE `endereco`
  MODIFY `id_endereco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id_estoque` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `movimentacaoestoque`
--
ALTER TABLE `movimentacaoestoque`
  MODIFY `id_movimentacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `orcamento`
--
ALTER TABLE `orcamento`
  MODIFY `id_orcamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `perfil`
--
ALTER TABLE `perfil`
  MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `permissao`
--
ALTER TABLE `permissao`
  MODIFY `id_permissao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `produto`
--
ALTER TABLE `produto`
  MODIFY `id_produto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `telhado`
--
ALTER TABLE `telhado`
  MODIFY `id_telhado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `bairro`
--
ALTER TABLE `bairro`
  ADD CONSTRAINT `FK_Bairro_Cidade` FOREIGN KEY (`id_cidade`) REFERENCES `cidade` (`id_cidade`);

--
-- Restrições para tabelas `cidade`
--
ALTER TABLE `cidade`
  ADD CONSTRAINT `FK_Cidade_Estado` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id_estado`);

--
-- Restrições para tabelas `endereco`
--
ALTER TABLE `endereco`
  ADD CONSTRAINT `FK_Endereco_Bairro` FOREIGN KEY (`id_bairro`) REFERENCES `bairro` (`id_bairro`);

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `FK_Estoque_Produto` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`);

--
-- Restrições para tabelas `movimentacaoestoque`
--
ALTER TABLE `movimentacaoestoque`
  ADD CONSTRAINT `FK_MovimentacaoEstoque_Estoque` FOREIGN KEY (`id_estoque`) REFERENCES `estoque` (`id_estoque`);

--
-- Restrições para tabelas `orcamento`
--
ALTER TABLE `orcamento`
  ADD CONSTRAINT `FK_Orcamento_Cliente` FOREIGN KEY (`id_usuario_cliente`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `FK_Orcamento_Telhado` FOREIGN KEY (`id_telhado`) REFERENCES `telhado` (`id_telhado`),
  ADD CONSTRAINT `FK_Orcamento_Vendedor` FOREIGN KEY (`id_usuario_vendedor`) REFERENCES `usuario` (`id_usuario`);

--
-- Restrições para tabelas `orcamento_prod`
--
ALTER TABLE `orcamento_prod`
  ADD CONSTRAINT `FK_Orcamento_Prod_Orcamento` FOREIGN KEY (`id_orcamento`) REFERENCES `orcamento` (`id_orcamento`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Orcamento_Prod_Produto` FOREIGN KEY (`id_produto`) REFERENCES `produto` (`id_produto`);

--
-- Restrições para tabelas `perfil_permissao`
--
ALTER TABLE `perfil_permissao`
  ADD CONSTRAINT `FK_Perfil_Permissao_Perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfil` (`id_perfil`),
  ADD CONSTRAINT `FK_Perfil_Permissao_Permissao` FOREIGN KEY (`id_permissao`) REFERENCES `permissao` (`id_permissao`);

--
-- Restrições para tabelas `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_Usuario_Perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfil` (`id_perfil`);

--
-- Restrições para tabelas `usuario_endereco`
--
ALTER TABLE `usuario_endereco`
  ADD CONSTRAINT `FK_usuario_endereco_endereco` FOREIGN KEY (`id_endereco`) REFERENCES `endereco` (`id_endereco`),
  ADD CONSTRAINT `FK_usuario_endereco_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
