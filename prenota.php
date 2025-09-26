<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: accesso.php');
    exit();
}

require_once 'db_connection.php';

// Funzione helper per "tradurre" i codici delle tariffe
function getNomeTariffa($codice) {
    $nomi = [
        'STD_D' => 'Giornaliero Standard',
        'STD_W' => 'Settimanale Standard',
        'VIP_D' => 'Giornaliero VIP',
        'VIP_W' => 'Settimanale VIP',
        'STD_W_PREM' => 'Settimanale Premium (con asciugamani)',
        'STD_W_APE' => 'Settimanale Ape (con aperitivo tutti i giorni)',
        'VIP_W_PREM' => 'Settimanale VIP Premium (con asciugamani)',
        'VIP_W_APE' => 'Settimanale VIP Ape (con aperitivo tutti i giorni)',
        'STD_D_PREM' => 'Giornaliero Premium (con asciugamani)',
        'STD_D_APE' => 'Giornaliero Ape (con aperitivo)',
        'VIP_D_PREM' => 'Giornaliero VIP Premium (con asciugamani)',
        'VIP_D_APE' => 'Giornaliero VIP Ape (con aperitivo)',
    ];
    return isset($nomi[$codice]) ? $nomi[$codice] : $codice;
}

// Inizializzazione variabili
$ombrellone = null;
$errore = '';
$data_selezionata = '';
$tipo_prenotazione = '';
$tariffe_disponibili = [];
$nome_cliente = $_SESSION['nome_cliente'];
$cognome_cliente = $_SESSION['cognome_cliente'];

