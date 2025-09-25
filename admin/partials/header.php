<?php
session_start();

// Controllo di autenticazione per tutte le pagine admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../../db_connection.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Funzione helper per "tradurre" i codici delle tariffe
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
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> - Lido Paradiso</title>
    <link rel="stylesheet" href="../stile.css?v=<?= filemtime('../stile.css') ?>">
    <style>
        /* Stili specifici per l'admin */
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .admin-table th, .admin-table td { border: 1px solid #d3a27f; padding: 12px; text-align: left; }
        .admin-table th { background-color: #c08457; color: #3b2a1a; }
        .admin-table tr:nth-child(even) { background-color: #f2dfd3; }
        .admin-table input[type="number"], .admin-table input[type="text"] { width: 100px; padding: 5px; }
        main { padding: 2rem; }
        .admin-container { max-width: 1000px; margin: 0 auto; }
    </style>
</head>
<body>
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

