<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lido Codici Sballati - Home</title>
    <link rel="stylesheet" href="assets/css/stile.css?v=<?= filemtime('assets/css/stile.css') ?>">
</head>
<body>
<div class="container">
    <header><h1>LIDO CODICI SBALLATI</h1></header>
    <nav>
        <a href="index.php" class="active">Home</a>
        <?php if (isset($_SESSION['codice_cliente'])): ?>
            <a href="mappa.php">Mappa Spiaggia</a>
            <a href="le_mie_prenotazioni.php">Le mie Prenotazioni</a>
            <a href="profilo.php">Il mio profilo (<?= htmlspecialchars($_SESSION['nome_cliente']) ?>)</a>
        <?php else: ?>
            <a href="src/auth/accesso.php">Accedi</a>
            <a href="src/auth/registrazione.php">Registrati</a>
        <?php endif; ?>
    </nav>
    <main>
        <div class="glass-panel" style="text-align: center; padding: 50px 30px; margin-top: 5vh;">
            <h1 style="font-size: 3rem; margin-bottom: 20px;">LIDO CODICI SBALLATI</h1>
            <p style="font-size: 1.3rem; color: #5d4037; margin-bottom: 40px;">
                La tua oasi esclusiva ti aspetta. 
                <br>Prenota il tuo posto al sole con un click.
            </p>
            
            <?php if (isset($_SESSION['codice_cliente'])): ?>
                <a href="mappa.php" class="button" style="padding: 15px 50px; font-size: 1.2rem;">Vai alla Mappa</a>
            <?php else: ?>
                <div style="display: flex; gap: 20px; justify-content: center;">
                    <a href="src/auth/accesso.php" class="button">Accedi</a>
                    <a href="src/auth/registrazione.php" class="button button-secondary">Registrati</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer>© 2025 - Lido Codici Sballati</footer>
</div>
</body>
</html>