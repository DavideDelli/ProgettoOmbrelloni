<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: src/auth/accesso.php');
    exit();
}

$messaggio_successo = $_SESSION['messaggio_successo'] ?? '';
unset($_SESSION['messaggio_successo']);
$messaggio_errore = $_SESSION['messaggio_errore'] ?? '';
unset($_SESSION['messaggio_errore']);

$nome = $_SESSION['nome_cliente'];
$cognome = $_SESSION['cognome_cliente'];
$dataNascita = $_SESSION['dataNascita_cliente'];
$codice = $_SESSION['codice_cliente'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Il mio Profilo</title>
    <link rel="stylesheet" href="assets/css/stile.css?v=<?= filemtime('assets/css/stile.css') ?>">
</head>
<body>
<div class="container">
    <header><h1>Il mio Profilo</h1></header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php">Prenotazioni</a>
        <a href="profilo.php" class="active">Profilo</a>
    </nav>
    <main>
        <div class="glass-panel" style="max-width: 600px;">
            <h3>Dati Personali</h3>
            
            <?php if ($messaggio_successo): ?>
                <div class="messaggio-conferma"><?= htmlspecialchars($messaggio_successo) ?></div>
            <?php endif; ?>
            <?php if ($messaggio_errore): ?>
                <div class="messaggio errore"><?= $messaggio_errore ?></div>
            <?php endif; ?>

            <div style="background: rgba(255,255,255,0.5); border-radius: 16px; padding: 25px; text-align: center; margin-bottom: 30px; border: 1px dashed #8d6e63;">
                <p style="margin: 0; color: #5d4037; font-weight: 700; letter-spacing: 1px; font-size: 0.9rem;">IL TUO CODICE CLIENTE</p>
                <span style="font-size: 2.5rem; font-weight: 900; color: #3e2723; letter-spacing: 3px;"><?= htmlspecialchars($codice) ?></span>
            </div>

            <form action="src/user/processa_modifica.php" method="POST">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                </div>
                <div class="form-group">
                    <label for="cognome">Cognome</label>
                    <input type="text" id="cognome" name="cognome" value="<?= htmlspecialchars($cognome) ?>" required>
                </div>
                <div class="form-group">
                    <label for="dataNascita">Data di Nascita</label>
                    <input type="date" id="dataNascita" name="dataNascita" value="<?= htmlspecialchars($dataNascita) ?>" required>
                </div>
                
                <div style="margin-top: 40px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 25px; display: flex; justify-content: space-between; align-items: center;">
                    <a href="src/user/elimina_account.php" style="color: #c62828; font-weight: 600; font-size: 0.9rem; opacity: 0.8; transition: opacity 0.3s;">Elimina Account</a>
                    
                    <div style="display: flex; gap: 15px;">
                        <a href="src/auth/logout.php" class="button button-secondary">Logout</a>
                        <button type="submit">Salva</button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <footer>© 2025 - Lido Codici Sballati</footer>
</div>
</body>
</html>