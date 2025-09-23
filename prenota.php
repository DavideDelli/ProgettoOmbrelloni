<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: accesso.php');
    exit();
}

require_once 'db_connection.php';

// Inizializzazione
$ombrellone = null;
$errore = '';
$data_selezionata = '';
$tipo_prenotazione = 'giornaliero';
$prezzo_base = 0;

// Recupero dati utente
$nome_cliente = $_SESSION['nome_cliente'];
$cognome_cliente = $_SESSION['cognome_cliente'];

if (isset($_GET['id'], $_GET['data'], $_GET['tipo'])) {
    $id_ombrellone = $_GET['id'];
    $data_selezionata = $_GET['data'];
    $tipo_prenotazione = $_GET['tipo'];

    $sql = "
        SELECT o.id, o.settore, o.numFila, o.numPostoFila, t.nome AS nome_tipologia, t.codice AS cod_tipologia
        FROM ombrellone o JOIN tipologia t ON o.codTipologia = t.codice
        WHERE o.id = :id_ombrellone
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_ombrellone' => $id_ombrellone]);
    $ombrellone = $stmt->fetch();

    if (!$ombrellone) {
        $errore = "Ombrellone non trovato.";
    } else {
        // --- CALCOLO PREZZO BASE ---
        // 1. Definisco il prezzo giornaliero in base alla tipologia (Standard o VIP)
        $prezzo_giornaliero = ($ombrellone['cod_tipologia'] === 'VIP') ? 50 : 30;
        
        if ($tipo_prenotazione === 'settimanale') {
            // 2. Per il settimanale, moltiplico il prezzo giornaliero per 7.
            //    Questo è il prezzo base dell'abbonamento, come hai richiesto.
            $prezzo_base = $prezzo_giornaliero * 7;
        } else {
            // Per il giornaliero, il prezzo base è semplicemente quello giornaliero.
            $prezzo_base = $prezzo_giornaliero;
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
        <a href="logout.php">Logout (<?= htmlspecialchars($nome_cliente) ?>)</a>
    </nav>
    <main>
        <?php if ($errore): ?>
            <div class="messaggio errore" style="max-width: 700px; margin: 2rem auto;">
                <h2>Errore</h2>
                <p><?= htmlspecialchars($errore) ?></p>
                <a href="mappa.php" class="button">Torna alla Mappa</a>
            </div>
        <?php elseif ($ombrellone): ?>
            <div style="text-align: center; margin-bottom: 30px;"><h2 style="font-size: 2.2em; color: #3b2a1a;">Un ultimo passo...</h2><p style="font-size: 1.2em; color: #7c3f06; margin-top: 0;">Controlla i dettagli e conferma.</p></div>
            
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
                <p id="prezzo_totale"><strong>Prezzo Totale:</strong> €<?= $prezzo_base ?></p>
            </div>

            <div style="text-align: center; margin-bottom: 25px;"><a href="mappa.php?data_ricerca=<?= htmlspecialchars($data_selezionata) ?>" class="button" style="text-decoration: none; background-color: #c08457;">← Cambia Selezione</a></div>

            <form action="conferma.php" method="POST" class="form-prenotazione">
                <input type="hidden" name="id_ombrellone" value="<?= htmlspecialchars($ombrellone['id']) ?>">
                <input type="hidden" name="data_prenotazione" value="<?= htmlspecialchars($data_selezionata) ?>">
                <input type="hidden" name="importo" id="importo_totale_hidden" value="<?= $prezzo_base ?>">
                <input type="hidden" name="tipo_prenotazione" value="<?= htmlspecialchars($tipo_prenotazione) ?>">

                <fieldset>
                    <legend>Dati Cliente</legend>
                    <div class="form-group"><label>Nome:</label><input type="text" name="nome" value="<?= htmlspecialchars($nome_cliente) ?>" readonly></div>
                    <div class="form-group"><label>Cognome:</label><input type="text" name="cognome" value="<?= htmlspecialchars($cognome_cliente) ?>" readonly></div>
                </fieldset>

                <?php if ($tipo_prenotazione === 'giornaliero'): ?>
                    <fieldset>
                        <legend>Opzioni Aggiuntive</legend>
                        <div class="form-group">
                            <label for="ombrelloni_extra">Ombrelloni aggiuntivi (max 2):</label>
                            <select name="ombrelloni_extra" id="ombrelloni_extra">
                                <option value="0">0</option>
                                <option value="1">1 (+€25)</option>
                                <option value="2">2 (+€50)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Servizi Extra:</label>
                            <label style="display: block; margin-bottom: 5px;"><input type="checkbox" name="servizi_extra[]" value="aperitivo"> Aperitivo (+€10)</label>
                            <label style="display: block; margin-bottom: 5px;"><input type="checkbox" name="servizi_extra[]" value="pedalo"> Noleggio Pedalò (+€20)</label>
                            <label style="display: block;"><input type="checkbox" name="servizi_extra[]" value="asciugamani"> Asciugamani extra (+€5)</label>
                        </div>
                    </fieldset>
                <?php else: ?>
                    <fieldset>
                        <legend>Opzioni Abbonamento</legend>
                         <div class="form-group">
                            <label style="display: block; margin-bottom: 10px;"><input type="radio" name="abbonamento_extra" value="nessuno" checked> Abbonamento Standard</label>
                            <label style="display: block; margin-bottom: 10px;"><input type="radio" name="abbonamento_extra" value="premium"> Abbonamento Premium (asciugamani giornalieri) (+€20)</label>
                            <label style="display: block;"><input type="radio" name="abbonamento_extra" value="vip"> Abbonamento VIP (aperitivo giornaliero) (+€40)</label>
                        </div>
                    </fieldset>
                <?php endif; ?>

                <div class="form-group"><button type="submit">Conferma e Prenota</button></div>
            </form>
            
            <script>
                const prezzoBase = <?= $prezzo_base ?>;
                const prezzoTotaleEl = document.getElementById('prezzo_totale');
                const importoHiddenEl = document.getElementById('importo_totale_hidden');
                
                const selectOmbrelloni = document.getElementById('ombrelloni_extra');
                const serviziCheckbox = document.querySelectorAll('input[name="servizi_extra[]"]');
                if (selectOmbrelloni && serviziCheckbox.length > 0) {
                    function aggiornaPrezzoGiornaliero() {
                        let prezzoExtra = 0;
                        prezzoExtra += parseInt(selectOmbrelloni.value) * 25;
                        serviziCheckbox.forEach(cb => {
                            if (cb.checked) {
                                switch (cb.value) {
                                    case 'aperitivo': prezzoExtra += 10; break;
                                    case 'pedalo': prezzoExtra += 20; break;
                                    case 'asciugamani': prezzoExtra += 5; break;
                                }
                            }
                        });
                        const totale = prezzoBase + prezzoExtra;
                        prezzoTotaleEl.innerHTML = `<strong>Prezzo Totale:</strong> €${totale}`;
                        importoHiddenEl.value = totale;
                    }
                    selectOmbrelloni.addEventListener('change', aggiornaPrezzoGiornaliero);
                    serviziCheckbox.forEach(cb => cb.addEventListener('change', aggiornaPrezzoGiornaliero));
                }

                const radioAbbonamento = document.querySelectorAll('input[name="abbonamento_extra"]');
                if (radioAbbonamento.length > 0) {
                    function aggiornaPrezzoSettimanale() {
                        let prezzoExtra = 0;
                        const scelta = document.querySelector('input[name="abbonamento_extra"]:checked').value;
                        if (scelta === 'premium') {
                            prezzoExtra = 20;
                        } else if (scelta === 'vip') {
                            prezzoExtra = 40;
                        }
                        // Lo script aggiunge il costo extra (20 o 40) al prezzo base già calcolato (giornaliero * 7)
                        const totale = prezzoBase + prezzoExtra;
                        prezzoTotaleEl.innerHTML = `<strong>Prezzo Totale:</strong> €${totale}`;
                        importoHiddenEl.value = totale;
                    }
                    radioAbbonamento.forEach(radio => radio.addEventListener('change', aggiornaPrezzoSettimanale));
                }
            </script>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>