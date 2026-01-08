<?php
$page_title = 'Gestione Date Disponibili';
require_once 'partials/header.php';

$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_inizio = $_POST['data_inizio'] ?? null;
    $data_fine = $_POST['data_fine'] ?? null;

    if ($data_inizio && $data_fine && $data_inizio <= $data_fine) {
        try {
            $pdo->beginTransaction();

            // 1. Recupera tutti gli ID degli ombrelloni
            $ombrelloni = $pdo->query("SELECT id FROM ombrellone")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($ombrelloni)) {
                throw new Exception("Nessun ombrellone trovato nel database. Impossibile procedere.");
            }

            // 2. Prepara la query di inserimento
            $sql = "INSERT IGNORE INTO giornodisponibilita (data, idOmbrellone) VALUES (:data, :id_ombrellone)";
            $stmt = $pdo->prepare($sql);

            // 3. Itera sulle date e sugli ombrelloni per inserire i record
            $periodo = new DatePeriod(
                new DateTime($data_inizio),
                new DateInterval('P1D'),
                (new DateTime($data_fine))->modify('+1 day') // Include la data di fine
            );

            $giorni_aggiunti = 0;
            foreach ($periodo as $data) {
                $data_formattata = $data->format('Y-m-d');
                foreach ($ombrelloni as $id_ombrellone) {
                    $stmt->execute(['data' => $data_formattata, 'id_ombrellone' => $id_ombrellone]);
                    if ($stmt->rowCount() > 0) {
                        $giorni_aggiunti++;
                    }
                }
            }

            $pdo->commit();
            $messaggio = "Operazione completata. Aggiunti {$giorni_aggiunti} nuovi slot di disponibilità nel periodo dal " . date("d/m/Y", strtotime($data_inizio)) . " al " . date("d/m/Y", strtotime($data_fine)) . ".";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errore = "Errore durante l'aggiunta delle date: " . $e->getMessage();
        }
    } else {
        $errore = "Le date inserite non sono valide. Assicurati che la data di inizio sia precedente o uguale a quella di fine.";
    }
}

// Recupero l'ultima data disponibile per mostrarla all'utente
$ultima_data = null;
try {
    $stmt_last = $pdo->query("SELECT MAX(data) FROM giornodisponibilita");
    $ultima_data = $stmt_last->fetchColumn();
} catch (Exception $e) {
    // Ignora errori non critici per la visualizzazione
}
?>

<h1>Gestione Date Disponibili</h1>
<p>Usa questo modulo per "aprire" la spiaggia per un determinato periodo. Il sistema creerà le disponibilità per tutti gli ombrelloni nelle date specificate.<br><strong>Attenzione:</strong> L'operazione non sovrascrive le date esistenti, ma aggiunge solo quelle mancanti.</p>

<div class="glass-panel" style="text-align: center; margin-bottom: 20px; padding: 15px;">
    <h3 style="margin-top: 0;">Stato Attuale Calendario</h3>
    <?php if ($ultima_data): ?>
        <p style="font-size: 1.1em;">Le disponibilità sono attualmente caricate fino al: <strong style="color: #fff; background: rgba(0,0,0,0.2); padding: 2px 8px; border-radius: 4px;"><?= date("d/m/Y", strtotime($ultima_data)) ?></strong></p>
    <?php else: ?>
        <p>Nessuna data disponibile caricata nel sistema.</p>
    <?php endif; ?>
</div>

<?php if ($messaggio): ?><div class="messaggio successo glass-panel"><p><?= htmlspecialchars($messaggio) ?></p></div><?php endif; ?>
<?php if ($errore): ?><div class="messaggio errore glass-panel"><p><?= htmlspecialchars($errore) ?></p></div><?php endif; ?>

<form method="POST" action="gestione_date.php" class="form-prenotazione glass-panel" style="max-width: 600px;">
    <fieldset class="glass-panel">
        <legend>Aggiungi Periodo di Disponibilità</legend>
        <div class="form-group"><label for="data_inizio">Data Inizio:</label><input type="date" id="data_inizio" name="data_inizio" required></div>
        <div class="form-group"><label for="data_fine">Data Fine:</label><input type="date" id="data_fine" name="data_fine" required></div>
    </fieldset>
    <div class="form-group" style="text-align: center;"><button type="submit">Aggiungi Disponibilità</button></div>
</form>

<?php require_once 'partials/footer.php'; ?>
