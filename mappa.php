<?php
session_start();

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: accesso.php');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

$tipo_prenotazione = $_GET['tipo_prenotazione'] ?? 'giornaliero'; 
// ## LA CORREZIONE È QUI ##
// Imposta una data di default che esista nel tuo database
$data_selezionata = $_GET['data_ricerca'] ?? '2026-06-01';

$ombrelloni_mappa = [];
$messaggio_errore = '';
$settori_presenti = [];

$sql_check_data = "SELECT 1 FROM giornodisponibilita WHERE data = :data_selezionata LIMIT 1";
$stmt_check_data = $pdo->prepare($sql_check_data);
$stmt_check_data->execute(['data_selezionata' => $data_selezionata]);

if ($stmt_check_data->rowCount() > 0) {
    if ($tipo_prenotazione === 'settimanale') {
        $data_fine = date('Y-m-d', strtotime($data_selezionata . ' + 6 days'));
        
        $sql_occupati = "SELECT DISTINCT idOmbrellone FROM giornodisponibilita WHERE data BETWEEN :data_inizio AND :data_fine AND numProgrContratto IS NOT NULL";
        $stmt_occupati = $pdo->prepare($sql_occupati);
        $stmt_occupati->execute(['data_inizio' => $data_selezionata, 'data_fine' => $data_fine]);
        $id_ombrelloni_occupati = $stmt_occupati->fetchAll(PDO::FETCH_COLUMN);

        $sql_ombrelloni = "SELECT id, settore, numFila, numPostoFila, codTipologia FROM ombrellone";
        $stmt_ombrelloni = $pdo->query($sql_ombrelloni);
        $ombrelloni_mappa_temp = $stmt_ombrelloni->fetchAll();
        
        foreach ($ombrelloni_mappa_temp as $ombrellone) {
            $ombrellone['occupato'] = in_array($ombrellone['id'], $id_ombrelloni_occupati);
            $ombrelloni_mappa[] = $ombrellone;
        }

    } else {
        $sql_ombrelloni = "
            SELECT o.id, o.settore, o.numFila, o.numPostoFila, o.codTipologia,
                   CASE WHEN gd.numProgrContratto IS NOT NULL THEN 1 ELSE 0 END AS occupato
            FROM ombrellone o
            LEFT JOIN giornodisponibilita gd ON o.id = gd.idOmbrellone AND gd.data = :data_selezionata
            ORDER BY o.settore, o.numFila, o.numPostoFila
        ";
        $stmt_ombrelloni = $pdo->prepare($sql_ombrelloni);
        $stmt_ombrelloni->execute(['data_selezionata' => $data_selezionata]);
        $ombrelloni_mappa = $stmt_ombrelloni->fetchAll();
    }
    
    foreach($ombrelloni_mappa as $ombrellone) {
        if (!in_array($ombrellone['settore'], $settori_presenti)) {
            $settori_presenti[] = $ombrellone['settore'];
        }
    }

} else {
    $messaggio_errore = "La data selezionata non è disponibile. Scegli un'altra data.";
}

