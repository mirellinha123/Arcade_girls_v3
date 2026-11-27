SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS jogos_bd
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;
USE jogos_bd;

CREATE TABLE IF NOT EXISTS Usuarios (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Username VARCHAR(40) NOT NULL UNIQUE,
    Senha VARCHAR(255) NOT NULL,
    Perfil ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    Foto VARCHAR(100) NULL,
    DataCad DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS Clientes (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(120) NOT NULL,
    Email VARCHAR(150) NULL,
    Telefone VARCHAR(30) NULL,
    CPF VARCHAR(14) NULL UNIQUE,
    DataNascimento DATE NULL,
    Foto VARCHAR(100) NULL,
    DataCad DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE Clientes MODIFY Nome VARCHAR(120) NOT NULL;

CREATE TABLE IF NOT EXISTS Jogos (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(150) NOT NULL,
    Plataforma VARCHAR(80) NOT NULL,
    Genero VARCHAR(80) NOT NULL,
    AnoLancamento SMALLINT UNSIGNED NOT NULL,
    Preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ClassificacaoIndicativa VARCHAR(40) NOT NULL,
    DataEntrada DATE NOT NULL,
    Estoque INT UNSIGNED NOT NULL DEFAULT 0,
    EstadoConservacao VARCHAR(40) NOT NULL,
    TipoMidia VARCHAR(40) NOT NULL,
    Descricao TEXT NOT NULL,
    Foto VARCHAR(100) NULL,
    DataCad DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_jogos_nome (Nome),
    INDEX idx_jogos_plataforma_genero (Plataforma, Genero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO Usuarios (Nome, Email, Username, Senha, Perfil)
VALUES ('Mirella Admin', 'mirella@arcade.local', 'mirella_admin',
        '$2y$10$7PpuPnd.wRAssjVeP6qE4OxDVC7.Dp6jovUFP6ixsVBNgvgAuvM4O', 'admin')
ON DUPLICATE KEY UPDATE
    Nome = VALUES(Nome), Email = VALUES(Email), Username = VALUES(Username),
    Senha = VALUES(Senha), Perfil = VALUES(Perfil);

UPDATE Usuarios
SET Nome = 'Julia Vendedora', Email = 'julia.vendedora@arcade.local',
    Username = 'julia_vendedora',
    Senha = '$2y$10$YuOXLpzVfMve8POoB/SSt.DNfURZXvs.jcZz8eXbHNGfze5Ujx2vW', Perfil = 'usuario'
WHERE Username IN ('visitante', 'julia_funcionaria');

INSERT INTO Usuarios (Nome, Email, Username, Senha, Perfil)
VALUES ('Julia Vendedora', 'julia.vendedora@arcade.local', 'julia_vendedora',
        '$2y$10$YuOXLpzVfMve8POoB/SSt.DNfURZXvs.jcZz8eXbHNGfze5Ujx2vW', 'usuario')
ON DUPLICATE KEY UPDATE
    Nome = VALUES(Nome), Email = VALUES(Email), Username = VALUES(Username),
    Senha = VALUES(Senha), Perfil = VALUES(Perfil);

UPDATE Clientes SET Telefone = '(15) 98811-2233', CPF = '104.928.376-78', DataNascimento = '1992-03-14'
WHERE Email = 'ana.souza@example.com';
UPDATE Clientes SET Telefone = '(15) 98822-3344', CPF = '285.173.649-37', DataNascimento = '1988-08-22'
WHERE Email = 'bruno.lima@example.com';
UPDATE Clientes SET Telefone = '(15) 98833-4455', CPF = '390.461.827-03', DataNascimento = '1995-06-07'
WHERE Email = 'camila.rocha@example.com';
UPDATE Clientes SET Telefone = '(15) 98844-5566', CPF = '617.204.983-69', DataNascimento = '1990-11-30'
WHERE Email = 'rafael.costa@example.com';

INSERT INTO Clientes (Nome, Email, Telefone, CPF, DataNascimento)
SELECT novos.Nome, novos.Email, novos.Telefone, novos.CPF, novos.DataNascimento
FROM (
    SELECT 'Ana Souza' AS Nome, 'ana.souza@example.com' AS Email,
           '(15) 98811-2233' AS Telefone, '104.928.376-78' AS CPF, '1992-03-14' AS DataNascimento
    UNION ALL SELECT 'Bruno Lima', 'bruno.lima@example.com',
           '(15) 98822-3344', '285.173.649-37', '1988-08-22'
    UNION ALL SELECT 'Camila Rocha', 'camila.rocha@example.com',
           '(15) 98833-4455', '390.461.827-03', '1995-06-07'
    UNION ALL SELECT 'Rafael Costa', 'rafael.costa@example.com',
           '(15) 98844-5566', '617.204.983-69', '1990-11-30'
) AS novos
WHERE NOT EXISTS (SELECT 1 FROM Clientes WHERE Email = novos.Email);

INSERT INTO Jogos (
    Nome, Plataforma, Genero, AnoLancamento, Preco, ClassificacaoIndicativa,
    DataEntrada, Estoque, EstadoConservacao, TipoMidia, Descricao
)
SELECT novos.Nome, novos.Plataforma, novos.Genero, novos.AnoLancamento, novos.Preco,
       novos.Classificacao, novos.DataEntrada, novos.Estoque, novos.Estado,
       novos.Midia, novos.Descricao
FROM (
    SELECT 'Super Mario World' AS Nome, 'Super Nintendo' AS Plataforma, 'Aventura' AS Genero,
           1990 AS AnoLancamento, 149.90 AS Preco, 'Livre' AS Classificacao,
           '2026-09-01' AS DataEntrada, 5 AS Estoque, 'Excelente' AS Estado,
           'Cartucho' AS Midia, 'Aventura clássica no Reino dos Dinossauros.' AS Descricao
    UNION ALL SELECT 'Pac-Man', 'Arcade', 'Ação', 1980, 199.90, 'Livre', '2026-09-05', 3, 'Bom',
           'Cartucho', 'Clássico jogo de arcade do personagem Pac-Man.'
    UNION ALL SELECT 'Sonic The Hedgehog', 'Mega Drive', 'Plataforma', 1991, 129.90, 'Livre',
           '2026-09-10', 4, 'Excelente', 'Cartucho', 'A primeira aventura do famoso ouriço azul.'
    UNION ALL SELECT 'The Legend of Zelda: Ocarina of Time', 'Nintendo 64', 'Aventura', 1998,
           249.90, '10 Anos', '2026-09-15', 2, 'Muito Bom', 'Cartucho',
           'Uma das aventuras mais aclamadas da franquia Zelda.'
) AS novos
WHERE NOT EXISTS (
    SELECT 1 FROM Jogos
    WHERE Jogos.Nome = novos.Nome AND Jogos.Plataforma = novos.Plataforma
);

UPDATE Jogos SET Foto = 'super mario world.png'
WHERE Nome = 'Super Mario World';
UPDATE Jogos SET Foto = 'pac-man.jpg'
WHERE Nome = 'Pac-Man';
UPDATE Jogos SET Foto = 'sonic.jpg'
WHERE Nome = 'Sonic The Hedgehog';
UPDATE Jogos SET Foto = 'zelda ocarina of time.jpg'
WHERE Nome = 'The Legend of Zelda: Ocarina of Time';
