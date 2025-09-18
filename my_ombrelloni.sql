-- ###############################################################
-- ### SCRIPT PER LA CREAZIONE DELLA STRUTTURA DEL DATABASE    ###
-- ### my_ombrelloni (VUOTO, SENZA DATI)                       ###
-- ###############################################################

CREATE TABLE `Cliente` (
  `codice` varchar(20) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `dataNascita` date DEFAULT NULL,
  `indirizzo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `Tipologia` (
  `codice` varchar(10) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text,
  PRIMARY KEY (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `Tariffa` (
  `codice` varchar(10) NOT NULL,
  `prezzo` decimal(10,2) NOT NULL,
  `dataInizio` date NOT NULL,
  `dataFine` date NOT NULL,
  `tipo` varchar(15) NOT NULL,
  `numMinGiorni` int DEFAULT NULL,
  PRIMARY KEY (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --- Tabelle con Relazioni (Foreign Keys) ---

CREATE TABLE `Ombrellone` (
  `id` int NOT NULL AUTO_INCREMENT,
  `settore` varchar(20) NOT NULL,
  `numFila` int NOT NULL,
  `numPostoFila` int NOT NULL,
  `codTipologia` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `codTipologia` (`codTipologia`),
  CONSTRAINT `fk_ombrellone_tipologia` FOREIGN KEY (`codTipologia`) REFERENCES `Tipologia` (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `Contratto` (
  `numProgr` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `importo` decimal(10,2) NOT NULL,
  `codiceCliente` varchar(20) NOT NULL,
  PRIMARY KEY (`numProgr`),
  KEY `codiceCliente` (`codiceCliente`),
  CONSTRAINT `fk_contratto_cliente` FOREIGN KEY (`codiceCliente`) REFERENCES `Cliente` (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `TipologiaTariffa` (
  `codTipologia` varchar(10) NOT NULL,
  `codTariffa` varchar(10) NOT NULL,
  PRIMARY KEY (`codTipologia`,`codTariffa`),
  KEY `codTariffa` (`codTariffa`),
  CONSTRAINT `fk_tiptariffa_tipologia` FOREIGN KEY (`codTipologia`) REFERENCES `Tipologia` (`codice`),
  CONSTRAINT `fk_tiptariffa_tariffa` FOREIGN KEY (`codTariffa`) REFERENCES `Tariffa` (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `GiornoDisponibilita` (
  `idOmbrellone` int NOT NULL,
  `data` date NOT NULL,
  `numProgrContratto` int DEFAULT NULL,
  PRIMARY KEY (`idOmbrellone`,`data`),
  KEY `numProgrContratto` (`numProgrContratto`),
  CONSTRAINT `fk_giornodisp_ombrellone` FOREIGN KEY (`idOmbrellone`) REFERENCES `Ombrellone` (`id`),
  CONSTRAINT `fk_giornodisp_contratto` FOREIGN KEY (`numProgrContratto`) REFERENCES `Contratto` (`numProgr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;