<?php
// Abilita la visualizzazione degli errori per il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Includi il file di connessione al database
require_once 'db_connection.php';

// Inizializza le variabili
$data_selezionata = date('Y-m-d'); // Imposta la data odierna come predefinita
$ombrelloni_mappa = [];
$messaggio_errore = '';
$settori_presenti = [];

// Controlla se il form di ricerca è stato inviato
if (isset($_GET['data_ricerca']) && !empty($_GET['data_ricerca'])) {
    $data_selezionata = $_GET['data_ricerca'];
}

// Controlla se la data selezionata esiste nel database
$sql_check_data = "SELECT 1 FROM giornodisponibilita WHERE data = :data_selezionata LIMIT 1";
$stmt_check_data = $pdo->prepare($sql_check_data);
$stmt_check_data->execute(['data_selezionata' => $data_selezionata]);

if ($stmt_check_data->rowCount() > 0) {
    // La data è valida, procediamo a recuperare tutti gli ombrelloni e il loro stato
    $sql_ombrelloni = "
        SELECT 
            o.id, o.settore, o.numFila, o.numPostoFila, o.codTipologia,
            CASE WHEN gd.numProgrContratto IS NOT NULL THEN 1 ELSE 0 END AS occupato
        FROM ombrellone o
        LEFT JOIN giornodisponibilita gd ON o.id = gd.idOmbrellone AND gd.data = :data_selezionata
        ORDER BY o.settore, o.numFila, o.numPostoFila
    ";
    
    $stmt_ombrelloni = $pdo->prepare($sql_ombrelloni);
    $stmt_ombrelloni->execute(['data_selezionata' => $data_selezionata]);
    $ombrelloni_mappa = $stmt_ombrelloni->fetchAll();
    
    // Raccoglie i settori unici per le etichette
    foreach($ombrelloni_mappa as $ombrellone) {
        if (!in_array($ombrellone['settore'], $settori_presenti)) {
            $settori_presenti[] = $ombrellone['settore'];
        }
    }

} else {
    $messaggio_errore = "La data selezionata non è valida o è al di fuori della stagione balneare. Prego, scegli un'altra data.";
}

/**
 * Funzione per calcolare le coordinate di un ombrellone in layout a colonne.
 * @param array $ombrellone Dati dell'ombrellone.
 * @return array Coordinate 'top' e 'left' in percentuale.
 */
function calcola_posizione_ombrellone($ombrellone) {
    $settore = $ombrellone['settore'];
    $colonna = $ombrellone['numFila']; // numFila ora rappresenta la colonna
    $posto_in_colonna = $ombrellone['numPostoFila']; // numPostoFila è la posizione verticale

    $config_settori = [
        // Ogni settore: top iniziale, left iniziale, spaziatura verticale, spaziatura orizzontale
        'A' => ['base_top' => 15, 'base_left' => 10, 'v_spacing' => 7, 'h_spacing' => 5],
        'B' => ['base_top' => 15, 'base_left' => 30, 'v_spacing' => 7, 'h_spacing' => 5],
        'C' => ['base_top' => 15, 'base_left' => 50, 'v_spacing' => 7, 'h_spacing' => 5],
        'D' => ['base_top' => 15, 'base_left' => 70, 'v_spacing' => 7, 'h_spacing' => 5],
        'E' => ['base_top' => 15, 'base_left' => 90, 'v_spacing' => 7, 'h_spacing' => 5],
    ];
    
    if (!isset($config_settori[$settore])) {
        return ['top' => '0%', 'left' => '0%'];
    }

    $config = $config_settori[$settore];
    
    // Calcolo posizione
    $top = $config['base_top'] + (($posto_in_colonna - 1) * $config['v_spacing']);
    $left = $config['base_left'] + (($colonna - 1) * $config['h_spacing']);

    return [
        'top' => $top . '%', 
        'left' => $left . '%'
    ];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazione Ombrelloni - Mappa Spiaggia</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
<div class="container">
    <header>Lido Paradiso</header>
    <nav>
        <a href="index.php" class="active">Home</a>
        <a href="#">Servizi</a>
        <a href="#">Listino Prezzi</a>
        <a href="#">Contatti</a>
    </nav>

    <div class="search-filter">
        <form method="GET" action="index.php">
            <label for="data_ricerca">Seleziona la data della tua prenotazione:</label>
            <input type="date" id="data_ricerca" name="data_ricerca" value="<?= htmlspecialchars($data_selezionata) ?>" required />
            <button type="submit">Mostra Disponibilità</button>
        </form>
    </div>

    <main>
        <?php if (!empty($messaggio_errore)): ?>
            <div class="messaggio errore">
                <p><?= htmlspecialchars($messaggio_errore) ?></p>
            </div>
        <?php elseif (!empty($ombrelloni_mappa)): ?>
            <h3>Disponibilità per il <strong><?= date("d/m/Y", strtotime($data_selezionata)) ?></strong></h3>
            <div class="legenda">
                <span class="box disponibile"></span> Libero
                <span class="box vip"></span> VIP Libero
                <span class="box occupato"></span> Occupato
            </div>

            <div class="spiaggia-mappa-container">
                <div class="ombrelloni-container">
                    
                    <?php 
                        $posizioni_settori = ['A' => 15, 'B' => 35, 'C' => 55, 'D' => 75, 'E' => 95];
                        foreach ($settori_presenti as $settore): 
                    ?>
                        <div class="settore-label" style="left: <?= $posizioni_settori[$settore] ?? 0 ?>%;">SETTORE <?= htmlspecialchars($settore) ?></div>
                    <?php endforeach; ?>

                    <?php foreach ($ombrelloni_mappa as $ombrellone): ?>
                        <?php
                            $posizione = calcola_posizione_ombrellone($ombrellone);
                            $is_occupato = $ombrellone['occupato'];
                            $is_vip = $ombrellone['codTipologia'] === 'VIP';
                            
                            $class = 'ombrellone-wrapper' . ($is_occupato ? ' occupato' : ' disponibile') . ($is_vip ? ' vip' : '');
                            $tooltip = "Ombrellone #{$ombrellone['id']} | Sett. {$ombrellone['settore']} | Col. {$ombrellone['numFila']} | Posto {$ombrellone['numPostoFila']}";
                            
                            $style = "top: {$posizione['top']}; left: {$posizione['left']};";

                            if ($is_occupato) {
                                echo "<div class='{$class}' style='{$style}' title='{$tooltip} (Non disponibile)'>";
                                echo "<div class='ombrellone-icon'></div>";
                                echo "<span class='ombrellone-numero'>{$ombrellone['id']}</span>";
                                echo "</div>";
                            } else {
                                $link = "prenota.php?id=" . urlencode($ombrellone['id']) . "&data=" . urlencode($data_selezionata);
                                echo "<a href='{$link}' class='{$class}' style='{$style}' title='{$tooltip} (Clicca per prenotare)'>";
                                echo "<div class='ombrellone-icon'></div>";
                                echo "<span class='ombrellone-numero'>{$ombrellone['id']}</span>";
                                echo "</a>";
                            }
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p>Seleziona una data per visualizzare la mappa della spiaggia e la disponibilità degli ombrelloni.</p>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
