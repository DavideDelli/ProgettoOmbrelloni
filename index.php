<?php
// Abilita la visualizzazione degli errori per il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Includi il file di connessione al database
require_once 'db_connection.php';

// Inizializza le variabili
$data_selezionata = '';
$settori = [];
$ombrelloni_occupati_ids = [];
$messaggio_errore = '';

// Controlla se il form di ricerca è stato inviato
if (isset($_GET['data_ricerca']) && !empty($_GET['data_ricerca'])) {
    
    $data_selezionata = $_GET['data_ricerca'];
    
    // Controlla se la data selezionata esiste nel database
    $sql_check_data = "SELECT 1 FROM giornodisponibilita WHERE data = :data_selezionata LIMIT 1";
    $stmt_check_data = $pdo->prepare($sql_check_data);
    $stmt_check_data->execute(['data_selezionata' => $data_selezionata]);

    if ($stmt_check_data->rowCount() > 0) {
        // La data è valida, procediamo a costruire la mappa
        $sql_tutti = "SELECT id, settore, numFila, numPostoFila, codTipologia FROM ombrellone ORDER BY settore, numFila, numPostoFila";
        $stmt_tutti = $pdo->query($sql_tutti);
        $tutti_gli_ombrelloni = $stmt_tutti->fetchAll();

        foreach ($tutti_gli_ombrelloni as $ombrellone) {
            $settori[$ombrellone['settore']][$ombrellone['numFila']][$ombrellone['numPostoFila']] = $ombrellone;
        }

        $sql_occupati = "SELECT idOmbrellone FROM giornodisponibilita WHERE data = :data_selezionata AND numProgrContratto IS NOT NULL";
        $stmt_occupati = $pdo->prepare($sql_occupati);
        $stmt_occupati->execute(['data_selezionata' => $data_selezionata]);
        $ombrelloni_occupati_ids = $stmt_occupati->fetchAll(PDO::FETCH_COLUMN);

    } else {
        $messaggio_errore = "La data selezionata non è valida o è al di fuori della stagione balneare. Prego, scegli un'altra data.";
    }
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
    <header>Noleggio Ombrelloni</header>
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

        <?php if ($data_selezionata): ?>
            <?php if (!empty($messaggio_errore)): ?>
                <div class="messaggio errore">
                    <p><?= htmlspecialchars($messaggio_errore) ?></p>
                </div>
            <?php else: ?>
                <h3>Disponibilità per il giorno: <strong><?= htmlspecialchars($data_selezionata) ?></strong></h3>
                <div class="legenda">
                    <span class="box disponibile"></span> Disponibile
                    <span class="box occupato"></span> Occupato
                    <span class="box vip"></span> VIP
                </div>

                <div id="vista-spiaggia">
                    <div class="mare"></div>
                    <div class="spiaggia">
                        <?php foreach (array_keys($settori) as $nome_settore): ?>
                            <button class="bottone-settore" data-settore="<?= htmlspecialchars($nome_settore) ?>">
                                Settore <?= htmlspecialchars($nome_settore) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="vista-griglie">
                    <?php foreach ($settori as $nome_settore => $file): ?>
                        <div id="settore-grid-<?= htmlspecialchars($nome_settore) ?>" class="sector-grid-container hidden">
                            <a href="#" class="torna-alla-mappa">‹ Torna alla scelta dei settori</a>
                            <div class="settore-grid">
                                <h4>Settore <?= htmlspecialchars($nome_settore) ?></h4>

                                <?php
                                $max_fila = empty($file) ? 0 : max(array_keys($file));
                                $max_posto = 0;
                                foreach ($file as $posti_in_fila) {
                                    if (!empty($posti_in_fila)) {
                                        $current_max_posto = max(array_keys($posti_in_fila));
                                        if ($current_max_posto > $max_posto) $max_posto = $current_max_posto;
                                    }
                                }
                                ?>

                                <?php for ($f = 1; $f <= $max_fila; $f++): ?>
                                    <div class="fila">
                                        <div class="numero-fila">Fila <?= $f ?></div>

                                        <?php for ($p = 1; $p <= $max_posto; $p++): ?>
                                            <?php
                                            if (isset($file[$f][$p])) {
                                                $ombrellone = $file[$f][$p];
                                                $tipologia = ($p <= 2) ? 'VIP' : $ombrellone['codTipologia'];
                                                $is_occupato = in_array($ombrellone['id'], $ombrelloni_occupati_ids);
                                                $class = 'ombrellone' 
                                                         . ($is_occupato ? ' occupato' : ' disponibile') 
                                                         . ($tipologia == 'VIP' ? ' vip' : '');
                                                $tooltip = "Ombrellone #{$ombrellone['id']} | Posto: {$p} | Tipo: {$tipologia}";

                                                if ($is_occupato) {
                                                    echo "<div class='{$class}' title='{$tooltip} (Non disponibile)'>{$p}</div>";
                                                } else {
                                                    $link = "prenota.php?id=" . urlencode($ombrellone['id']) . "&data=" . urlencode($data_selezionata);
                                                    echo "<a href='{$link}' class='{$class}' title='{$tooltip} (Clicca per prenotare)'>{$p}</a>";
                                                }
                                            } else {
                                                echo "<div class='spazio-vuoto'></div>";
                                            }
                                            ?>
                                        <?php endfor; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Usa il filtro di ricerca per visualizzare la mappa della spiaggia.</p>
        <?php endif; ?>
    </main>

    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>

<script src="script.js"></script>
</body>
</html>