function calcola_posizione_ombrellone($ombrellone) {
    $settore = $ombrellone['settore'];
    $colonna = $ombrellone['numFila'];
    $posto_in_colonna = $ombrellone['numPostoFila'];
    $config_settori = [
        /* Ho aggiustato la spaziatura per adattarla alle nuove dimensioni delle icone */
        'A' => ['base_top' => 15, 'base_left' => 10, 'v_spacing' => 6, 'h_spacing' => 4],
        'B' => ['base_top' => 15, 'base_left' => 30, 'v_spacing' => 6, 'h_spacing' => 4],
        'C' => ['base_top' => 15, 'base_left' => 50, 'v_spacing' => 6, 'h_spacing' => 4],
        'D' => ['base_top' => 15, 'base_left' => 70, 'v_spacing' => 6, 'h_spacing' => 4],
    ];
    if (!isset($config_settori[$settore])) { return ['top' => '0%', 'left' => '0%']; }
    $config = $config_settori[$settore];
    $top = $config['base_top'] + (($posto_in_colonna - 1) * $config['v_spacing']);
    $left = $config['base_left'] + (($colonna - 1) * $config['h_spacing']);
    return ['top' => $top . '%', 'left' => $left . '%'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Mappa Spiaggia - Lido Paradiso</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
</head>
<body>
<div class="container">
    <header>Lido Paradiso</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php" class="active">Mappa Spiaggia</a>
        <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['nome_cliente']) ?>)</a>
    </nav>

    <div class="search-filter">
        <form method="GET" action="mappa.php">
            <label for="data_ricerca">Seleziona la data di inizio:</label>
            <input type="date" id="data_ricerca" name="data_ricerca" value="<?= htmlspecialchars($data_selezionata) ?>" required />

            <div style="display: flex; gap: 15px; align-items: center;">
                <label><input type="radio" name="tipo_prenotazione" value="giornaliero" <?= $tipo_prenotazione === 'giornaliero' ? 'checked' : '' ?>> Giornaliero</label>
                <label><input type="radio" name="tipo_prenotazione" value="settimanale" <?= $tipo_prenotazione === 'settimanale' ? 'checked' : '' ?>> Abbonamento 7 Giorni</label>
            </div>

            <button type="submit">Mostra Disponibilità</button>
        </form>
    </div>

    <main>
        <?php if (!empty($messaggio_errore)): ?>
             <div class="messaggio errore"><p><?= htmlspecialchars($messaggio_errore) ?></p></div>
        <?php elseif (!empty($ombrelloni_mappa)): ?>
            <?php if ($tipo_prenotazione === 'settimanale'): 
                $data_fine_str = date("d/m/Y", strtotime($data_selezionata . ' + 6 days'));
            ?>
                <h3>Disponibilità per la settimana dal <strong><?= date("d/m/Y", strtotime($data_selezionata)) ?></strong> al <strong><?= $data_fine_str ?></strong></h3>
            <?php else: ?>
                <h3>Disponibilità per il <strong><?= date("d/m/Y", strtotime($data_selezionata)) ?></strong></h3>
            <?php endif; ?>
            
            <div class="legenda">
                <span class="box disponibile"></span> Libero
                <span class="box vip"></span> VIP Libero
                <span class="box occupato"></span> Occupato
            </div>

            <div class="spiaggia-mappa-container">
                <div class="mare">
                </div>
                <div class="sabbia">
                    <div class="starfish" style="top: 20%; left: 5%;"></div>
                    <div class="starfish" style="top: 50%; left: 20%;"></div>
                    <div class="starfish" style="top: 80%; left: 10%;"></div>
                    <div class="starfish" style="top: 30%; left: 40%;"></div>
                    <div class="starfish" style="top: 60%; left: 60%;"></div>
                    <div class="starfish" style="top: 10%; left: 80%;"></div>
                    <div class="wooden-path" style="left: 25%;"></div>
                    <div class="wooden-path" style="left: 45%;"></div>
                    <div class="wooden-path" style="left: 65%;"></div>
                    <div class="wooden-path" style="left: 85%;"></div>
                    <div class="avenue-container">
                        <div class="road"></div>
                        <div class="sidewalk">
                        </div>
                    </div>
                    <div class="bar">BAR</div>
                    <div class="settori-container">
                        <?php 
                            $config_settori = [
                                'A' => ['base_left' => 10, 'h_spacing' => 4],
                                'B' => ['base_left' => 30, 'h_spacing' => 4],
                                'C' => ['base_left' => 50, 'h_spacing' => 4],
                                'D' => ['base_left' => 70, 'h_spacing' => 4],
                            ];
                            foreach ($config_settori as $settore => $config):
                                $center = $config['base_left'] + $config['h_spacing'];
                        ?>
                            <div class="settore-label" style="left: <?= $center ?>%;">SETTORE <?= htmlspecialchars($settore) ?></div>
                        <?php endforeach; ?>
                    </div>

                    <div class="ombrelloni-container">
                        <?php 
                        $settore_corrente = '';
                        $numero_per_settore = 0;

                        foreach ($ombrelloni_mappa as $ombrellone): 
                            if ($ombrellone['settore'] !== $settore_corrente) {
                                $settore_corrente = $ombrellone['settore'];
                                $numero_per_settore = 1;
                            } else {
                                $numero_per_settore++;
                            }
                        
                            $posizione = calcola_posizione_ombrellone($ombrellone);
                            $is_occupato = $ombrellone['occupato'];
                            $is_vip = $ombrellone['codTipologia'] === 'VIP';
                            $class = 'ombrellone-wrapper' . ($is_occupato ? ' occupato' : ' disponibile') . ($is_vip ? ' vip' : '');
                            $tooltip = "Sett. {$ombrellone['settore']}, N. {$numero_per_settore} | Fila {$ombrellone['numFila']}, Posto {$ombrellone['numPostoFila']}";
                            $style = "top: {$posizione['top']}; left: {$posizione['left']};";

                            if ($is_occupato) {
                                echo "<div class='{$class}' style='{$style}' title='{$tooltip} (Non disponibile)'>";
                                echo "<div class='ombrellone-icon'></div><span class='ombrellone-numero'>{$numero_per_settore}</span></div>";
                            } else {
                                $link = "prenota.php?id=" . urlencode($ombrellone['id']) . "&data=" . urlencode($data_selezionata) . "&tipo=" . urlencode($tipo_prenotazione);
                                echo "<a href='{$link}' class='{$class}' style='{$style}' title='{$tooltip} (Clicca per prenotare)'>";
                                echo "<div class='ombrellone-icon'></div><span class='ombrellone-numero'>{$numero_per_settore}</span></a>";
                            }
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>Nessun ombrellone trovato per i criteri selezionati.</p>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>