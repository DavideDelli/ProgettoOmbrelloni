<?php
session_start();

// Controllo di autenticazione per tutte le pagine admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Percorso corretto per il file di connessione
require_once __DIR__ . '/../../src/db_connection.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

function getNomeTariffa($codice) {
    $nomi = [
        'STD_D' => 'Giornaliero Standard', 'STD_W' => 'Settimanale Standard',
        'VIP_D' => 'Giornaliero VIP', 'VIP_W' => 'Settimanale VIP',
        'STD_W_PREM' => 'Settimanale Premium', 'STD_W_APE'  => 'Settimanale Ape',
        'VIP_W_PREM' => 'Settimanale VIP Premium', 'VIP_W_APE'  => 'Settimanale VIP Ape',
        'STD_D_PREM' => 'Giornaliero Premium (con asciugamani)', 'STD_D_APE'  => 'Giornaliero Ape (con aperitivo)',
        'VIP_D_PREM' => 'Giornaliero VIP Premium (con asciugamani)', 'VIP_D_APE'  => 'Giornaliero VIP Ape (con aperitivo)',
    ];
    return $nomi[$codice] ?? $codice;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> - Lido Codici Sballati</title>
    <link rel="stylesheet" href="../assets/css/stile.css?v=<?= filemtime('../assets/css/stile.css') ?>">
    <style>
        /* Stili specifici per l'admin con effetto vetro */
        body.glass-ui .admin-container { max-width: 1200px; margin: 0 auto; }
        body.glass-ui .admin-table {
            width: 100%;
            border-collapse: separate; /* Necessario per i bordi arrotondati */
            border-spacing: 0;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            overflow: hidden; /* Per applicare il border-radius alle celle */
        }
        body.glass-ui .admin-table th, body.glass-ui .admin-table td {
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px;
            text-align: left;
            color: #FFF;
        }
        body.glass-ui .admin-table th {
            background-color: rgba(255, 255, 255, 0.15);
        }
        body.glass-ui .admin-table tr:last-child td {
            border-bottom: none;
        }
        body.glass-ui .admin-table input, body.glass-ui .admin-table select {
            width: 100%;
            box-sizing: border-box;
        }
        body.glass-ui main { padding: 2rem; }
        body.glass-ui .form-prenotazione, body.glass-ui .riepilogo-box {
            max-width: 100%;
        }
    </style>
</head>
<body class="glass-ui">
<div class="container">
    <header>Pannello Admin</header>
    <nav>
        <a href="index.php">Dashboard</a>
        <a href="gestione_tariffe.php">Gestione Tariffe</a>
        <a href="gestione_date.php">Gestione Date</a>
        <a href="../index.php" target="_blank">Vedi Sito</a>
        <a href="logout.php">Logout</a>
    </nav>
    <main>
        <div class="admin-container">