if (isset($_GET['id'], $_GET['data'], $_GET['tipo'])) {
    $id_ombrellone = $_GET['id'];
    $data_selezionata = $_GET['data'];
    $tipo_prenotazione = $_GET['tipo'];
    
    $sql_ombrellone = "SELECT o.id, o.settore, o.numFila, o.numPostoFila, t.codice AS cod_tipologia FROM ombrellone o JOIN tipologia t ON o.codTipologia = t.codice WHERE o.id = :id";
    $stmt_ombrellone = $pdo->prepare($sql_ombrellone);
    $stmt_ombrellone->execute(['id' => $id_ombrellone]);
    $ombrellone = $stmt_ombrellone->fetch();

    if (!$ombrellone) {
        $errore = "Ombrellone non trovato.";
    } else {
        $tipo_tariffa_db = ($tipo_prenotazione === 'settimanale') ? 'SETTIMANALE' : 'GIORNALIERO';
        
        $sql_tariffe = "
            SELECT tar.codice, tar.prezzo
            FROM tariffa tar
            JOIN tipologiatariffa tt ON tar.codice = tt.codTariffa
            WHERE tt.codTipologia = :cod_tipologia AND tar.tipo = :tipo_tariffa
            ORDER BY tar.prezzo ASC
        ";
        $stmt_tariffe = $pdo->prepare($sql_tariffe);
        $stmt_tariffe->execute(['cod_tipologia' => $ombrellone['cod_tipologia'], 'tipo_tariffa' => $tipo_tariffa_db]);
        $tariffe_disponibili = $stmt_tariffe->fetchAll();
        
        if (empty($tariffe_disponibili)) {
            $errore = "Nessuna tariffa disponibile per la selezione corrente.";
            $ombrellone = null;
        }
    }
} else {
    $errore = "Dati mancanti per la prenotazione.";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Conferma Prenotazione</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
    <style>
        input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
        fieldset { margin-bottom: 20px; padding: 15px; border-radius: 6px; border: 1px solid #ac6730; }
        legend { font-weight: bold; color: #3b2a1a; padding: 0 5px; }
    </style>
</head>
<body>
<div class="container">
    <header>Riepilogo Prenotazione</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php">Le mie Prenotazioni</a>
        <a href="logout.php">Logout (<?= htmlspecialchars($nome_cliente) ?>)</a>
    </nav>
    <main>
        <?php if ($errore): ?>
            <div class="messaggio errore" style="max-width: 700px; margin: 2rem auto;">
                <h2>Errore</h2>
                <p><?= htmlspecialchars($errore) ?></p>
                <a href="mappa.php" class="button">Torna alla Mappa</a>
            </div>
        <?php elseif ($ombrellone && !empty($tariffe_disponibili)): ?>
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-size: 2.2em; color: #3b2a1a;">Un ultimo passo...</h2>
                <p style="font-size: 1.2em; color: #7c3f06; margin-top: 0;">Controlla i dettagli e conferma.</p>
            </div>
            
            <div class="riepilogo-box">
                <?php if ($tipo_prenotazione === 'settimanale'): 
                    $data_inizio_str = date("d/m/Y", strtotime($data_selezionata));
                    $data_fine_str = date("d/m/Y", strtotime($data_selezionata . ' + 6 days'));
                ?>
                    <p><strong>Tipo:</strong> Abbonamento 7 giorni</p>
                    <p><strong>Periodo:</strong> Da <strong><?= $data_inizio_str ?></strong> a <strong><?= $data_fine_str ?></strong></p>
                <?php else: ?>
                    <p><strong>Tipo:</strong> Prenotazione Giornaliera</p>
                    <p><strong>Data:</strong> <?= htmlspecialchars(date("d/m/Y", strtotime($data_selezionata))) ?></p>
                <?php endif; ?>
                <p><strong>Ombrellone:</strong> Settore <?= htmlspecialchars($ombrellone['settore']) ?>, Fila <?= htmlspecialchars($ombrellone['numFila']) ?>, Posto <?= htmlspecialchars($ombrellone['numPostoFila']) ?></p>
                <p id="prezzo_totale"><strong>Prezzo Totale:</strong> €<?= number_format($tariffe_disponibili[0]['prezzo'], 2, ',', '.') ?></p>
            </div>

            <div style="text-align: center; margin-bottom: 25px;">
            <a href="mappa.php?data_ricerca=<?= htmlspecialchars($data_selezionata) ?>&amp;tipo_prenotazione=<?= htmlspecialchars($tipo_prenotazione) ?>" class="button" style="text-decoration: none; background-color: #6c757d;">← Cambia Selezione</a>
            </div>

            <form action="conferma.php" method="POST" class="form-prenotazione">
                <input type="hidden" name="id_ombrellone" value="<?= htmlspecialchars($ombrellone['id']) ?>">
                <input type="hidden" name="data_prenotazione" value="<?= htmlspecialchars($data_selezionata) ?>">
                <input type="hidden" name="tipo_prenotazione" value="<?= htmlspecialchars($tipo_prenotazione) ?>">

                <fieldset>
                    <legend>Dati Cliente</legend>
                    <div class="form-group"><label>Nome:</label><input type="text" name="nome" value="<?= htmlspecialchars($nome_cliente) ?>" readonly></div>
                    <div class="form-group"><label>Cognome:</label><input type="text" name="cognome" value="<?= htmlspecialchars($cognome_cliente) ?>" readonly></div>
                </fieldset>

                <fieldset>
                    <legend>Scegli il tuo Pacchetto</legend>
                     <div class="form-group">
                        <?php foreach ($tariffe_disponibili as $index => $tariffa): ?>
                            <label style="display: block; margin-bottom: 10px;">
                                <input type="radio" name="cod_tariffa" 
                                       value="<?= htmlspecialchars($tariffa['codice']) ?>" 
                                       data-prezzo="<?= $tariffa['prezzo'] ?>"
                                       <?= $index === 0 ? 'checked' : '' ?>>
                                <?= htmlspecialchars(getNomeTariffa($tariffa['codice'])) ?> 
                                (€<?= number_format($tariffa['prezzo'], 2, ',', '.') ?>)
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div class="form-group"><button type="submit">Conferma e Prenota</button></div>
            </form>
            
            <script>
                const prezzoTotaleEl = document.getElementById('prezzo_totale');
                const radioTariffe = document.querySelectorAll('input[name="cod_tariffa"]');

                if (radioTariffe.length > 0) {
                    function aggiornaPrezzo() {
                        const scelta = document.querySelector('input[name="cod_tariffa"]:checked');
                        const nuovoPrezzo = parseFloat(scelta.dataset.prezzo);
                        prezzoTotaleEl.innerHTML = `<strong>Prezzo Totale:</strong> €${nuovoPrezzo.toFixed(2).replace('.', ',')}`;
                    }
                    radioTariffe.forEach(radio => radio.addEventListener('change', aggiornaPrezzo));
                }
            </script>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>