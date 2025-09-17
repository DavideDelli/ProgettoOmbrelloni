<?php
// Impostazioni per la connessione al database
$host = 'localhost';       // Di solito è 'localhost' su Altervista
$dbname = 'my_ombrelloni'; // Il nome del tuo database
$user = 'root';    // Lo username per accedere al database
$password = '';    // La password per accedere al database

// Stringa di connessione (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Opzioni di PDO per la gestione degli errori
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Trasforma gli errori in eccezioni, più facile da gestire
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Restituisce i risultati come array associativi
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa PREPARED STATEMENTS nativi per maggiore sicurezza
];

// Blocco try-catch per gestire eventuali errori di connessione
try {
    // Crea l'oggetto PDO (la connessione vera e propria)
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    // Se la connessione fallisce, mostra un messaggio di errore e interrompi lo script
    // In un sito reale, qui scriveresti l'errore in un file di log invece di mostrarlo a schermo
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
