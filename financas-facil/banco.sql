-- Cria o banco de dados se não existir
CREATE DATABASE IF NOT EXISTS financas_facil;
USE financas_facil;

-- Cria a tabela 'usuarios' se não existir
CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    cpf VARCHAR(20),
    email VARCHAR(255) UNIQUE,
    imagem_perfil LONGBLOB,
    imagem_perfil_tipo VARCHAR(50),
    senha VARCHAR(255) NOT NULL
);



-- Cria a tabela 'finance_calculations' se não existir
CREATE TABLE  IF NOT EXISTS  finance_calculations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    salario DECIMAL(10, 2),
    outras_despesas DECIMAL(10, 2),
    agua DECIMAL(10, 2),
    energia DECIMAL(10, 2),
    mercado DECIMAL(10, 2),
    prestacoes_veiculares DECIMAL(10, 2),
    gas DECIMAL(10, 2),
    condominio DECIMAL(10, 2),
    internet DECIMAL(10, 2),
    dividas DECIMAL(10, 2),
    percapita INT,
    data DATE,
    FOREIGN KEY (user_id) REFERENCES usuarios(id)
);


