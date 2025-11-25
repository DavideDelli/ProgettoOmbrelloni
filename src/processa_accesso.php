<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['codice_cliente'])) {
    $codice_cliente = trim($_POST['codice_cliente']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM cliente WHERE codice = :codice");
        $stmt->execute(['codice' => $codice_cliente]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            // Utente trovato, imposto la sessione
            $_SESSION['codice_cliente'] = $cliente['codice'];
            $_SESSION['nome_cliente'] = $cliente['nome'];
            $_SESSION['cognome_cliente'] = $cliente['cognome'];
            $_SESSION['dataNascita_cliente'] = $cliente['dataNascita'];
            
            header("Location: ../mappa.php");
            exit();
        } else {
            // Utente non trovato
            header("Location: ../accesso.php?errore=1");
            exit();
        }
    } catch (PDOException $e) {
        die("Errore durante l'accesso: " . $e->getMessage());
    }
} else {
    header("Location: ../accesso.php");
    exit();
}
?>
