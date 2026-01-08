<?php
$page_title = 'Aggiungi Tariffa';
require_once 'partials/header.php';

// Recupero le tipologie di ombrellone per le checkbox
try {
    $tipologie = $pdo->query("SELECT * FROM tipologia ORDER BY codice")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errore = "Impossibile caricare le tipologie: " . $e->getMessage();
    $tipologie = [];
}
?>

<h1>Aggiungi Nuova Tariffa</h1>
<p>Compila i campi sottostanti per creare una nuova tariffa.</p>

<?php if (!empty($errore)): ?>
    <div class="messaggio errore glass-panel"><p><?= htmlspecialchars($errore) ?></p></div>
<?php endif; ?>

<form method="POST" action="processa_tariffa.php" class="form-prenotazione glass-panel" style="max-width: 800px; text-align: left;">
    <input type="hidden" name="azione" value="crea">

    <fieldset class="glass-panel">
        <legend>Dettagli Tariffa</legend>
        <div class="form-group">
            <label for="codice">Codice Tariffa (es. STD_D_2025):</label>
            <input type="text" id="codice" name="codice" required maxlength="10">
        </div>
        <div class="form-group">
            <label for="descrizione">Descrizione (es. Giornaliero Standard):</label>
            <input type="text" id="descrizione" name="descrizione" required>
        </div>
        <div class="form-group">
            <label for="prezzo">Prezzo:</label>
            <input type="number" id="prezzo" name="prezzo" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label for="tipo">Tipo:</label>
            <select id="tipo" name="tipo" required>
                <option value="GIORNALIERO">GIORNALIERO</option>
                <option value="SETTIMANALE">SETTIMANALE</option>
            </select>
        </div>
        <div class="form-group">
            <label for="numMinGiorni">Numero Minimo Giorni (opzionale):</label>
            <input type="number" id="numMinGiorni" name="numMinGiorni" min="1">
        </div>
    </fieldset>

    <fieldset class="glass-panel">
        <legend>Periodo di Validità</legend>
        <div class="form-group">
            <label for="dataInizio">Data Inizio:</label>
            <input type="date" id="dataInizio" name="dataInizio" required>
        </div>
        <div class="form-group">
            <label for="dataFine">Data Fine:</label>
            <input type="date" id="dataFine" name="dataFine" required>
        </div>
    </fieldset>

    <fieldset class="glass-panel">
        <legend>Associazione Tipologie Ombrellone</legend>
        <p>Seleziona a quali tipologie di ombrellone questa tariffa si applica.</p>
        <?php foreach ($tipologie as $tipologia): ?>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="tipologie[]" value="<?= htmlspecialchars($tipologia['codice']) ?>">
                    <?= htmlspecialchars($tipologia['nome']) ?> (<?= htmlspecialchars($tipologia['descrizione']) ?>)
                </label>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <div class="form-group" style="text-align: right; margin-top: 30px;">
        <a href="gestione_tariffe.php" class="button-link" style="background-color: #6c757d;">Annulla</a>
        <button type="submit">Crea Tariffa</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const numMinGiorniInput = document.getElementById('numMinGiorni');

    function aggiornaStatoGiorni() {
        if (!tipoSelect || !numMinGiorniInput) return;

        if (tipoSelect.value === 'GIORNALIERO') {
            numMinGiorniInput.value = 1;
            numMinGiorniInput.readOnly = true;
            numMinGiorniInput.style.opacity = '0.5';
        } else if (tipoSelect.value === 'SETTIMANALE') {
            numMinGiorniInput.value = 7;
            numMinGiorniInput.readOnly = true;
            numMinGiorniInput.style.opacity = '0.5';
        }
    }

    tipoSelect.addEventListener('change', aggiornaStatoGiorni);
    aggiornaStatoGiorni(); // Imposta lo stato iniziale al caricamento
});
</script>

<?php require_once 'partials/footer.php'; ?>
