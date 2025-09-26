<?php
session_start();

// NOTA: La password è hardcoded. In un ambiente di produzione, 
// dovrebbe essere hashata e memorizzata in modo sicuro.
$admin_password_corretta = 'password'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password_corretta) {
        // Password corretta, imposta la sessione e reindirizza alla dashboard
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit();
    }
}

// Se la password è errata o la richiesta non è un POST, torna al login con errore
header('Location: login.php?errore=1');
exit();
