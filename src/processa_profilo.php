<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../db_connection.php';

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: ../accesso.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profilo.php');
    exit();
}

$nome = trim($_POST['nome']);
$cognome = trim($_POST['cognome']);
$dataNascita = $_POST['dataNascita'];
$codice_cliente = $_SESSION['codice_cliente'];
$errori = [];

// Validazione
if (empty($nome)) { $errori[] = "Il campo Nome è obbligatorio."; }
if (empty($cognome)) { $errori[] = "Il campo Cognome è obbligatorio."; }
if (empty($dataNascita)) { $errori[] = "Il campo Data di Nascita è obbligatorio."; }

if (!empty($nome) && !preg_match("/^[a-zA-Z' ]+$/", $nome)) {
    $errori[] = "Il Nome può contenere solo lettere, spazi e apostrofi.";
}
if (!empty($cognome) && !preg_match("/^[a-zA-Z' ]+$/", $cognome)) {
    $errori[] = "Il Cognome può contenere solo lettere, spazi e apostrofi.";
}

if (!empty($dataNascita)) {
    try {
        $data_nascita_obj = new DateTime($dataNascita);
        $oggi = new DateTime();
        if ($data_nascita_obj > $oggi) {
            $errori[] = "La data di nascita non può essere nel futuro.";
        }
    } catch (Exception $e) {
        $errori[] = "Formato data di nascita non valido.";
    }
}

if (empty($errori)) {
    try {
        $sql = "UPDATE cliente SET nome = :nome, cognome = :cognome, dataNascita = :dataNascita WHERE codice = :codice";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'cognome' => $cognome,
            'dataNascita' => $dataNascita,
            'codice' => $codice_cliente
        ]);

        // Aggiorna i dati in sessione
        $_SESSION['nome_cliente'] = $nome;
        $_SESSION['cognome_cliente'] = $cognome;
        $_SESSION['dataNascita_cliente'] = $dataNascita;

        $_SESSION['messaggio_successo'] = "Profilo aggiornato con successo!";

    } catch (PDOException $e) {
        $_SESSION['messaggio_errore'] = "Errore durante l'aggiornamento del profilo.";
    }
} else {
    $_SESSION['messaggio_errore'] = implode('<br>', $errori);
}

header('Location: ../profilo.php');
exit();
