<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../db_connection.php';

// 1. Check if the form was submitted and the client code is not empty
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty(trim($_POST['codice_cliente']))) {
    header("Location: accesso.php");
    exit();
}

$codice_cliente = trim($_POST['codice_cliente']);

try {
    // 2. Prepare and execute the query to find the client by their code
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE codice = :codice");
    $stmt->execute(['codice' => $codice_cliente]);
    $cliente = $stmt->fetch();

    // 3. Check if the client was found
    if ($cliente) {
        // Client found. Regenerate session ID to prevent session fixation.
        session_regenerate_id(true);

        // Store client data in the session
        $_SESSION['codice_cliente'] = $cliente['codice'];
        $_SESSION['nome_cliente'] = $cliente['nome'];
        $_SESSION['cognome_cliente'] = $cliente['cognome'];
        $_SESSION['dataNascita_cliente'] = $cliente['dataNascita'];
        
        // Redirect to the main map page on successful login
        header("Location: ../../mappa.php");
        exit();
    } else {
        // Client not found, redirect back to the login page with an error flag
        header("Location: accesso.php?errore=1");
        exit();
    }
} catch (PDOException $e) {
    // On a database error, it's safer to redirect to the login page with an error,
    // rather than exposing the DB error message.
    // For a production environment, you should log the error: error_log($e->getMessage());
    header("Location: accesso.php?errore=1");
    exit();
}
