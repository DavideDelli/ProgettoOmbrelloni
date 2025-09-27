<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Utilizzo di un percorso assoluto per maggiore robustezza
require_once __DIR__ . '/../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty(trim($_POST['codice_cliente']))) {
    header("Location: accesso.php");
    exit();
}

$codice_cliente = trim($_POST['codice_cliente']);

try {
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE codice = :codice");
    $stmt->execute(['codice' => $codice_cliente]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        session_regenerate_id(true);

        $_SESSION['codice_cliente'] = $cliente['codice'];
        $_SESSION['nome_cliente'] = $cliente['nome'];
        $_SESSION['cognome_cliente'] = $cliente['cognome'];
        $_SESSION['dataNascita_cliente'] = $cliente['dataNascita'];
        
        // Reindirizzamento alla pagina della mappa
        header("Location: ../../mappa.php");
        exit();
    } else {
        header("Location: accesso.php?errore=1");
        exit();
    }
} catch (PDOException $e) {
    error_log("Errore accesso: " . $e->getMessage());
    header("Location: accesso.php?errore=1");
    exit();
}
