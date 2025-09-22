<?php
// Abilita la visualizzazione degli errori per il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

$ombrellone = null;
$errore = '';
$data_selezionata = '';
$prezzo_base = 0;

// Controlla se abbiamo ricevuto i dati necessari (ID ombrellone e data)
if (isset($_GET['id']) && isset($_GET['data']) && !empty($_GET['id']) && !empty($_GET['data'])) {
    
    $id_ombrellone = $_GET['id'];
    $data_selezionata = $_GET['data'];

    // Recupera i dati dell'ombrellone
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
        // Prezzo base stimato
        $prezzo_base = ($ombrellone['cod_tipologia'] === 'VIP') ? 50 : 30;
    }

} else {
    $errore = "Dati mancanti. Impossibile procedere con la prenotazione.";
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
        /* Piccolo stile interno per i form */
        .riepilogo-box { background: #f4f0e9; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        fieldset { margin-bottom: 15px; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
        legend { font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <header>Riepilogo Prenotazione</header>
    <nav>
        <a href="index.php">Torna alla Mappa</a>
        <a href="#">Servizi</a>
        <a href="#">Contatti</a>
    </nav>
    <main>
        <?php if ($errore): ?>
            <div class="messaggio errore">
                <h2>Errore</h2>
                <p><?= htmlspecialchars($errore) ?></p>
                <a href="index.php" class="button-link">Torna alla ricerca</a>
            </div>
        <?php elseif ($ombrellone): ?>

            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-size: 2.2em; color: #3b2a1a; text-shadow: 1px 1px 3px rgba(0,0,0,0.1); margin: 0 0 5px 0;">Un ultimo passo...</h2>
                <p style="font-size: 1.2em; color: #7c3f06; margin-top: 0;">Controlla i dettagli e conferma la tua giornata al mare.</p>
            </div>

            <div class="riepilogo-box">
                <p><strong>Data:</strong> <?= htmlspecialchars($data_selezionata) ?></p>
                <p><strong>Ombrellone ID:</strong> <?= htmlspecialchars($ombrellone['id']) ?></p>
                <p><strong>Posizione:</strong> Settore <?= htmlspecialchars($ombrellone['settore']) ?>, Fila <?= htmlspecialchars($ombrellone['numFila']) ?>, Posto <?= htmlspecialchars($ombrellone['numPostoFila']) ?></p>
                <p><strong>Tipologia:</strong> <?= htmlspecialchars($ombrellone['nome_tipologia']) ?> (<?= htmlspecialchars($ombrellone['descrizione']) ?>)</p>
                <p id="prezzo_totale"><strong>Prezzo totale stimato:</strong> €<?= $prezzo_base ?></p>
            </div>
            
            <div style="text-align: center; margin-bottom: 25px;">
                <a href="index.php?data_ricerca=<?= htmlspecialchars($data_selezionata) ?>" class="button" style="text-decoration: none; background-color: #c08457; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">← Cambia la tua selezione</a>
            </div>

            <form action="conferma.php" method="POST" class="form-prenotazione">
                <input type="hidden" name="id_ombrellone" value="<?= htmlspecialchars($ombrellone['id']) ?>">
                <input type="hidden" name="data_prenotazione" value="<?= htmlspecialchars($data_selezionata) ?>">

                <div class="form-group">
                    <label for="codice_cliente">Il tuo Codice Cliente:</label>
                    <input type="text" id="codice_cliente" name="codice_cliente" placeholder="Es. CLIENTE0001" required>
                </div>

                <div class="form-group">
                    <label for="ombrelloni_extra">Ombrelloni aggiuntivi (max 2):</label>
                    <select name="ombrelloni_extra" id="ombrelloni_extra">
                        <option value="0">0</option>
                        <option value="1">1 (+€25)</option>
                        <option value="2">2 (+€50)</option>
                    </select>
                </div>

                <fieldset class="form-group">
                    <legend>Servizi Extra:</legend>
                    <label><input type="checkbox" name="servizi_extra[]" value="aperitivo"> Aperitivo (+€10)</label><br>
                    <label><input type="checkbox" name="servizi_extra[]" value="pedalo"> Pedalo (+€20)</label><br>
                    <label><input type="checkbox" name="servizi_extra[]" value="asciugamani"> Asciugamani extra (+€5)</label>
                </fieldset>

                <div class="form-group">
                    <button type="submit">Conferma la Prenotazione</button>
                </div>
            </form>

            <script>
                const prezzoBase = <?= $prezzo_base ?>;
                const selectOmbrelloni = document.getElementById('ombrelloni_extra');
                const serviziCheckbox = document.querySelectorAll('input[name="servizi_extra[]"]');
                const prezzoTotaleEl = document.getElementById('prezzo_totale');

                function aggiornaPrezzo() {
                    let prezzoExtra = 0;
                    prezzoExtra += parseInt(selectOmbrelloni.value) * 25;
                    serviziCheckbox.forEach(cb => {
                        if(cb.checked){
                            switch(cb.value){
                                case 'aperitivo': prezzoExtra += 10; break;
                                case 'pedalo': prezzoExtra += 20; break;
                                case 'asciugamani': prezzoExtra += 5; break;
                            }
                        }
                    });
                    const totale = prezzoBase + prezzoExtra;
                    prezzoTotaleEl.innerHTML = `<strong>Prezzo totale stimato:</strong> €${totale}`;
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