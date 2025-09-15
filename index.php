<?php
ini_set('display_errors', 1); // Aggiungi questa riga
error_reporting(E_ALL);     // Aggiungi questa riga

// Il resto del tuo codice...
require_once 'db_connection.php';
// ...
// Includi il file di connessione al database all'inizio
require_once 'db_connection.php';

// Inizializza la variabile per i risultati
$ombrelloni_disponibili = [];
$data_selezionata = '';

// Controlla se il form di ricerca è stato inviato con una data valida
if (isset($_GET['data_ricerca']) && !empty($_GET['data_ricerca'])) {
    
    $data_selezionata = $_GET['data_ricerca'];
    
    // Query SQL per trovare gli ombrelloni liberi in una data specifica
    // Usiamo un PREPARED STATEMENT per la sicurezza (previene SQL Injection)
    $sql = "
        SELECT o.id, o.settore, o.numFila, o.numPostoFila, t.nome AS nome_tipologia, t.descrizione
        FROM Ombrellone o
        JOIN Tipologia t ON o.codTipologia = t.codice
        LEFT JOIN GiornoDisponibilita gd ON o.id = gd.idOmbrellone AND gd.data = :data_selezionata
        WHERE gd.idOmbrellone IS NULL OR gd.numProgrContratto IS NULL
        ORDER BY o.settore, o.numFila, o.numPostoFila
    ";
    
    // Prepara ed esegui la query
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['data_selezionata' => $data_selezionata]);
    
    // Ottieni i risultati
    $ombrelloni_disponibili = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noleggio Ombrelloni - Ricerca Disponibilità</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
    <div class="container">
        <header>
            Noleggio Ombrelloni
        </header>

        <nav>
            <a href="#">Home</a>
            <a href="#">Servizi</a>
            <a href="#">Chi Siamo</a>
            <a href="#">Contatti</a>
        </nav>

        <div class="search-filter">
            <form method="GET" action="index.php">
                <label for="data_ricerca" style="color: white; font-weight: bold; margin-right: 10px;">Seleziona una data:</label>
                <input type="date" id="data_ricerca" name="data_ricerca" value="<?= htmlspecialchars($data_selezionata) ?>" required />
                <button type="submit">Cerca Disponibilità</button>
            </form>
        </div>

        <main>
            <h2>Risultati Disponibilità</h2>
            
            <?php if ($data_selezionata): // Se è stata effettuata una ricerca ?>
                <h3>Ombrelloni disponibili per il giorno: <?= htmlspecialchars($data_selezionata) ?></h3>
                
                <?php if (count($ombrelloni_disponibili) > 0): // Se ci sono risultati ?>
                    <div style="text-align: left; max-width: 800px; margin: auto;">
                        <?php foreach ($ombrelloni_disponibili as $ombrellone): ?>
                            <div style="border: 1px solid #7c3f06; padding: 10px; margin-bottom: 10px; background-color: #f2dfd3;">
                                <strong>Ombrellone ID:</strong> <?= htmlspecialchars($ombrellone['id']) ?><br>
                                <strong>Posizione:</strong> Settore <?= htmlspecialchars($ombrellone['settore']) ?>, Fila <?= htmlspecialchars($ombrellone['numFila']) ?>, Posto <?= htmlspecialchars($ombrellone['numPostoFila']) ?><br>
                                <strong>Tipologia:</strong> <?= htmlspecialchars($ombrellone['nome_tipologia']) ?> (<?= htmlspecialchars($ombrellone['descrizione']) ?>)
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: // Se non ci sono risultati ?>
                    <p>Nessun ombrellone disponibile per la data selezionata. Prova un altro giorno.</p>
                <?php endif; ?>

            <?php else: // Se la pagina è stata appena aperta senza ricerca ?>
                <p>Usa il filtro di ricerca qui sopra per trovare un ombrellone disponibile nella data che preferisci.</p>
            <?php endif; ?>

        </main>

        <footer>
            © 2025 - Università degli Studi di Bergamo - Tutti i diritti riservati
        </footer>
    </div>
</body>
</html>