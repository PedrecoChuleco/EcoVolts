-- =========================================================
-- EcoVolts - Script de criação do banco de dados
-- Execute este arquivo no MySQL (phpMyAdmin, workbench, ou
-- via terminal: mysql -u root -p < database.sql)
-- =========================================================

CREATE DATABASE IF NOT EXISTS ecovolts_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ecovolts_db;

-- Tabela de usuários do sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de registros de energia solar (CRUD principal)
CREATE TABLE IF NOT EXISTS registros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_equipamento VARCHAR(150) NOT NULL,
    potencia_kwh DECIMAL(10,2) NOT NULL,
    data_registro DATE NOT NULL,
    observacao VARCHAR(255) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Não inserimos um usuário de teste aqui porque a senha precisa ser
-- criptografada com password_hash() do PHP (não é seguro nem correto
-- gerar esse hash manualmente em SQL). Basta abrir cadastro.php e criar
-- uma conta normalmente pelo formulário — ela já será salva com hash.
