<?php
$page_title = 'Modifica Tariffa';
require_once 'partials/header.php';

$codice_tariffa = $_GET['codice'] ?? '';
$errore = '';
$tariffa = null;
$tipologie_associate = [];

if (empty($codice_tariffa)) {
    $errore = "Codice tariffa non specificato.";
} else {
    try {
        // Recupero i dettagli della tariffa
        $stmt_tariffa = $pdo->prepare("SELECT * FROM tariffa WHERE codice = :codice");
        $stmt_tariffa->execute(['codice' => $codice_tariffa]);
        $tariffa = $stmt_tariffa->fetch(PDO::FETCH_ASSOC);

        if (!$tariffa) {
            $errore = "Tariffa non trovata.";
        } else {
            // Recupero le tipologie associate
            $stmt_assoc = $pdo->prepare("SELECT codTipologia FROM tipologiatariffa WHERE codTariffa = :codice");
            $stmt_assoc->execute(['codice' => $codice_tariffa]);
            $tipologie_associate = $stmt_assoc->fetchAll(PDO::FETCH_COLUMN);
        }

        // Recupero tutte le tipologie disponibili per le checkbox
        $tipologie = $pdo->query("SELECT * FROM tipologia ORDER BY codice")->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $errore = "Errore nel recupero dei dati: " . $e->getMessage();
    }
}
?>

<h1>Modifica Tariffa: <?= htmlspecialchars($codice_tariffa) ?></h1>

<?php if ($errore): ?>
    <div class="messaggio errore"><p><?= htmlspecialchars($errore) ?></p></div>
<?php elseif ($tariffa): ?>
    <form method="POST" action="processa_tariffa.php" class="form-prenotazione" style="max-width: 800px; text-align: left;">
        <input type="hidden" name="azione" value="modifica">
        <input type="hidden" name="codice_originale" value="<?= htmlspecialchars($tariffa['codice']) ?>">

        <fieldset>
            <legend>Dettagli Tariffa</legend>
            <div class="form-group">
                <label for="codice">Codice Tariffa:</label>
                <input type="text" id="codice" name="codice" value="<?= htmlspecialchars($tariffa['codice']) ?>" required maxlength="10">
            </div>
            <div class="form-group">
                <label for="prezzo">Prezzo:</label>
                <input type="number" id="prezzo" name="prezzo" value="<?= htmlspecialchars($tariffa['prezzo']) ?>" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo" required>
                    <option value="GIORNALIERO" <?= ($tariffa['tipo'] === 'GIORNALIERO') ? 'selected' : '' ?>>GIORNALIERO</option>
                    <option value="SETTIMANALE" <?= ($tariffa['tipo'] === 'SETTIMANALE') ? 'selected' : '' ?>>SETTIMANALE</option>
                </select>
            </div>
            <div class="form-group">
                <label for="numMinGiorni">Numero Minimo Giorni (opzionale):</label>
                <input type="number" id="numMinGiorni" name="numMinGiorni" value="<?= htmlspecialchars($tariffa['numMinGiorni']) ?>" min="1">
            </div>
        </fieldset>

        <fieldset>
            <legend>Periodo di Validità</legend>
            <div class="form-group">
                <label for="dataInizio">Data Inizio:</label>
                <input type="date" id="dataInizio" name="dataInizio" value="<?= htmlspecialchars($tariffa['dataInizio']) ?>" required>
            </div>
            <div class="form-group">
                <label for="dataFine">Data Fine:</label>
                <input type="date" id="dataFine" name="dataFine" value="<?= htmlspecialchars($tariffa['dataFine']) ?>" required>
            </div>
        </fieldset>

        <fieldset>
            <legend>Associazione Tipologie Ombrellone</legend>
            <?php foreach ($tipologie as $tipologia): ?>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="tipologie[]" value="<?= htmlspecialchars($tipologia['codice']) ?>" <?= in_array($tipologia['codice'], $tipologie_associate) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($tipologia['nome']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </fieldset>

        <div class="form-group" style="text-align: right; margin-top: 30px;">
            <a href="gestione_tariffe.php" class="button-link" style="background-color: #6c757d;">Annulla</a>
            <button type="submit">Salva Modifiche</button>
        </div>
    </form>
<?php endif; ?>

<?php require_once 'partials/footer.php'; ?>
