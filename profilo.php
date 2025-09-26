<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Protezione della pagina
if (!isset($_SESSION['codice_cliente'])) {
    header('Location: src/auth/accesso.php');
    exit();
}

// 2. Gestione messaggi di stato dalla sessione
$messaggio_successo = '';
if (isset($_SESSION['messaggio_successo'])) {
    $messaggio_successo = $_SESSION['messaggio_successo'];
    unset($_SESSION['messaggio_successo']);
}

$messaggio_errore = '';
if (isset($_SESSION['messaggio_errore'])) {
    $messaggio_errore = $_SESSION['messaggio_errore'];
    unset($_SESSION['messaggio_errore']);
}

// 3. Recupero dati utente dalla sessione
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
    <style>
        .profile-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 30px; }
        .main-actions { display: flex; align-items: center; gap: 15px; }
        .codice-cliente-box { background: #f4f0e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 2px dashed #ac6730; text-align: center; }
        .codice-cliente-box p { margin: 0; font-size: 1.1em; }
        .codice-cliente-box span { font-weight: bold; font-size: 1.4em; color: #3b2a1a; display: block; margin-top: 5px; }
        .profile-actions .btn-delete {
            background: #dc3545;
        }
        .profile-actions .btn-delete:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
<div class="container">
    <header>Il mio Profilo</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php">Le mie Prenotazioni</a>
        <a href="profilo.php" class="active">Il mio profilo (<?= htmlspecialchars($nome_cliente) ?>)</a>
    </nav>
    <main>
        <form action="src/user/processa_modifica.php" method="POST" class="form-prenotazione">
            <h3>Gestisci le tue informazioni</h3>
            <?php if ($messaggio_successo): ?><div class="messaggio successo" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; max-width: 100%; margin-bottom: 20px;"><p><?= htmlspecialchars($messaggio_successo) ?></p></div><?php endif; ?>
            <?php if ($messaggio_errore): ?><div class="messaggio errore" style="max-width: 100%; margin-bottom: 20px;"><?= $messaggio_errore ?></div><?php endif; ?>
            <div class="codice-cliente-box"><p>Il tuo Codice Cliente personale è:</p><span><?= htmlspecialchars($codice_cliente) ?></span></div>
            <div class="form-group"><label for="nome">Nome:</label><input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome_cliente) ?>" required></div>
            <div class="form-group"><label for="cognome">Cognome:</label><input type="text" id="cognome" name="cognome" value="<?= htmlspecialchars($cognome_cliente) ?>" required></div>
            <div class="form-group"><label for="dataNascita">Data di Nascita:</label><input type="date" id="dataNascita" name="dataNascita" value="<?= htmlspecialchars($dataNascita_cliente) ?>" style="width:100%; padding:10px; border:1px solid #7c3f06; border-radius:6px; font-size:1em; box-sizing: border-box;" required></div>
            <div class="profile-actions">
                <a href="src/user/elimina_account.php" class="button btn-delete">Elimina Account</a>
                <div class="main-actions">
                    <a href="src/auth/logout.php" class="button">Logout</a>
                    <button type="submit">Salva Modifiche</button>
                </div>
            </div>
        </form>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
