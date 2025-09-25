<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Benvenuti al Lido Paradiso</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
    </head>
<body>
<div class="container">
    <header>Lido Paradiso</header>
    <nav>
        <a href="index.php" class="active">Home</a>
        <?php if (isset($_SESSION['codice_cliente'])): ?>
            <a href="mappa.php">Mappa Spiaggia</a>
            <a href="le_mie_prenotazioni.php">Le mie Prenotazioni</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['nome_cliente']) ?>)</a>
        <?php else: ?>
            <a href="accesso.php">Accedi</a>
            <a href="registrazione.php">Registrati</a>
        <?php endif; ?>
    </nav>
    <main>
        <div class="hero">
            <h1>La tua oasi di relax al mare</h1>
            <p>Per garantirti il miglior posto in spiaggia, accedi con il tuo Codice Cliente o registrati per ottenerne uno. Una volta dentro, potrai visualizzare la mappa e prenotare il tuo ombrellone.</p>
            
            <?php if (isset($_SESSION['codice_cliente'])): ?>
                <a href="mappa.php" class="cta-button">Vai alla Mappa</a>
            <?php else: ?>
                <a href="accesso.php" class="cta-button">Accedi o Registrati</a>
            <?php endif; ?>
        </div>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>