<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: src/auth/accesso.php');
    exit();
}

$messaggio_successo = $_SESSION['messaggio_successo'] ?? '';
if ($messaggio_successo) unset($_SESSION['messaggio_successo']);

$messaggio_errore = $_SESSION['messaggio_errore'] ?? '';
if ($messaggio_errore) unset($_SESSION['messaggio_errore']);

$nome_cliente = $_SESSION['nome_cliente'];
$cognome_cliente = $_SESSION['cognome_cliente'];
$dataNascita_cliente = $_SESSION['dataNascita_cliente'];
$codice_cliente = $_SESSION['codice_cliente'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il mio Profilo - Lido Codici Sballati</title>
    <link rel="stylesheet" href="assets/css/stile.css?v=<?= filemtime('assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Il mio Profilo</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php">Le mie Prenotazioni</a>
        <a href="profilo.php" class="active">Il mio profilo (<?= htmlspecialchars($nome_cliente) ?>)</a>
    </nav>
    <main>
        <form action="src/user/processa_modifica.php" method="POST" class="form-prenotazione glass-panel">
            <h3>Gestisci le tue informazioni</h3>
            
            <?php if ($messaggio_successo): ?>
                <div class="messaggio-conferma glass-panel" style="margin-bottom: 20px;"><p><?= htmlspecialchars($messaggio_successo) ?></p></div>
            <?php endif; ?>
            <?php if ($messaggio_errore): ?>
                <div class="messaggio errore glass-panel" style="margin-bottom: 20px;"><?= $messaggio_errore ?></div>
            <?php endif; ?>

            <div style="padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.1); text-align: center;">
                <p style="margin: 0; font-size: 1.1em;">Il tuo Codice Cliente personale è:</p>
                <span style="font-weight: bold; font-size: 1.4em; display: block; margin-top: 5px;"><?= htmlspecialchars($codice_cliente) ?></span>
            </div>

            <div class="form-group"><label for="nome">Nome:</label><input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome_cliente) ?>" required></div>
            <div class="form-group"><label for="cognome">Cognome:</label><input type="text" id="cognome" name="cognome" value="<?= htmlspecialchars($cognome_cliente) ?>" required></div>
            <div class="form-group"><label for="dataNascita">Data di Nascita:</label><input type="date" id="dataNascita" name="dataNascita" value="<?= htmlspecialchars($dataNascita_cliente) ?>" required></div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
                <a href="src/user/elimina_account.php" class="button-link" style="background: #B22222 !important;">Elimina Account</a>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="src/auth/logout.php" class="button-link" style="background: rgba(0,0,0,0.2) !important;">Logout</a>
                    <button type="submit">Salva Modifiche</button>
                </div>
            </div>
        </form>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
