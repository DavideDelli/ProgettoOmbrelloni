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
    $config_settori = [
        'A' => ['base_top' => 15, 'base_left' => 10, 'v_spacing' => 6, 'h_spacing' => 4],
        'B' => ['base_top' => 15, 'base_left' => 30, 'v_spacing' => 6, 'h_spacing' => 4],
        'C' => ['base_top' => 15, 'base_left' => 50, 'v_spacing' => 6, 'h_spacing' => 4],
        'D' => ['base_top' => 15, 'base_left' => 70, 'v_spacing' => 6, 'h_spacing' => 4],
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
    <title>Mappa Spiaggia - Lido Codici Sballati</title>
    <link rel="stylesheet" href="assets/css/stile.css?v=<?= filemtime('assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Lido Codici Sballati</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php" class="active">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php">Le mie Prenotazioni</a>
        <a href="profilo.php">Il mio profilo (<?= htmlspecialchars($_SESSION['nome_cliente']) ?>)</a>
    </nav>

    <div class="search-filter glass-panel">
        <form method="GET" action="mappa.php">
            <label for="data_ricerca">Seleziona la data di inizio:</label>
            <input type="date" id="data_ricerca" name="data_ricerca" value="<?= htmlspecialchars($data_selezionata) ?>" required />
            <div style="display: flex; gap: 15px; align-items: center;">
                <label><input type="radio" name="tipo_prenotazione" value="giornaliero" onchange="this.form.submit()" <?= $tipo_prenotazione === 'giornaliero' ? 'checked' : '' ?>> Giornaliero</label>
                <label><input type="radio" name="tipo_prenotazione" value="settimanale" onchange="this.form.submit()" <?= $tipo_prenotazione === 'settimanale' ? 'checked' : '' ?>> Abbonamento 7 Giorni</label>
            </div>
        </form>
    </div>

    <main>
        <?php if (!empty($messaggio_errore)): ?>
             <div class="messaggio errore glass-panel"><p><?= htmlspecialchars($messaggio_errore) ?></p></div>
        <?php elseif (!empty($ombrelloni_mappa)): ?>
            <div class="legenda-container glass-panel">
                <?php if ($tipo_prenotazione === 'settimanale'): ?>
                    <h3>Disponibilità per la settimana dal <strong><?= date("d/m/Y", strtotime($data_selezionata)) ?></strong> al <strong><?= date("d/m/Y", strtotime($data_selezionata . ' + 6 days')) ?></strong></h3>
                <?php else: ?>
                    <h3>Disponibilità per il <strong><?= date("d/m/Y", strtotime($data_selezionata)) ?></strong></h3>
                <?php endif; ?>
                <div class="legenda">
                    <span class="box disponibile"></span> Libero
                    <span class="box vip"></span> VIP Libero
                    <span class="box occupato"></span> Occupato
                </div>
            </div>

            <div class="spiaggia-mappa-container">
                <div class="mare"></div>
                <div class="sabbia">
                    <div class="wooden-path" style="left: 25%;"></div>
                    <div class="wooden-path" style="left: 45%;"></div>
                    <div class="wooden-path" style="left: 65%;"></div>
                    <div class="wooden-path" style="left: 85%;"></div>
                    <div class="avenue-container">
                        <div class="road"></div>
                        <div class="sidewalk"></div>
                    </div>
                    <div class="ombrelloni-container">
                        <?php 
                        $config_settori = ['A' => 10, 'B' => 30, 'C' => 50, 'D' => 70];
                        foreach ($config_settori as $settore => $left_pos):
                        ?>
                            <div class="settore-label" style="left: <?= $left_pos + 4 ?>%; top: 5%;">SETTORE <?= $settore ?></div>
                        <?php 
                        endforeach;

                        foreach ($ombrelloni_mappa as $ombrellone): 
                            $posizione = calcola_posizione_ombrellone($ombrellone);
                            $is_occupato = $ombrellone['occupato'];
                            $is_vip = $ombrellone['codTipologia'] === 'VIP';
                            $class = 'ombrellone-wrapper' . ($is_occupato ? ' occupato' : ' disponibile') . ($is_vip ? ' vip' : '');
                            $tooltip = "Sett. {$ombrellone['settore']}, Fila {$ombrellone['numFila']}, Posto {$ombrellone['numPostoFila']}";
                            $style = "top: {$posizione['top']}; left: {$posizione['left']};";

                            if ($is_occupato) {
                                echo "<div class='$class' style='$style' title='$tooltip (Non disponibile)'><div class='ombrellone-icon'></div><span class='ombrellone-numero'>{$ombrellone['numPostoFila']}</span></div>";
                            } else {
                                $link = "src/booking/prenota.php?id=" . urlencode($ombrellone['id']) . "&data=" . urlencode($data_selezionata) . "&tipo=" . urlencode($tipo_prenotazione);
                                echo "<a href='$link' class='$class' style='$style' title='$tooltip (Clicca per prenotare)'><div class='ombrellone-icon'></div><span class='ombrellone-numero'>{$ombrellone['numPostoFila']}</span></a>";
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