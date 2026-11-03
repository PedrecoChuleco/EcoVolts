-- ============================================================================
-- EcoVolts — COMPLEMENTO DOS DADOS DE DEMONSTRAÇÃO   dados-demonstracao-complemento.sql
--
-- Completa os dados criados por dados-demonstracao.sql com:
--   Telhado         1 telhado por orçamento de demonstração (área, direção, estimativas)
--   Orcamento_Prod  4 itens por orçamento: placas, inversor, cabos e mão de obra
--   Endereço        endereço residencial de 12 clientes em 12 estados / 5 regiões
--                   (Cidade, Bairro, Endereco e usuario_endereco)
--
-- Efeito nas telas: orçamentos com itens e telhado (detalhe, PDF e relatório do
-- cliente completos) e gráfico "Clientes por região" com dados reais:
--   Sudeste 4 | Sul 3 | Nordeste 3 | Centro-Oeste 1 | Norte 1 | Sem endereço 2
--
-- PRÉ-REQUISITOS (nesta ordem): bd-ecovolts.sql  ->  dados-demonstracao.sql
--
-- IDEMPOTENTE: pode ser reexecutado sem duplicar telhados, itens ou endereços.
-- (Os orçamentos são localizados pelo cliente + valor da conta atual.)
--
-- Itens e valores seguem o modelo do orcamento-handler.php; preços vêm do cadastro
-- de Produto. Telhados: área sempre suficiente para as placas; nos orçamentos feitos
-- pelo Simulador com padrão estimado, a área não foi informada (area_estimada = 1).
-- ============================================================================

USE ecovolts;
SET NAMES utf8mb4;

-- ----------------------------------------------------------------------------
-- 1) ENDEREÇOS: cidades e bairros
-- ----------------------------------------------------------------------------
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Salvador', id_estado FROM Estado WHERE sigla_estado = 'BA'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Fortaleza', id_estado FROM Estado WHERE sigla_estado = 'CE'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Goiânia', id_estado FROM Estado WHERE sigla_estado = 'GO'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Belo Horizonte', id_estado FROM Estado WHERE sigla_estado = 'MG'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Belém', id_estado FROM Estado WHERE sigla_estado = 'PA'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Recife', id_estado FROM Estado WHERE sigla_estado = 'PE'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Curitiba', id_estado FROM Estado WHERE sigla_estado = 'PR'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Niterói', id_estado FROM Estado WHERE sigla_estado = 'RJ'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Porto Alegre', id_estado FROM Estado WHERE sigla_estado = 'RS'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Florianópolis', id_estado FROM Estado WHERE sigla_estado = 'SC'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Araras', id_estado FROM Estado WHERE sigla_estado = 'SP'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;
INSERT INTO Cidade (nome_cidade, id_estado) SELECT 'Campinas', id_estado FROM Estado WHERE sigla_estado = 'SP'
ON DUPLICATE KEY UPDATE nome_cidade = nome_cidade;

INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Pituba', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Salvador' AND e.sigla_estado = 'BA'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Aldeota', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Fortaleza' AND e.sigla_estado = 'CE'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Setor Bueno', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Goiânia' AND e.sigla_estado = 'GO'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Savassi', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Belo Horizonte' AND e.sigla_estado = 'MG'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Nazaré', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Belém' AND e.sigla_estado = 'PA'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Boa Viagem', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Recife' AND e.sigla_estado = 'PE'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Batel', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Curitiba' AND e.sigla_estado = 'PR'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Icaraí', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Niterói' AND e.sigla_estado = 'RJ'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Moinhos de Vento', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Porto Alegre' AND e.sigla_estado = 'RS'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Trindade', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Florianópolis' AND e.sigla_estado = 'SC'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Centro', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Araras' AND e.sigla_estado = 'SP'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;
INSERT INTO Bairro (nome_bairro, id_cidade)
SELECT 'Cambuí', c.id_cidade FROM Cidade c JOIN Estado e ON e.id_estado = c.id_estado
WHERE c.nome_cidade = 'Campinas' AND e.sigla_estado = 'SP'
ON DUPLICATE KEY UPDATE nome_bairro = nome_bairro;

