-- ###############################################################
-- ### SCRIPT PER IL POPOLAMENTO DEL DATABASE my_ombrelloni    ###
-- ###############################################################

-- Specifica quale database usare, per sicurezza
USE my_ombrelloni;

-- Inizia una transazione: o va tutto a buon fine, o non viene salvato nulla
START TRANSACTION;

-- ====================================================================
-- FASE 1: INSERIMENTO DATI NELLE TABELLE PRINCIPALI
-- ====================================================================

-- Popolamento Tipologia
INSERT INTO `Tipologia` (`codice`, `nome`, `descrizione`) VALUES
('STD', 'Standard', 'Ombrellone classico con due lettini.'),
('VIP', 'VIP', 'Ombrellone in prima fila con cassaforte, due lettini e una sedia regista.');

-- Popolamento Tariffa (schema di prezzi semplice)
INSERT INTO `Tariffa` (`codice`, `prezzo`, `dataInizio`, `dataFine`, `tipo`, `numMinGiorni`) VALUES
('STD_D', 30.00, '2020-01-01', '2029-12-31', 'GIORNALIERO', 1),
('VIP_D', 50.00, '2020-01-01', '2029-12-31', 'GIORNALIERO', 1),
('STD_W', 180.00, '2020-01-01', '2029-12-31', 'SETTIMANALE', 7),
('VIP_W', 300.00, '2020-01-01', '2029-12-31', 'SETTIMANALE', 7);

-- Collegamento Tipologie-Tariffe
INSERT INTO `TipologiaTariffa` (`codTipologia`, `codTariffa`) VALUES
('STD', 'STD_D'), ('STD', 'STD_W'),
('VIP', 'VIP_D'), ('VIP', 'VIP_W');

-- Popolamento con 20 Clienti
INSERT INTO `Cliente` (`codice`, `nome`, `cognome`, `dataNascita`, `indirizzo`) VALUES
('CLIENTE0001', 'Mario', 'Rossi', '1988-05-20', 'Via Roma 1'),
('CLIENTE0002', 'Laura', 'Bianchi', '1995-11-02', 'Via Milano 2'),
('CLIENTE0003', 'Paolo', 'Verdi', '1976-01-15', 'Corso Garibaldi 3'),
('CLIENTE0004', 'Anna', 'Neri', '2001-07-30', 'Piazza Duomo 4'),
('CLIENTE0005', 'Luca', 'Gialli', '1982-09-12', 'Via Mazzini 5'),
('CLIENTE0006', 'Giulia', 'Ferrari', '1990-03-25', 'Viale Europa 6'),
('CLIENTE0007', 'Marco', 'Ricci', '1985-08-10', 'Via Dante 7'),
('CLIENTE0008', 'Sara', 'Conti', '1998-12-01', 'Largo Colombo 8'),
('CLIENTE0009', 'Simone', 'Gallo', '1970-06-18', 'Via Petrarca 9'),
('CLIENTE0010', 'Elena', 'Lombardi', '2000-02-29', 'Via Leopardi 10'),
('CLIENTE0011', 'Davide', 'Moretti', '1989-04-14', 'Piazza Volta 11'),
('CLIENTE0012', 'Chiara', 'Rizzo', '1993-10-05', 'Via Manzoni 12'),
('CLIENTE0013', 'Matteo', 'Greco', '1981-07-22', 'Corso Vittorio Emanuele 13'),
('CLIENTE0014', 'Francesca', 'Marino', '1996-09-03', 'Via Cavour 14'),
('CLIENTE0015', 'Andrea', 'Santoro', '1978-11-11', 'Viale della Repubblica 15'),
('CLIENTE0016', 'Valentina', 'Barbieri', '1991-01-08', 'Via dei Mille 16'),
('CLIENTE0017', 'Giovanni', 'Fontana', '1986-05-30', 'Piazza San Marco 17'),
('CLIENTE0018', 'Marta', 'Russo', '1999-08-19', 'Via Po 18'),
('CLIENTE0019', 'Riccardo', 'Esposito', '1973-03-12', 'Lungo Tevere 19'),
('CLIENTE0020', 'Sofia', 'Romano', '2002-04-07', 'Via Appia 20');

-- ====================================================================
-- FASE 2: INSERIMENTO DATI NELLE TABELLE CON RELAZIONI
-- ====================================================================

