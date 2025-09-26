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
?>

<h1>Gestione Date Disponibili</h1>
<p>Usa questo modulo per "aprire" la spiaggia per un determinato periodo. Il sistema creerà le disponibilità per tutti gli ombrelloni nelle date specificate.<br><strong>Attenzione:</strong> L'operazione non sovrascrive le date esistenti, ma aggiunge solo quelle mancanti.</p>

<?php if ($messaggio): ?><div class="messaggio successo" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724;"><p><?= htmlspecialchars($messaggio) ?></p></div><?php endif; ?>
<?php if ($errore): ?><div class="messaggio errore"><p><?= htmlspecialchars($errore) ?></p></div><?php endif; ?>

<form method="POST" action="gestione_date.php" class="form-prenotazione" style="max-width: 600px;">
    <fieldset>
        <legend>Aggiungi Periodo di Disponibilità</legend>
        <div class="form-group"><label for="data_inizio">Data Inizio:</label><input type="date" id="data_inizio" name="data_inizio" required style="width:100%; padding:10px; border:1px solid #7c3f06; border-radius:6px; font-size:1em; box-sizing: border-box;"></div>
        <div class="form-group"><label for="data_fine">Data Fine:</label><input type="date" id="data_fine" name="data_fine" required style="width:100%; padding:10px; border:1px solid #7c3f06; border-radius:6px; font-size:1em; box-sizing: border-box;"></div>
    </fieldset>
    <div class="form-group"><button type="submit">Aggiungi Disponibilità</button></div>
</form>

<?php require_once 'partials/footer.php'; ?>

