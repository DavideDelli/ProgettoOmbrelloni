-- File: schema.sql
-- Questo script crea la struttura completa del database "noleggio_ombrelloni".
-- Eseguire questo script in un database vuoto per creare tutte le tabelle necessarie.

-- Cancellazione delle tabelle esistenti per una nuova creazione pulita
DROP TABLE IF EXISTS GiornoDisponibilita;
DROP TABLE IF EXISTS Contratto;
DROP TABLE IF EXISTS Cliente;
DROP TABLE IF EXISTS TipologiaTariffa;
DROP TABLE IF EXISTS Tariffa;
DROP TABLE IF EXISTS Ombrellone;
DROP TABLE IF EXISTS Tipologia;

-- Tabella TIPOLOGIA
CREATE TABLE Tipologia (
    codice VARCHAR(10) PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descrizione TEXT
);

-- Tabella OMBRELLONE
CREATE TABLE Ombrellone (
    id INT PRIMARY KEY,
    settore VARCHAR(20) NOT NULL,
    numFila INT NOT NULL,
    numPostoFila INT NOT NULL,
    codTipologia VARCHAR(10) NOT NULL,
    FOREIGN KEY (codTipologia) REFERENCES Tipologia(codice)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- Tabella TARIFFA
CREATE TABLE Tariffa (
    codice VARCHAR(10) PRIMARY KEY,
    prezzo DECIMAL(10, 2) NOT NULL,
    dataInizio DATE NOT NULL,
    dataFine DATE NOT NULL,
    tipo VARCHAR(15) NOT NULL CHECK (tipo IN ('Giornaliera', 'Abbonamento')),
    numMinGiorni INT,
    CONSTRAINT chk_Tariffa_Tipo_Giorni CHECK (
        (tipo = 'Giornaliera' AND numMinGiorni IS NULL) OR
        (tipo = 'Abbonamento' AND numMinGiorni IS NOT NULL)
    )
);

-- Tabella di collegamento TIPOLOGIA - TARIFFA (Relazione N:M)
CREATE TABLE TipologiaTariffa (
    codTipologia VARCHAR(10),
    codTariffa VARCHAR(10),
    PRIMARY KEY (codTipologia, codTariffa),
    FOREIGN KEY (codTipologia) REFERENCES Tipologia(codice)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (codTariffa) REFERENCES Tariffa(codice)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- Tabella CLIENTE
CREATE TABLE Cliente (
    codice VARCHAR(20) PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    dataNascita DATE,
    indirizzo VARCHAR(255)
);

-- Tabella CONTRATTO
CREATE TABLE Contratto (
    numProgr INT PRIMARY KEY,
    data DATE NOT NULL,
    importo DECIMAL(10, 2) NOT NULL,
    codiceCliente VARCHAR(20) NOT NULL,
    FOREIGN KEY (codiceCliente) REFERENCES Cliente(codice)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- Tabella GIORNODISPONIBILITA
CREATE TABLE GiornoDisponibilita (
    idOmbrellone INT,
    data DATE,
    numProgrContratto INT,
    PRIMARY KEY (idOmbrellone, data),
    FOREIGN KEY (idOmbrellone) REFERENCES Ombrellone(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (numProgrContratto) REFERENCES Contratto(numProgr)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);