<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/stile.css?v=<?= filemtime('../assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Area Amministratore</header>
    <main>
        <form action="processa_login.php" method="POST" class="form-prenotazione glass-panel" style="max-width: 500px;">
            <h3>Login Amministratore</h3>
            <?php if(isset($_GET['errore'])): ?>
                <div class="messaggio errore glass-panel" style="margin-bottom: 20px;"><p>Password errata.</p></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group" style="text-align: center;">
                <button type="submit">Accedi</button>
            </div>
        </form>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
