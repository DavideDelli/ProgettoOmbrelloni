<?php
// Impostazioni per la connessione al database
$host = 'localhost';
$dbname = 'my_ombrelloni';
$user = 'ombrelloni_user'; // <-- NUOVO UTENTE
$password = '';    // <-- LA NUOVA PASSWORD CHE HAI SCELTO

// ... il resto del file rimane invariato ...
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
