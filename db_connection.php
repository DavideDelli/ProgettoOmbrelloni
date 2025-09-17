<?php
// File: db_connection.php

// --- CONFIGURAZIONE DATABASE ---
// Modifica questi valori in base al tuo ambiente
$db_host = 'localhost';      // Solitamente 'localhost'
$db_name = 'ombrelloni'; // Il nome del database che hai creato
$db_user = 'root';           // L'utente del database (di default 'root' in XAMPP)
$db_password = '7733';           // La password (di default vuota in XAMPP)
$charset = 'utf8mb4';

// --- STRINGA DI CONNESSIONE (DSN) ---
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

// Opzioni per la connessione PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- CREAZIONE ISTANZA PDO ---
try {
    // Tenta di stabilire la connessione
    $pdo = new PDO($dsn, $db_user, $db_password, $options);
} catch (\PDOException $e) {
    // Se la connessione fallisce, mostra un errore e termina lo script
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>