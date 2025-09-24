<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Recupero Codice Cliente</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
</head>
<body>
<div class="container">
    <header>Recupera Codice</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="accesso.php">Accedi</a>
        <a href="registrazione.php">Registrati</a>
    </nav>
    <main>
        <form action="processa_recupero.php" method="POST" class="form-prenotazione">
            <h3>Trova il tuo codice</h3>
            <p style="margin-bottom:20px;">Inserisci i dati esatti con cui ti sei registrato.</p>
            
            <?php if(isset($_GET['errore'])): ?>
                <div class="messaggio errore" style="max-width: 600px; margin: 0 auto 20px auto;">
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
                <input type="date" id="dataNascita" name="dataNascita" style="width:100%;" required>
            </div>
            
            <div class="form-group">
                <button type="submit">Trova il mio Codice</button>
            </div>
        </form>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>