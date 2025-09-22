<?php
session_start();

// Protezione: se l'utente non è loggato, non può prenotare
if (!isset($_SESSION['codice_cliente'])) {
    header('Location: accesso.php');
    exit();
}

// Abilita la visualizzazione degli errori per il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

$ombrellone = null;
$errore = '';
$data_selezionata = '';
$prezzo_base = 0;

// Recupero dati utente dalla sessione
$nome_cliente = $_SESSION['nome_cliente'];
$cognome_cliente = $_SESSION['cognome_cliente'];
$dataNascita_cliente = $_SESSION['dataNascita_cliente'];
$codice_cliente = $_SESSION['codice_cliente'];

if (isset($_GET['id']) && isset($_GET['data']) && !empty($_GET['id']) && !empty($_GET['data'])) {
    $id_ombrellone = $_GET['id'];
    $data_selezionata = $_GET['data'];
    $sql = "
        SELECT o.id, o.settore, o.numFila, o.numPostoFila, t.nome AS nome_tipologia, t.descrizione, t.codice AS cod_tipologia
        FROM ombrellone o
        JOIN tipologia t ON o.codTipologia = t.codice
        WHERE o.id = :id_ombrellone
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_ombrellone' => $id_ombrellone]);
    $ombrellone = $stmt->fetch();
    if (!$ombrellone) {
        $errore = "Ombrellone non trovato.";
    } else {
        $prezzo_base = ($ombrellone['cod_tipologia'] === 'VIP') ? 50 : 30;
    }
} else {
    $errore = "Dati mancanti per la prenotazione.";
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conferma Prenotazione</title>
    <link rel="stylesheet" href="stile.css">
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
            <div class="messaggio errore">
                <h2>Errore</h2>
                <p><?= htmlspecialchars($errore) ?></p>
                <a href="mappa.php" class="button">Torna alla Mappa</a>
            </div>
        <?php elseif ($ombrellone): ?>

            <div style="text-align: center; margin-bottom: 30px;"><h2 style="font-size: 2.2em; color: #3b2a1a;">Un ultimo passo...</h2><p style="font-size: 1.2em; color: #7c3f06; margin-top: 0;">Controlla i dettagli e conferma la tua giornata al mare.</p></div>
            <div class="riepilogo-box"><p><strong>Data:</strong> <?= htmlspecialchars($data_selezionata) ?></p><p><strong>Ombrellone:</strong> Settore <?= htmlspecialchars($ombrellone['settore']) ?>, Fila <?= htmlspecialchars($ombrellone['numFila']) ?>, Posto <?= htmlspecialchars($ombrellone['numPostoFila']) ?></p><p><strong>Tipologia:</strong> <?= htmlspecialchars($ombrellone['nome_tipologia']) ?></p><p id="prezzo_totale"><strong>Prezzo Totale:</strong> €<?= $prezzo_base ?></p></div>
            <div style="text-align: center; margin-bottom: 25px;"><a href="mappa.php?data_ricerca=<?= htmlspecialchars($data_selezionata) ?>" class="button" style="text-decoration: none; background-color: #c08457;">← Cambia la tua selezione</a></div>

            <form action="conferma.php" method="POST" class="form-prenotazione">
                <input type="hidden" name="id_ombrellone" value="<?= htmlspecialchars($ombrellone['id']) ?>">
                <input type="hidden" name="data_prenotazione" value="<?= htmlspecialchars($data_selezionata) ?>">
                <input type="hidden" name="importo" id="importo_totale_hidden" value="<?= $prezzo_base ?>">

                <fieldset>
                    <legend>Dati Cliente</legend>
                    <div class="form-group">
                        <label for="nome">Nome:</label>
                        <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome_cliente) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="cognome">Cognome:</label>
                        <input type="text" id="cognome" name="cognome" value="<?= htmlspecialchars($cognome_cliente) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="data_nascita">Data di Nascita:</label>
                        <input type="date" id="data_nascita" name="data_nascita" value="<?= htmlspecialchars($dataNascita_cliente) ?>" style="width:100%;" readonly>
                    </div>
                </fieldset>

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
                        <label><input type="checkbox" name="servizi_extra[]" value="aperitivo"> Aperitivo (+€10)</label><br>
                        <label><input type="checkbox" name="servizi_extra[]" value="pedalo"> Noleggio Pedalò (+€20)</label><br>
                        <label><input type="checkbox" name="servizi_extra[]" value="asciugamani"> Asciugamani extra (+€5)</label>
                    </div>
                </fieldset>

                <div class="form-group">
                    <button type="submit">Conferma e Prenota</button>
                </div>
            </form>

            <script>
                const prezzoBase = <?= $prezzo_base ?>;
                const selectOmbrelloni = document.getElementById('ombrelloni_extra');
                const serviziCheckbox = document.querySelectorAll('input[name="servizi_extra[]"]');
                const prezzoTotaleEl = document.getElementById('prezzo_totale');
                // Nuovo: riferimento all'input nascosto
                const importoHiddenEl = document.getElementById('importo_totale_hidden');

                function aggiornaPrezzo() {
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
                    // Aggiorna sia il testo visibile...
                    prezzoTotaleEl.innerHTML = `<strong>Prezzo Totale:</strong> €${totale}`;
                    // ...sia il valore dell'input nascosto da inviare
                    importoHiddenEl.value = totale;
                }

                selectOmbrelloni.addEventListener('change', aggiornaPrezzo);
                serviziCheckbox.forEach(cb => cb.addEventListener('change', aggiornaPrezzo));
            </script>

        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>