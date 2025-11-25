<?php
session_start();

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: src/auth/accesso.php');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'src/db_connection.php';

$tipo_prenotazione = $_GET['tipo_prenotazione'] ?? 'giornaliero';
$data_selezionata = $_GET['data_ricerca'] ?? '2026-06-01';

$ombrelloni_mappa = [];
$messaggio_errore = '';

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
        $sql_ombrelloni = "SELECT o.id, o.settore, o.numFila, o.numPostoFila, o.codTipologia, CASE WHEN gd.numProgrContratto IS NOT NULL THEN 1 ELSE 0 END AS occupato FROM ombrellone o LEFT JOIN giornodisponibilita gd ON o.id = gd.idOmbrellone AND gd.data = :data_selezionata ORDER BY o.settore, o.numFila, o.numPostoFila";
        $stmt_ombrelloni = $pdo->prepare($sql_ombrelloni);
        $stmt_ombrelloni->execute(['data_selezionata' => $data_selezionata]);
        $ombrelloni_mappa = $stmt_ombrelloni->fetchAll();
    }
} else {
    $messaggio_errore = "La data selezionata non è disponibile. Scegli un'altra data.";
}

function calcola_posizione_ombrellone(array $ombrellone): array {
    // SPAZIATURA OTTIMIZZATA PER PIU' SABBIA
    // Base Top: 14% (Inizia in alto)
    // V Spacing: 8.2% (Molto arioso verticalmente)
    $config_settori = [
        'A' => ['base_top' => 14, 'base_left' => 9,  'v_spacing' => 8.2, 'h_spacing' => 9.5],
        'B' => ['base_top' => 14, 'base_left' => 34, 'v_spacing' => 8.2, 'h_spacing' => 9.5],
        'C' => ['base_top' => 14, 'base_left' => 59, 'v_spacing' => 8.2, 'h_spacing' => 9.5],
        'D' => ['base_top' => 14, 'base_left' => 84, 'v_spacing' => 8.2, 'h_spacing' => 9.5],
    ];
    
    $config = $config_settori[$ombrellone['settore']] ?? ['base_top' => 0, 'base_left' => 0, 'v_spacing' => 0, 'h_spacing' => 0];
    
    $top = $config['base_top'] + (($ombrellone['numPostoFila'] - 1) * $config['v_spacing']);
    $left = $config['base_left'] + (($ombrellone['numFila'] - 1) * $config['h_spacing']);
    
    return ['top' => $top . '%', 'left' => $left . '%'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mappa Spiaggia - Lido Codici Sballati</title>
    <link rel="stylesheet" href="assets/css/stile.css?v=<?= time() ?>">
    <style>
        /* FIX DEFINITIVO CENTRATURA BUTTONS */
        .radio-option span {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 42px !important; /* Altezza fissa per il bottone */
            padding: 0 25px !important; /* Padding solo laterale */
            box-sizing: border-box !important;
        }
        
        /* Spinge fisicamente il contenuto (testo+icona) verso il basso */
        /* Questo compensa l'effetto ottico del font maiuscolo */
        .radio-option span svg, 
        .radio-option span {
             padding-top: 3px !important; 
        }

        .radio-option span svg {
            margin-right: 8px;
            margin-top: -1px; /* Micro-adjustment per allineare l'icona al testo */
            padding-top: 0 !important; /* Reset padding su icona per non raddoppiarlo */
        }
    </style>
</head>
<body>
<div class="container">
    <header><h1>Lido Codici Sballati</h1></header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php" class="active">Mappa</a>
        <a href="le_mie_prenotazioni.php">Prenotazioni</a>
        <a href="profilo.php">Profilo</a>
    </nav>

    <div class="control-panel">
        <div class="search-group">
            <form method="GET" action="mappa.php">
                <div>
                    <label for="data_ricerca">DATA:</label>
                    <input type="date" id="data_ricerca" name="data_ricerca" value="<?= htmlspecialchars($data_selezionata) ?>" required onchange="this.form.submit()" />
                </div>
                
                <div>
                     <label style="visibility: hidden;">TIPO:</label> <!-- Spacer Label -->
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="tipo_prenotazione" value="giornaliero" onchange="this.form.submit()" <?= $tipo_prenotazione === 'giornaliero' ? 'checked' : '' ?>> 
                            <span>
                                <!-- Icona Sole -->
                                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                                Giornaliero
                            </span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="tipo_prenotazione" value="settimanale" onchange="this.form.submit()" <?= $tipo_prenotazione === 'settimanale' ? 'checked' : '' ?>> 
                            <span>
                                <!-- Icona Calendario -->
                                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                Settimanale
                            </span>
                        </label>
                    </div>
                </div>
            </form>
        </div>

        <div class="legend-group">
            <div class="legend-item"><div class="legend-box disponibile"></div> Libero</div>
            <div class="legend-item"><div class="legend-box vip"></div> VIP</div>
            <div class="legend-item"><div class="legend-box occupato"></div> Occupato</div>
        </div>
    </div>

    <main>
        <?php if (!empty($messaggio_errore)): ?>
             <div class="messaggio errore"><p><?= htmlspecialchars($messaggio_errore) ?></p></div>
        <?php elseif (!empty($ombrelloni_mappa)): ?>
            <div class="spiaggia-mappa-container">
                <div class="mare">
                    <div class="waves-container">
                        <div class="wave wave-1"></div>
                        <div class="wave wave-2"></div>
                        <div class="wave wave-3"></div>
                    </div>
                </div>
                
                <div class="sabbia">
                    <!-- PASSERELLE: bottom: 40px per toccare la strada -->
                    <div class="wooden-path" style="left: 25%; bottom: 40px;"></div>
                    <div class="wooden-path" style="left: 50%; bottom: 40px;"></div>
                    <div class="wooden-path" style="left: 75%; bottom: 40px;"></div>
                    
                    <div class="avenue-container" style="height: 40px;">
                        <div class="sidewalk" style="bottom: 0; height: 100%;"></div>
                    </div>
                    
                    <div class="ombrelloni-container">
                        <?php 
                        $labels = ['A' => 13, 'B' => 38, 'C' => 63, 'D' => 88];
                        foreach ($labels as $settore => $left_pos):
                        ?>
                            <div style="position: absolute; top: 0; left: <?= $left_pos ?>%; transform: translateX(-50%); text-align: center; pointer-events: none; z-index: 5;">
                                <div style="font-size: 1rem; font-weight: 700; color: rgba(62, 39, 35, 0.4); letter-spacing: 2px; text-transform: uppercase; margin-top: 15px;">SETTORE</div>
                                <div style="font-size: 6rem; font-family: 'Times New Roman', serif; font-weight: 900; color: rgba(62, 39, 35, 0.15); line-height: 0.8;"><?= $settore ?></div>
                            </div>
                        <?php 
                        endforeach;

                        foreach ($ombrelloni_mappa as $ombrellone): 
                            $posizione = calcola_posizione_ombrellone($ombrellone);
                            $is_occupato = $ombrellone['occupato'];
                            $is_vip = $ombrellone['codTipologia'] === 'VIP';
                            
                            $lato_numero = ($ombrellone['numFila'] % 2 != 0) ? 'pos-numero-dx' : 'pos-numero-sx';

                            $class = "ombrellone-wrapper $lato_numero " . ($is_occupato ? 'occupato' : 'disponibile') . ($is_vip ? ' vip' : '');
                            
                            $tooltip = "Settore {$ombrellone['settore']} | Fila {$ombrellone['numFila']} | Posto {$ombrellone['numPostoFila']}";
                            if($is_vip) $tooltip .= " (VIP)";
                            $style = "top: {$posizione['top']}; left: {$posizione['left']};";

                            $innerContent = "<div class='ombrellone-icon'></div><span class='ombrellone-numero'>{$ombrellone['numPostoFila']}</span>";

                            if ($is_occupato) {
                                echo "<div class='$class' style='$style' title='$tooltip - Non disponibile'>$innerContent</div>";
                            } else {
                                $link = "src/booking/prenota.php?id=" . urlencode($ombrellone['id']) . "&data=" . urlencode($data_selezionata) . "&tipo=" . urlencode($tipo_prenotazione);
                                echo "<a href='$link' class='$class' style='$style' title='$tooltip - Clicca per prenotare'>$innerContent</a>";
                            }
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="messaggio glass-panel">
                <p>Nessun ombrellone trovato per i criteri selezionati.</p>
            </div>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Lido Codici Sballati</footer>
</div>
</body>
</html>