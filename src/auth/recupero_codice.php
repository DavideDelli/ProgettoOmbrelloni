<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Recupero Codice Cliente</title>
    <link rel="stylesheet" href="../../assets/css/stile.css?v=<?= filemtime('../../assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Recupera Codice</header>
    <nav>
        <a href="../../index.php">Home</a>
        <a href="accesso.php">Accedi</a>
        <a href="registrazione.php">Registrati</a>
    </nav>
    <main>
        <form action="processa_recupero.php" method="POST" class="form-prenotazione glass-panel">
            <h3>Trova il tuo codice</h3>
            <p style="margin-bottom:20px; text-align: center;">Inserisci i dati esatti con cui ti sei registrato.</p>
            
            <?php if (isset($_GET['errore'])): ?>
                <div class="messaggio errore glass-panel" style="margin-bottom: 20px;">
                    <p>Nessun cliente trovato con i dati inseriti. Controlla e riprova.</p>
                </div>
            <?php endif; ?>

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
            
            <div class="form-group" style="text-align: center;">
                <button type="submit">Trova il mio Codice</button>
            </div>
        </form>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
