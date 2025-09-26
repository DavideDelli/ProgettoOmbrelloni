<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../stile.css?v=<?= filemtime('../stile.css') ?>">
</head>
<body>
<div class="container">
    <header>Area Amministrativa</header>
    <main style="padding-top: 50px;">
        <form action="processa_login.php" method="POST" class="form-prenotazione">
            <h3>Login Amministratore</h3>
            <?php if(isset($_GET['errore'])): ?>
                <div class="messaggio errore"><p>Password errata.</p></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Accedi</button>
            </div>
        </form>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>

