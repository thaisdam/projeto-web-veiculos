-- Criação da tabela para armazenar os dados dos anunciantes.
CREATE TABLE Anunciante (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(255) NOT NULL,
    CPF VARCHAR(14) NOT NULL UNIQUE,
    Email VARCHAR(255) NOT NULL UNIQUE,
    SenhaHash VARCHAR(255) NOT NULL,
    Telefone VARCHAR(20)
);

-- Criação da tabela para armazenar os anúncios de veículos.
-- Ela se relaciona com a tabela Anunciante.
CREATE TABLE Anuncio (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Marca VARCHAR(100) NOT NULL,
    Modelo VARCHAR(100) NOT NULL,
    Ano INT NOT NULL,
    Cor VARCHAR(50),
    Quilometragem INT,
    Descricao TEXT,
    Valor DECIMAL(10, 2) NOT NULL,
    DataHora DATETIME NOT NULL,
    Estado VARCHAR(50),
    Cidade VARCHAR(100),
    IdAnunciante INT NOT NULL,
    FOREIGN KEY (IdAnunciante) REFERENCES Anunciante(Id)
);

-- Criação da tabela para registrar o interesse de usuários nos anúncios.
-- Ela se relaciona com a tabela Anuncio.
CREATE TABLE Interesse (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Nome VARCHAR(255) NOT NULL,
    Telefone VARCHAR(20) NOT NULL,
    Mensagem TEXT NOT NULL,
    DataHora DATETIME NOT NULL,
    IdAnuncio INT NOT NULL,
    FOREIGN KEY (IdAnuncio) REFERENCES Anuncio(Id)
);

-- Criação da tabela para armazenar as fotos associadas a cada anúncio.
-- O nome do arquivo da foto e o ID do anúncio formam uma chave primária composta
-- para garantir que não haja fotos duplicadas para o mesmo anúncio.
CREATE TABLE Foto (
    IdAnuncio INT NOT NULL,
    NomeArqFoto VARCHAR(255) NOT NULL,
    PRIMARY KEY (IdAnuncio, NomeArqFoto),
    FOREIGN KEY (IdAnuncio) REFERENCES Anuncio(Id)
);