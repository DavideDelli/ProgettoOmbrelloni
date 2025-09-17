<?php
// ====================================================================
//      INIZIO BLOCCO PER MOSTRARE GLI ERRORI (DA INSERIRE QUI)
// ====================================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ====================================================================
//      FINE BLOCCO PER MOSTRARE GLI ERRORI
// ====================================================================


// Includi il file di connessione al database
require_once 'db_connection.php';

// Inizializza le variabili
$data_selezionata = '';
$settori = [];
$ombrelloni_occupati_ids = [];

// Controlla se il form di ricerca è stato inviato con una data valida
if (isset($_GET['data_ricerca']) && !empty($_GET['data_ricerca'])) {
    
    $data_selezionata = $_GET['data_ricerca'];
    
    // NUOVA LOGICA: FASE 1
    // Recuperiamo TUTTI gli ombrelloni per costruire la mappa completa della spiaggia.
    $sql_tutti = "SELECT id, settore, numFila, numPostoFila, codTipologia FROM Ombrellone ORDER BY settore, numFila, numPostoFila";
    $stmt_tutti = $pdo->prepare($sql_tutti);
    $stmt_tutti->execute();
    $tutti_gli_ombrelloni = $stmt_tutti->fetchAll();
    
    // Organizziamo gli ombrelloni in una struttura dati più comoda (un array multidimensionale)
    // Es: $settori['A'][1][5] = dati dell'ombrellone in Settore A, Fila 1, Posto 5
    foreach ($tutti_gli_ombrelloni as $ombrellone) {
        $settori[$ombrellone['settore']][$ombrellone['numFila']][$ombrellone['numPostoFila']] = $ombrellone;
    }

    // NUOVA LOGICA: FASE 2
    // Ora recuperiamo solo gli ID degli ombrelloni GIA' OCCUPATI per la data selezionata.
    $sql_occupati = "SELECT idOmbrellone FROM GiornoDisponibilita WHERE data = :data_selezionata AND numProgrContratto IS NOT NULL";
    $stmt_occupati = $pdo->prepare($sql_occupati);
    $stmt_occupati->execute(['data_selezionata' => $data_selezionata]);
    
    // Usiamo PDO::FETCH_COLUMN per ottenere un array semplice di ID [1, 5, 12, ...]
    $ombrelloni_occupati_ids = $stmt_occupati->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noleggio Ombrelloni - Mappa Disponibilità</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
    <div class="container">
        <header>
            Noleggio Ombrelloni
        </header>

        <nav>
            <a href="index.php">Home</a>
            <a href="#">Servizi</a>
            <a href="#">Chi Siamo</a>
            <a href="#">Contatti</a>
        </nav>

        <div class="search-filter">
            <form method="GET" action="index.php">
                <label for="data_ricerca">Seleziona una data:</label>
                <input type="date" id="data_ricerca" name="data_ricerca" value="<?= htmlspecialchars($data_selezionata) ?>" required />
                <button type="submit">Cerca Disponibilità</button>
            </form>
        </div>

        <main>
            <h2>Mappa Disponibilità</h2>
            
            <?php if ($data_selezionata): // Se è stata effettuata una ricerca ?>
                <h3>Disponibilità per il giorno: <strong><?= htmlspecialchars($data_selezionata) ?></strong></h3>
                
                <div class="legenda">
                    <span class="box disponibile"></span> Disponibile
                    <span class="box occupato"></span> Occupato
                    <span class="box vip"></span> VIP
                </div>

                <?php if (!empty($settori)): ?>
                    
                    <div class="mappa-spiaggia">
                        <?php foreach ($settori as $nome_settore => $file): ?>
                            <div class="settore-grid">
                                <h4>Settore <?= htmlspecialchars($nome_settore) ?></h4>
                                <?php
                                // Calcoliamo il numero massimo di file e posti per questo settore per costruire una griglia regolare
                                $max_fila = empty($file) ? 0 : max(array_keys($file));
                                $max_posto = 0;
                                foreach ($file as $posti_in_fila) {
                                    $current_max_posto = empty($posti_in_fila) ? 0 : max(array_keys($posti_in_fila));
                                    if ($current_max_posto > $max_posto) {
                                        $max_posto = $current_max_posto;
                                    }
                                }
                                ?>
                                <?php for ($f = 1; $f <= $max_fila; $f++): ?>
                                    <div class="fila">
                                        <div class="numero-fila">Fila <?= $f ?></div>
                                        <?php for ($p = 1; $p <= $max_posto; $p++): ?>
                                            <?php
                                            // Controlliamo se in questa coordinata [fila][posto] esiste un ombrellone
                                            if (isset($file[$f][$p])) {
                                                $ombrellone = $file[$f][$p];
                                                // Controlliamo se l'ID di questo ombrellone è nella lista di quelli occupati
                                                $is_occupato = in_array($ombrellone['id'], $ombrelloni_occupati_ids);
                                                
                                                // Assegnamo le classi CSS corrette in base a stato e tipologia
                                                $class = 'ombrellone';
                                                $class .= $is_occupato ? ' occupato' : ' disponibile';
                                                $class .= ($ombrellone['codTipologia'] == 'VIP') ? ' vip' : '';
                                                
                                                // Creiamo un tooltip con i dettagli
                                                $tooltip = "Ombrellone #{$ombrellone['id']} | Posto: {$p} | Tipo: {$ombrellone['codTipologia']}";
                                                if ($is_occupato) {
                                                    $tooltip .= " (Non disponibile)";
                                                }
                                                
                                                echo "<div class='{$class}' title='{$tooltip}'>{$p}</div>";

                                            } else {
                                                // Se non c'è un ombrellone, creiamo uno spazio vuoto per mantenere l'allineamento
                                                echo "<div class='spazio-vuoto'></div>";
                                            }
                                            ?>
                                        <?php endfor; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <p>Mappa della spiaggia non disponibile.</p>
                <?php endif; ?>

            <?php else: // Se la pagina è stata appena aperta senza ricerca ?>
                <p>Usa il filtro di ricerca qui sopra per visualizzare la mappa della spiaggia per una data specifica.</p>
            <?php endif; ?>
        </main>

        <footer>
            © 2025 - Università degli Studi di Bergamo - Tutti i diritti riservati
        </footer>
    </div>
</body>
</html>