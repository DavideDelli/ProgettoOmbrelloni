<?php
// Abilita la visualizzazione degli errori per il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

$ombrellone = null;
$errore = '';
$data_selezionata = '';

// Controlla se abbiamo ricevuto i dati necessari (ID ombrellone e data)
if (isset($_GET['id']) && isset($_GET['data']) && !empty($_GET['id']) && !empty($_GET['data'])) {
    
    $id_ombrellone = $_GET['id'];
    $data_selezionata = $_GET['data'];

    // CORREZIONE: 'Ombrellone' -> 'ombrellone' e 'Tipologia' -> 'tipologia'
    $sql = "
        SELECT o.id, o.settore, o.numFila, o.numPostoFila, t.nome AS nome_tipologia, t.descrizione 
        FROM ombrellone o 
        JOIN tipologia t ON o.codTipologia = t.codice 
        WHERE o.id = :id_ombrellone
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_ombrellone' => $id_ombrellone]);
    $ombrellone = $stmt->fetch();

    if (!$ombrellone) {
        $errore = "Ombrellone non trovato.";
    }

} else {
    $errore = "Dati mancanti. Impossibile procedere con la prenotazione.";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Prenotazione</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
    <div class="container">
        <header>Riepilogo Prenotazione</header>
        <nav>
            <a href="index.php">Torna alla Mappa</a>
            <a href="#">Servizi</a>
            <a href="#">Contatti</a>
        </nav>
        <main>
            <?php if ($errore): ?>
                <div class="messaggio errore">
                    <h2>Errore</h2>
                    <p><?= htmlspecialchars($errore) ?></p>
                    <a href="index.php" class="button-link">Torna alla ricerca</a>
                </div>
            <?php elseif ($ombrellone): ?>
                <h2>Stai per prenotare:</h2>
                <div class="riepilogo-box">
                    <p><strong>Data:</strong> <?= htmlspecialchars($data_selezionata) ?></p>
                    <p><strong>Ombrellone ID:</strong> <?= htmlspecialchars($ombrellone['id']) ?></p>
                    <p><strong>Posizione:</strong> Settore <?= htmlspecialchars($ombrellone['settore']) ?>, Fila <?= htmlspecialchars($ombrellone['numFila']) ?>, Posto <?= htmlspecialchars($ombrellone['numPostoFila']) ?></p>
                    <p><strong>Tipologia:</strong> <?= htmlspecialchars($ombrellone['nome_tipologia']) ?> (<?= htmlspecialchars($ombrellone['descrizione']) ?>)</p>
                </div>

                <form action="conferma.php" method="POST" class="form-prenotazione">
                    <h3>Inserisci i tuoi dati</h3>
                    
                    <input type="hidden" name="id_ombrellone" value="<?= htmlspecialchars($ombrellone['id']) ?>">
                    <input type="hidden" name="data_prenotazione" value="<?= htmlspecialchars($data_selezionata) ?>">
                    
                    <div class="form-group">
                        <label for="codice_cliente">Il tuo Codice Cliente:</label>
                        <input type="text" id="codice_cliente" name="codice_cliente" placeholder="Es. CLIENTE0001" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">Conferma la Prenotazione</button>
                    </div>
                </form>
            <?php endif; ?>
        </main>
        <footer>© 2025 - Università degli Studi di Bergamo - Tutti i diritti riservati</footer>
    </div>
</body>
</html>