-- ----------------------------------------------------------------------------
-- 2) ENDEREÇOS: endereço residencial de cada cliente (só se ainda não tiver um)
-- ----------------------------------------------------------------------------
INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua Sete de Setembro', '120', NULL,
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Centro' AND c.nome_cidade = 'Araras' AND e.sigla_estado = 'SP'),
       '13600010'
FROM Usuario u
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Avenida Norte-Sul', '455', 'Apto 31',
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Cambuí' AND c.nome_cidade = 'Campinas' AND e.sigla_estado = 'SP'),
       '13024001'
FROM Usuario u
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua Pernambuco', '890', NULL,
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Savassi' AND c.nome_cidade = 'Belo Horizonte' AND e.sigla_estado = 'MG'),
       '30130150'
FROM Usuario u
WHERE u.login = 'camila.torres@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'camila.torres@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua Gavião Peixoto', '77', 'Bloco B',
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Icaraí' AND c.nome_cidade = 'Niterói' AND e.sigla_estado = 'RJ'),
       '24230100'
FROM Usuario u
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua Bispo Dom José', '310', NULL,
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Batel' AND c.nome_cidade = 'Curitiba' AND e.sigla_estado = 'PR'),
       '80420090'
FROM Usuario u
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua Padre Chagas', '65', NULL,
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Moinhos de Vento' AND c.nome_cidade = 'Porto Alegre' AND e.sigla_estado = 'RS'),
       '90570080'
FROM Usuario u
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua Lauro Linhares', '1500', 'Casa 2',
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Trindade' AND c.nome_cidade = 'Florianópolis' AND e.sigla_estado = 'SC'),
       '88036002'
FROM Usuario u
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Avenida Manoel Dias da Silva', '240', NULL,
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Pituba' AND c.nome_cidade = 'Salvador' AND e.sigla_estado = 'BA'),
       '41830000'
FROM Usuario u
WHERE u.login = 'joao.pires@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'joao.pires@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Avenida Boa Viagem', '3100', 'Apto 902',
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Boa Viagem' AND c.nome_cidade = 'Recife' AND e.sigla_estado = 'PE'),
       '51020000'
FROM Usuario u
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua T-30', '812', NULL,
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Setor Bueno' AND c.nome_cidade = 'Goiânia' AND e.sigla_estado = 'GO'),
       '74215040'
FROM Usuario u
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Rua Desembargador Leite Albuquerque', '150', 'Loja 1',
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Aldeota' AND c.nome_cidade = 'Fortaleza' AND e.sigla_estado = 'CE'),
       '60150150'
FROM Usuario u
WHERE u.login = 'paodourado@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'paodourado@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

INSERT INTO Endereco (logradouro, num_endereco, complemento, id_bairro, cep)
SELECT 'Avenida Nazaré', '480', NULL,
       (SELECT b.id_bairro FROM Bairro b JOIN Cidade c ON c.id_cidade = b.id_cidade JOIN Estado e ON e.id_estado = c.id_estado
         WHERE b.nome_bairro = 'Nazaré' AND c.nome_cidade = 'Belém' AND e.sigla_estado = 'PA'),
       '66035170'
FROM Usuario u
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');
INSERT INTO usuario_endereco (id_usuario, id_endereco, tipo_endereco)
SELECT u.id_usuario, LAST_INSERT_ID(), 'Residencial' FROM Usuario u
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br'
  AND NOT EXISTS (SELECT 1 FROM usuario_endereco ue WHERE ue.id_usuario = u.id_usuario AND ue.tipo_endereco = 'Residencial');

