<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso - LIDO CODICI SBALLATI</title>
    <link rel="stylesheet" href="../../assets/css/stile.css?v=<?= filemtime('../../assets/css/stile.css') ?>">
</head>
<body>
<div class="container">
    <header>Accedi</header>
    <nav>
        <a href="../../index.php">Home</a>
        <a href="accesso.php" class="active">Accedi</a>
        <a href="registrazione.php">Registrati</a>
        <a href="../../admin/login.php">Accesso Admin</a>
    </nav>
    <main>
        <form action="processa_accesso.php" method="POST" class="form-prenotazione">
            <h3>Accedi con il tuo Codice Cliente</h3>
            
            <?php if (isset($_GET['errore'])): ?>
                <div class="messaggio errore" style="max-width: 600px; margin: 0 auto 20px auto;">
                    <p>Codice Cliente non valido o non trovato. Riprova.</p>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="codice_cliente">Il tuo Codice Cliente:</label>
                <input type="text" id="codice_cliente" name="codice_cliente" placeholder="Es. CLAB167..." required>
            </div>
            
            <div class="form-group">
                <button type="submit">Entra</button>
            </div>
        </form>
        <div style="text-align: center; margin-top: -10px; margin-bottom: 20px;">
         <a href="recupero_codice.php" style="font-size: 0.9em; color: #7c3f06;">Hai dimenticato il codice?</a>
        </div>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
