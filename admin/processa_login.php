<?php
session_start();

// Password hardcoded. In un'applicazione reale, usare hash e database.
$admin_password_corretta = 'admin123'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password_corretta) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit();
    }
}

header('Location: login.php?errore=1');
exit();

