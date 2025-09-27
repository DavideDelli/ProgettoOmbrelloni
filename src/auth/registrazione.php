<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Lido Codici Sballati</title>
    <link rel="stylesheet" href="../../assets/css/stile.css?v=<?= filemtime('../../assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Crea il tuo Account</header>
    <nav>
        <a href="../../index.php">Home</a>
        <a href="accesso.php">Accedi</a>
        <a href="registrazione.php" class="active">Registrati</a>
    </nav>
    <main>
        <form action="salva_registrazione.php" method="POST" class="form-prenotazione glass-panel">
            <h3>Inserisci i tuoi dati per ottenere il Codice Cliente</h3>
            
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="cognome">Cognome:</label>
                <input type="text" id="cognome" name="cognome" required>
            </div>
            <div class="form-group">
                <label for="dataNascita">Data di Nascita:</label>
                <input type="date" id="dataNascita" name="dataNascita" required>
            </div>
            <div class="form-group">
                <label for="indirizzo">Indirizzo (opzionale):</label>
                <input type="text" id="indirizzo" name="indirizzo">
            </div>
            
            <div class="form-group" style="text-align: center;">
                <button type="submit">Crea il mio Codice</button>
            </div>
        </form>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