-- ----------------------------------------------------------------------------
-- 3) TELHADOS: um por orçamento (liga em Orcamento.id_telhado)
--    Colunas: área (m²), direção, área estimada, direção estimada, comporta placas.
-- ----------------------------------------------------------------------------
INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT NULL, 'Norte', 1, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 298.40 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 298.40 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT NULL, 'Leste', 1, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 412.90 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 412.90 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 12, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 186.30 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 186.30 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT NULL, 'Sul', 1, 1, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 523.10 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 523.10 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 19, 'Oeste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 247.80 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 247.80 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 35, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 689.50 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 689.50 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT NULL, 'Leste', 1, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 351.20 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 351.20 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT NULL, 'Norte', 1, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 455.00 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 455.00 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 39, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 734.60 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 734.60 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 158, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2380.00 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2380.00 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 25, 'Leste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 398.70 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 398.70 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 46, 'Oeste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 612.40 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 612.40 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 100, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1475.30 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1475.30 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 23, 'Sul', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 284.90 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 284.90 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 34, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 530.00 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 530.00 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 200, 'Leste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 3120.80 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 3120.80 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 28, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 447.60 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 447.60 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 72, 'Oeste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 905.20 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 905.20 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 23, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 376.10 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 376.10 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 23, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 321.70 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 321.70 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 25, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 478.90 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 478.90 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 116, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1890.40 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1890.40 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 24, 'Leste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 263.50 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 263.50 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 156, 'Norte', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2750.00 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2750.00 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 45, 'Oeste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 584.30 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 584.30 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 25, 'Sul', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'henrique.duarte@demo.ecovolts.com.br' AND o.valor_totalCE = 342.00 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'henrique.duarte@demo.ecovolts.com.br' AND o.valor_totalCE = 342.00 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 18, 'Sul', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 219.40 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 219.40 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

INSERT INTO Telhado (area_telhado, direcao_telhado, area_estimada, direcao_estimada, comporta_placas)
SELECT 32, 'Leste', 0, 0, 1
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 397.80 AND o.id_telhado IS NULL;
UPDATE Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
SET o.id_telhado = LAST_INSERT_ID()
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 397.80 AND o.id_telhado IS NULL AND o.id_orcamento > 0;

-- ----------------------------------------------------------------------------
-- 4) ITENS: placas, inversor, cabos e mão de obra de cada orçamento
--    Valor unitário = preço atual do cadastro de Produto.
-- ----------------------------------------------------------------------------
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 5, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 298.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 298.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 50, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 298.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 5, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 298.40
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 412.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 412.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 80, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 412.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 412.90
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 3, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 186.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 186.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 30, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 186.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 3, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 186.30
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 13, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 523.10
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 2, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 523.10
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 130, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 523.10
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 13, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 523.10
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 5, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 247.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 247.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 50, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 247.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 5, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 247.80
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 11, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 689.50
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 2, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 689.50
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 110, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 689.50
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 11, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 689.50
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 7, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 351.20
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 351.20
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 70, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 351.20
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 7, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 351.20
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 455.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 455.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 80, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 455.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 455.00
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 12, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 734.60
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 2, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 734.60
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 120, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 734.60
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 12, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'larissa.campos@demo.ecovolts.com.br' AND o.valor_totalCE = 734.60
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 42, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2380.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 5, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2380.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 420, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2380.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 42, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2380.00
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 398.70
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 398.70
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 80, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 398.70
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 398.70
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 13, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 612.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 2, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 612.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 130, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 612.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 13, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 612.40
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 25, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1475.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 3, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1475.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 250, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1475.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 25, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1475.30
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 7, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 284.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 284.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 70, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 284.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 7, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 284.90
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 9, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 530.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 530.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 90, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 530.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 9, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'felipe.barros@demo.ecovolts.com.br' AND o.valor_totalCE = 530.00
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 64, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 3120.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 3120.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 640, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 3120.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 64, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 3120.80
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 447.60
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 447.60
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 80, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 447.60
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'camila.torres@demo.ecovolts.com.br' AND o.valor_totalCE = 447.60
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 18, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 905.20
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 2, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 905.20
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 180, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 905.20
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 18, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'diego.almeida@demo.ecovolts.com.br' AND o.valor_totalCE = 905.20
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 7, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 376.10
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 376.10
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 70, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 376.10
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 7, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'gabriela.monteiro@demo.ecovolts.com.br' AND o.valor_totalCE = 376.10
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 6, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 321.70
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 321.70
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 60, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 321.70
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 6, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'ana.ribeiro@demo.ecovolts.com.br' AND o.valor_totalCE = 321.70
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 478.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 478.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 80, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 478.90
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'bruno.carvalho@demo.ecovolts.com.br' AND o.valor_totalCE = 478.90
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 33, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1890.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 4, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1890.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 330, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1890.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 33, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'oficinarapida@demo.ecovolts.com.br' AND o.valor_totalCE = 1890.40
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 6, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 263.50
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 263.50
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 60, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 263.50
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 6, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'isabela.fontes@demo.ecovolts.com.br' AND o.valor_totalCE = 263.50
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 48, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2750.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 6, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2750.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 480, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2750.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 48, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'paodourado@demo.ecovolts.com.br' AND o.valor_totalCE = 2750.00
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 12, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 584.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 2, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 584.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 120, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 584.30
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 12, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'joao.pires@demo.ecovolts.com.br' AND o.valor_totalCE = 584.30
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'henrique.duarte@demo.ecovolts.com.br' AND o.valor_totalCE = 342.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'henrique.duarte@demo.ecovolts.com.br' AND o.valor_totalCE = 342.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 80, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'henrique.duarte@demo.ecovolts.com.br' AND o.valor_totalCE = 342.00
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'henrique.duarte@demo.ecovolts.com.br' AND o.valor_totalCE = 342.00
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 5, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 219.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 219.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 50, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 219.40
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 5, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'eduarda.nogueira@demo.ecovolts.com.br' AND o.valor_totalCE = 219.40
ON DUPLICATE KEY UPDATE qtd = qtd;

INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Painel Solar 550W Mono'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 397.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 1, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Inversor Solar 5kWp'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 397.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 80, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Cabo Solar 6 mm (por metro)'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 397.80
ON DUPLICATE KEY UPDATE qtd = qtd;
INSERT INTO Orcamento_Prod (id_orcamento, id_produto, qtd, valor_unitario, desconto)
SELECT o.id_orcamento, p.id_produto, 8, p.valorUn_produto, 0.00
FROM Orcamento o JOIN Usuario u ON u.id_usuario = o.id_usuario_cliente
JOIN Produto p ON p.nome_produto = 'Mão de Obra de Instalação (por placa)'
WHERE u.login = 'marcos.teixeira@demo.ecovolts.com.br' AND o.valor_totalCE = 397.80
ON DUPLICATE KEY UPDATE qtd = qtd;

-- ----------------------------------------------------------------------------
-- CONFERÊNCIA (opcional)
-- ----------------------------------------------------------------------------
-- SELECT COUNT(*) FROM Orcamento_Prod;                     -- esperado: 112
-- SELECT COUNT(*) FROM Orcamento WHERE id_telhado IS NOT NULL;  -- esperado: 28
-- SELECT o.num_orcamento, o.investimento, SUM(op.qtd * op.valor_unitario) AS soma_itens
--   FROM Orcamento o JOIN Orcamento_Prod op ON op.id_orcamento = o.id_orcamento
--  GROUP BY o.id_orcamento;                                 -- investimento = soma_itens

-- ----------------------------------------------------------------------------
-- LIMPEZA (opcional)
-- A limpeza de dados-demonstracao.sql (que apaga orçamentos e usuários de demonstração)
-- já remove os itens (Orcamento_Prod) e os vínculos de endereço em cascata. Sobram
-- telhados e endereços órfãos, que podem ser removidos assim:
-- ----------------------------------------------------------------------------
-- DELETE FROM Telhado WHERE id_telhado > 0
--   AND id_telhado NOT IN (SELECT id_telhado FROM Orcamento WHERE id_telhado IS NOT NULL);
-- DELETE FROM Endereco WHERE id_endereco > 0
--   AND id_endereco NOT IN (SELECT id_endereco FROM usuario_endereco);
