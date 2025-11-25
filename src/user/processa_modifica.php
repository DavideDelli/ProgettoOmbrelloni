<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../db_connection.php';

// 1. Authentication Check
if (!isset($_SESSION['codice_cliente'])) {
    header('Location: ../auth/accesso.php');
    exit();
}

// 2. Ensure the request is a POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ../../profilo.php');
    exit();
}

// 3. Data Retrieval and Validation
$nome = trim($_POST['nome']);
$cognome = trim($_POST['cognome']);
$dataNascita = $_POST['dataNascita'];
$codice_cliente = $_SESSION['codice_cliente'];
$errori = [];

// Validation (similar to registration)
if (empty($nome)) { $errori[] = "Il campo Nome è obbligatorio."; }
if (empty($cognome)) { $errori[] = "Il campo Cognome è obbligatorio."; }
if (empty($dataNascita)) { $errori[] = "Il campo Data di Nascita è obbligatorio."; }

if (!empty($nome) && !preg_match("/^[a-zA-Z' ]+$/u", $nome)) {
    $errori[] = "Il Nome può contenere solo lettere, spazi e apostrofi.";
}
if (!empty($cognome) && !preg_match("/^[a-zA-Z' ]+$/u", $cognome)) {
    $errori[] = "Il Cognome può contenere solo lettere, spazi e apostrofi.";
}

if (!empty($dataNascita)) {
    try {
        $data_nascita_obj = new DateTime($dataNascita);
        $oggi = new DateTime();
        if ($data_nascita_obj > $oggi) {
            $errori[] = "La data di nascita non può essere nel futuro.";
        } else {
            $eta = $oggi->diff($data_nascita_obj)->y;
            if ($eta < 18) {
                $errori[] = "Devi avere almeno 18 anni.";
            }
            if ($eta > 120) {
                $errori[] = "La data di nascita inserita non è valida.";
            }
        }
    } catch (Exception $e) {
        $errori[] = "Formato data di nascita non valido.";
    }
}

// 4. Update DB if validation passes
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

        // 5. Update session data to reflect changes immediately
        $_SESSION['nome_cliente'] = $nome;
        $_SESSION['cognome_cliente'] = $cognome;
        $_SESSION['dataNascita_cliente'] = $dataNascita;

        $_SESSION['messaggio_successo'] = "Profilo aggiornato con successo!";
        header('Location: ../../profilo.php');
        exit();

    } catch (PDOException $e) {
        // Log the real error, but show a generic message to the user
        error_log("Errore aggiornamento profilo: " . $e->getMessage());
        $_SESSION['messaggio_errore'] = "Si è verificato un errore durante l'aggiornamento del profilo. Riprova.";
        header('Location: ../../profilo.php');
        exit();
    }
} else {
    // 6. Handle validation errors
    $_SESSION['messaggio_errore'] = "<ul><li>" . implode("</li><li>", $errori) . "</li></ul>";
    header('Location: ../../profilo.php');
    exit();
}
