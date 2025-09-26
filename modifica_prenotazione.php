<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: accesso.php');
    exit();
}

require_once 'db_connection.php';

// Funzione helper da prenota.php
function getNomeTariffa($codice) {
    $nomi = [
        'STD_D' => 'Giornaliero Standard', 'STD_W' => 'Settimanale Standard',
        'VIP_D' => 'Giornaliero VIP', 'VIP_W' => 'Settimanale VIP',
        'STD_W_PREM' => 'Settimanale Premium', 'STD_W_APE'  => 'Settimanale Ape',
        'VIP_W_PREM' => 'Settimanale VIP Premium', 'VIP_W_APE'  => 'Settimanale VIP Ape',
        'STD_D_PREM' => 'Giornaliero Premium (con asciugamani)', 'STD_D_APE'  => 'Giornaliero Ape (con aperitivo)',
        'VIP_D_PREM' => 'Giornaliero VIP Premium (con asciugamani)', 'VIP_D_APE'  => 'Giornaliero VIP Ape (con aperitivo)',
    ];
    return $nomi[$codice] ?? $codice;
}

$errore = '';
if (isset($_SESSION['errore_modifica'])) {
    $errore = $_SESSION['errore_modifica'];
    unset($_SESSION['errore_modifica']);
}

$prenotazione = null;
$tariffe_disponibili = [];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $errore = "ID prenotazione non valido.";
} else {
    $id_contratto = $_GET['id'];
    $codice_cliente = $_SESSION['codice_cliente'];

    try {
        $sql = "
            SELECT 
                c.numProgr, c.importo, c.data AS data_inizio, c.dataFine AS data_fine, c.codTariffa,
                o.id AS id_ombrellone, o.settore, o.numFila, o.numPostoFila, o.codTipologia,
                tip.nome AS nome_tipologia
            FROM contratto c
            JOIN giornodisponibilita gd ON c.numProgr = gd.numProgrContratto
            JOIN ombrellone o ON gd.idOmbrellone = o.id
            JOIN tipologia tip ON o.codTipologia = tip.codice
            WHERE c.numProgr = :id_contratto AND c.codiceCliente = :codice_cliente
            GROUP BY c.numProgr
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_contratto' => $id_contratto, 'codice_cliente' => $codice_cliente]);
        $prenotazione = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prenotazione) {
            $errore = "Prenotazione non trovata o non autorizzato a modificarla.";
        } elseif (strtotime($prenotazione['data_inizio']) <= strtotime(date("Y-m-d"))) {
             $errore = "Non è possibile modificare una prenotazione passata o odierna.";
             $prenotazione = null;
        } elseif (empty($prenotazione['codTariffa'])) {
            $errore = "Impossibile modificare questa prenotazione perché creata prima dell'aggiornamento del sistema. Si prega di cancellarla e crearne una nuova.";
            $prenotazione = null;
        } else {
            $tipo_prenotazione = ($prenotazione['data_fine'] !== null) ? 'settimanale' : 'giornaliero';
            $tipo_tariffa_db = ($tipo_prenotazione === 'settimanale') ? 'SETTIMANALE' : 'GIORNALIERO';

            $sql_tariffe = "
                SELECT tar.codice, tar.prezzo FROM tariffa tar
                JOIN tipologiatariffa tt ON tar.codice = tt.codTariffa
                WHERE tt.codTipologia = :cod_tipologia AND tar.tipo = :tipo_tariffa
                ORDER BY tar.prezzo ASC
            ";
            $stmt_tariffe = $pdo->prepare($sql_tariffe);
            $stmt_tariffe->execute(['cod_tipologia' => $prenotazione['codTipologia'], 'tipo_tariffa' => $tipo_tariffa_db]);
            $tariffe_disponibili = $stmt_tariffe->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $errore = "Errore di connessione al database: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Prenotazione</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
    <style>
        fieldset { margin-bottom: 20px; padding: 15px; border-radius: 6px; border: 1px solid #ac6730; }
        legend { font-weight: bold; color: #3b2a1a; padding: 0 5px; }
    </style>
</head>
<body>
<div class="container">
    <header>Modifica Prenotazione</header>
    <nav>
        <a href="index.php">Home</a> <a href="mappa.php">Mappa Spiaggia</a> <a href="le_mie_prenotazioni.php" class="active">Le mie Prenotazioni</a> <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['nome_cliente']) ?>)</a>
    </nav>
    <main>
        <?php if ($errore): ?>
            <div class="messaggio errore">
                <h2>Errore</h2>
                <p><?= htmlspecialchars($errore) ?></p>
                <a href="le_mie_prenotazioni.php" class="button">Torna alle mie prenotazioni</a>
            </div>
        <?php endif; ?>

        <?php if ($prenotazione && !$errore): ?>
            <div class="riepilogo-box">
                <p><strong>Stai modificando la prenotazione N°:</strong> <?= htmlspecialchars($prenotazione['numProgr']) ?></p>
                <p><strong>Ombrellone:</strong> Settore <?= htmlspecialchars($prenotazione['settore']) ?>, Fila <?= htmlspecialchars($prenotazione['numFila']) ?>, Posto <?= htmlspecialchars($prenotazione['numPostoFila']) ?></p>
                <p id="prezzo_totale"><strong>Prezzo:</strong> €0,00</p>
            </div>

            <form action="processa_modifica.php" method="POST" class="form-prenotazione">
                <input type="hidden" name="id_contratto" value="<?= htmlspecialchars($prenotazione['numProgr']) ?>">
                <input type="hidden" name="tipo_prenotazione" value="<?= $tipo_prenotazione ?>">
                <fieldset>
                    <legend>Modifica Periodo e Pacchetto</legend>
                    <div class="form-group"><label for="data_nuova"><?= ($tipo_prenotazione === 'settimanale') ? 'Nuova data di inizio (la fine verrà ricalcolata):' : 'Nuova data:' ?></label><input type="date" id="data_nuova" name="data_nuova" value="<?= htmlspecialchars($prenotazione['data_inizio']) ?>" required></div>
                    <div class="form-group"><label>Nuovo pacchetto:</label><?php foreach ($tariffe_disponibili as $tariffa): ?><label style="display: block; margin-bottom: 10px;"><input type="radio" name="cod_tariffa_nuovo" value="<?= htmlspecialchars($tariffa['codice']) ?>" data-prezzo="<?= $tariffa['prezzo'] ?>" <?= ($tariffa['codice'] === $prenotazione['codTariffa']) ? 'checked' : '' ?>><?= htmlspecialchars(getNomeTariffa($tariffa['codice'])) ?> (€<?= number_format($tariffa['prezzo'], 2, ',', '.') ?>)</label><?php endforeach; ?></div>
                </fieldset>
                <div class="form-group" style="text-align: right;">
                    <a href="le_mie_prenotazioni.php" class="button-link" style="background-color: #6c757d;">Annulla</a>
                    <button type="submit">Salva Modifiche</button>
                </div>
            </form>
            <script>
                const prezzoTotaleEl = document.getElementById('prezzo_totale');
                const radioTariffe = document.querySelectorAll('input[name="cod_tariffa_nuovo"]');
                function aggiornaPrezzo() { const scelta = document.querySelector('input[name="cod_tariffa_nuovo"]:checked'); if (scelta) { const nuovoPrezzo = parseFloat(scelta.dataset.prezzo); prezzoTotaleEl.innerHTML = `<strong>Nuovo Prezzo:</strong> €${nuovoPrezzo.toFixed(2).replace('.', ',')}`; } }
                radioTariffe.forEach(radio => radio.addEventListener('change', aggiornaPrezzo));
                aggiornaPrezzo();
            </script>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>