-- Popolamento con 100 Ombrelloni (l'ID è AUTO_INCREMENT, non va specificato)
INSERT INTO `Ombrellone` (`settore`, `numFila`, `numPostoFila`, `codTipologia`) VALUES
('A',1,1,'VIP'),('A',1,2,'VIP'),('A',1,3,'STD'),('A',1,4,'STD'),('A',1,5,'STD'),('A',1,6,'STD'),('A',1,7,'STD'),('A',1,8,'STD'),('A',1,9,'STD'),('A',1,10,'STD'),
('A',2,1,'STD'),('A',2,2,'STD'),('A',2,3,'STD'),('A',2,4,'STD'),('A',2,5,'STD'),('A',2,6,'STD'),('A',2,7,'STD'),('A',2,8,'STD'),('A',2,9,'STD'),('A',2,10,'STD'),
('B',1,1,'VIP'),('B',1,2,'VIP'),('B',1,3,'VIP'),('B',1,4,'VIP'),('B',1,5,'STD'),('B',1,6,'STD'),('B',1,7,'STD'),('B',1,8,'STD'),('B',1,9,'STD'),('B',1,10,'STD'),
('B',2,1,'STD'),('B',2,2,'STD'),('B',2,3,'STD'),('B',2,4,'STD'),('B',2,5,'STD'),('B',2,6,'STD'),('B',2,7,'STD'),('B',2,8,'STD'),('B',2,9,'STD'),('B',2,10,'STD'),
('C',1,1,'VIP'),('C',1,2,'VIP'),('C',1,3,'STD'),('C',1,4,'STD'),('C',1,5,'STD'),('C',1,6,'STD'),('C',1,7,'STD'),('C',1,8,'STD'),('C',1,9,'STD'),('C',1,10,'STD'),
('C',2,1,'STD'),('C',2,2,'STD'),('C',2,3,'STD'),('C',2,4,'STD'),('C',2,5,'STD'),('C',2,6,'STD'),('C',2,7,'STD'),('C',2,8,'STD'),('C',2,9,'STD'),('C',2,10,'STD'),
('D',1,1,'VIP'),('D',1,2,'VIP'),('D',1,3,'STD'),('D',1,4,'STD'),('D',1,5,'STD'),('D',1,6,'STD'),('D',1,7,'STD'),('D',1,8,'STD'),('D',1,9,'STD'),('D',1,10,'STD'),
('D',2,1,'STD'),('D',2,2,'STD'),('D',2,3,'STD'),('D',2,4,'STD'),('D',2,5,'STD'),('D',2,6,'STD'),('D',2,7,'STD'),('D',2,8,'STD'),('D',2,9,'STD'),('D',2,10,'STD'),
('E',1,1,'VIP'),('E',1,2,'VIP'),('E',1,3,'STD'),('E',1,4,'STD'),('E',1,5,'STD'),('E',1,6,'STD'),('E',1,7,'STD'),('E',1,8,'STD'),('E',1,9,'STD'),('E',1,10,'STD');

-- Popolamento con 30 Contratti (numProgr è AUTO_INCREMENT)
INSERT INTO `Contratto` (`data`, `importo`, `codiceCliente`) VALUES
('2024-05-10',180.00,'CLIENTE0001'),('2024-05-20',30.00,'CLIENTE0002'),('2024-06-01',180.00,'CLIENTE0003'),('2024-06-15',30.00,'CLIENTE0004'),
('2024-07-01',300.00,'CLIENTE0005'),('2024-07-10',50.00,'CLIENTE0006'),('2024-07-20',180.00,'CLIENTE0007'),('2024-08-01',300.00,'CLIENTE0008'),
('2024-08-10',50.00,'CLIENTE0009'),('2024-09-05',180.00,'CLIENTE0010'),('2025-05-12',30.00,'CLIENTE0011'),('2025-05-22',180.00,'CLIENTE0012'),
('2025-06-05',50.00,'CLIENTE0013'),('2025-06-20',180.00,'CLIENTE0014'),('2025-07-02',50.00,'CLIENTE0015'),('2025-07-08',300.00,'CLIENTE0016'),
('2025-07-15',180.00,'CLIENTE0017'),('2025-08-03',50.00,'CLIENTE0018'),('2025-08-05',300.00,'CLIENTE0019'),('2025-09-01',180.00,'CLIENTE0020'),
('2025-06-11',300.00,'CLIENTE0001'),('2025-07-25',180.00,'CLIENTE0002'),('2025-08-15',30.00,'CLIENTE0003'),('2025-08-20',50.00,'CLIENTE0004'),
('2025-07-18',180.00,'CLIENTE0005'),('2024-05-11',180.00,'CLIENTE0006'),('2024-05-21',30.00,'CLIENTE0007'),('2024-06-02',180.00,'CLIENTE0008'),
('2024-06-16',30.00,'CLIENTE0009'),('2024-07-03',300.00,'CLIENTE0010');

-- ====================================================================
-- FASE 3: POPOLAMENTO GIORNI E AGGIORNAMENTO PRENOTAZIONI
-- ====================================================================

-- Popolamento di tutti i giorni disponibili per tutte le stagioni
INSERT INTO GiornoDisponibilita (idOmbrellone, data)
WITH RECURSIVE date_series(d) AS (
  SELECT DATE('2023-05-01') AS d
  UNION ALL
  SELECT d + INTERVAL 1 DAY FROM date_series WHERE d < '2025-09-30'
)
SELECT o.id, ds.d
FROM Ombrellone AS o
CROSS JOIN date_series AS ds
WHERE MONTH(ds.d) BETWEEN 5 AND 9;

-- Aggiornamento dei giorni prenotati in base ai contratti inseriti
UPDATE GiornoDisponibilita SET numProgrContratto=1 WHERE idOmbrellone=11 AND data BETWEEN '2024-05-15' AND '2024-05-21';
UPDATE GiornoDisponibilita SET numProgrContratto=2 WHERE idOmbrellone=12 AND data = '2024-05-21';
UPDATE GiornoDisponibilita SET numProgrContratto=3 WHERE idOmbrellone=13 AND data BETWEEN '2024-06-05' AND '2024-06-11';
UPDATE GiornoDisponibilita SET numProgrContratto=4 WHERE idOmbrellone=14 AND data = '2024-06-16';
UPDATE GiornoDisponibilita SET numProgrContratto=5 WHERE idOmbrellone=1 AND data BETWEEN '2024-07-03' AND '2024-07-09';
UPDATE GiornoDisponibilita SET numProgrContratto=6 WHERE idOmbrellone=2 AND data = '2024-07-11';
UPDATE GiornoDisponibilita SET numProgrContratto=7 WHERE idOmbrellone=15 AND data BETWEEN '2024-07-24' AND '2024-07-30';
UPDATE GiornoDisponibilita SET numProgrContratto=8 WHERE idOmbrellone=21 AND data BETWEEN '2024-08-07' AND '2024-08-13';
UPDATE GiornoDisponibilita SET numProgrContratto=9 WHERE idOmbrellone=22 AND data = '2024-08-15';
UPDATE GiornoDisponibilita SET numProgrContratto=10 WHERE idOmbrellone=30 AND data BETWEEN '2024-09-11' AND '2024-09-17';
UPDATE GiornoDisponibilita SET numProgrContratto=11 WHERE idOmbrellone=31 AND data = '2025-05-13';
UPDATE GiornoDisponibilita SET numProgrContratto=12 WHERE idOmbrellone=32 AND data BETWEEN '2025-05-27' AND '2025-06-02';
UPDATE GiornoDisponibilita SET numProgrContratto=13 WHERE idOmbrellone=41 AND data = '2025-06-06';
UPDATE GiornoDisponibilita SET numProgrContratto=14 WHERE idOmbrellone=42 AND data BETWEEN '2025-06-24' AND '2025-06-30';
UPDATE GiornoDisponibilita SET numProgrContratto=15 WHERE idOmbrellone=43 AND data = '2025-07-03';
UPDATE GiornoDisponibilita SET numProgrContratto=16 WHERE idOmbrellone=44 AND data BETWEEN '2025-07-08' AND '2025-07-14';
UPDATE GiornoDisponibilita SET numProgrContratto=17 WHERE idOmbrellone=50 AND data BETWEEN '2025-07-15' AND '2025-07-21';
UPDATE GiornoDisponibilita SET numProgrContratto=18 WHERE idOmbrellone=51 AND data = '2025-08-04';
UPDATE GiornoDisponibilita SET numProgrContratto=19 WHERE idOmbrellone=52 AND data BETWEEN '2025-08-05' AND '2025-08-11';
UPDATE GiornoDisponibilita SET numProgrContratto=20 WHERE idOmbrellone=60 AND data BETWEEN '2025-09-02' AND '2025-09-08';
UPDATE GiornoDisponibilita SET numProgrContratto=21 WHERE idOmbrellone=3 AND data BETWEEN '2025-06-12' AND '2025-06-18';
UPDATE GiornoDisponibilita SET numProgrContratto=22 WHERE idOmbrellone=16 AND data BETWEEN '2025-07-26' AND '2025-08-01';
UPDATE GiornoDisponibilita SET numProgrContratto=23 WHERE idOmbrellone=17 AND data = '2025-08-16';
UPDATE GiornoDisponibilita SET numProgrContratto=24 WHERE idOmbrellone=4 AND data = '2025-08-21';
UPDATE GiornoDisponibilita SET numProgrContratto=25 WHERE idOmbrellone=18 AND data BETWEEN '2025-07-19' AND '2025-07-25';
UPDATE GiornoDisponibilita SET numProgrContratto=26 WHERE idOmbrellone=25 AND data BETWEEN '2024-05-12' AND '2024-05-18';
UPDATE GiornoDisponibilita SET numProgrContratto=27 WHERE idOmbrellone=26 AND data = '2024-05-22';
UPDATE GiornoDisponibilita SET numProgrContratto=28 WHERE idOmbrellone=27 AND data BETWEEN '2024-06-03' AND '2024-06-09';
UPDATE GiornoDisponibilita SET numProgrContratto=29 WHERE idOmbrellone=28 AND data = '2024-06-17';
UPDATE GiornoDisponibilita SET numProgrContratto=30 WHERE idOmbrellone=5 AND data BETWEEN '2024-07-04' AND '2024-07-10';

-- ====================================================================
-- FASE FINALE: APPLICAZIONE DELLE MODIFICHE
-- ====================================================================
COMMIT;