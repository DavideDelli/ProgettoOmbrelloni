<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: accesso.php');
    exit();
}

$messaggio = '';
$successo = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_ombrellone = $_POST['id_ombrellone'];
    $data_inizio = $_POST['data_prenotazione'];
    $tipo_prenotazione = $_POST['tipo_prenotazione'];
    $codice_tariffa = $_POST['cod_tariffa'];
    $codice_cliente = $_SESSION['codice_cliente'];

    try {
        // --- CALCOLO SICURO DEL PREZZO ---
        $sql_prezzo = "
            SELECT tar.prezzo
            FROM tariffa tar
            JOIN tipologiatariffa tt ON tar.codice = tt.codTariffa
            JOIN ombrellone o ON tt.codTipologia = o.codTipologia
            WHERE o.id = :id_ombrellone AND tar.codice = :cod_tariffa
        ";
        $stmt_prezzo = $pdo->prepare($sql_prezzo);
        $stmt_prezzo->execute(['id_ombrellone' => $id_ombrellone, 'cod_tariffa' => $codice_tariffa]);
        $risultato_prezzo = $stmt_prezzo->fetch();

        if (!$risultato_prezzo) {
            throw new Exception("La tariffa selezionata non è valida per questo ombrellone.");
        }
        $importo_finale = (float) $risultato_prezzo['prezzo'];

        // --- INIZIO TRANSAZIONE E CONTROLLO DISPONIBILITÀ ---
        $pdo->beginTransaction();

        if ($tipo_prenotazione === 'settimanale') {
            $data_fine_calcolata = date('Y-m-d', strtotime($data_inizio . ' +6 days'));

            $sql_check = "
                SELECT COUNT(*) 
                FROM giornodisponibilita 
                WHERE idOmbrellone = :id_ombrellone 
                  AND data BETWEEN :data_inizio AND :data_fine
                  AND numProgrContratto IS NOT NULL
            ";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([
                'id_ombrellone' => $id_ombrellone,
                'data_inizio' => $data_inizio,
                'data_fine' => $data_fine_calcolata
            ]);
            $giorni_occupati = $stmt_check->fetchColumn();

            if ($giorni_occupati > 0) {
                throw new Exception("Impossibile completare la prenotazione. L'ombrellone non è disponibile per l'intero periodo selezionato.");
            }
        }
        
        // --- INSERIMENTO CONTRATTO CON LA DATA CORRETTA ---
        $data_fine_contratto = ($tipo_prenotazione === 'settimanale') ? date('Y-m-d', strtotime($data_inizio . ' +6 days')) : NULL;
        
        // MODIFICATO: Sostituito CURDATE() con :data_inizio per salvare la data della prenotazione
        $sql_contratto = "INSERT INTO contratto (data, dataFine, importo, codiceCliente, codTariffa) VALUES (:data_inizio, :data_fine, :importo, :codice, :cod_tariffa)";
        $stmt_contratto = $pdo->prepare($sql_contratto);
        $stmt_contratto->execute([
            'data_inizio' => $data_inizio, // MODIFICATO: Aggiunto il parametro corretto
            'data_fine' => $data_fine_contratto,
            'importo' => $importo_finale, 
            'codice' => $codice_cliente,
            'cod_tariffa' => $codice_tariffa
        ]);
        $nuovo_contratto_id = $pdo->lastInsertId();

        // Aggiorna le righe in giornodisponibilita
        $giorni_da_prenotare = ($tipo_prenotazione === 'settimanale') ? 7 : 1;

        for ($i = 0; $i < $giorni_da_prenotare; $i++) {
            $data_corrente = date('Y-m-d', strtotime($data_inizio . " +$i days"));
            $sql_aggiorna = "UPDATE giornodisponibilita SET numProgrContratto = :id_contratto WHERE idOmbrellone = :id_ombrellone AND data = :data AND numProgrContratto IS NULL";
            $stmt_aggiorna = $pdo->prepare($sql_aggiorna);
            $stmt_aggiorna->execute([
                'id_contratto' => $nuovo_contratto_id,
                'id_ombrellone' => $id_ombrellone,
                'data' => $data_corrente
            ]);
            
            if ($stmt_aggiorna->rowCount() === 0) {
                throw new Exception("Errore di concorrenza. Qualcuno ha prenotato l'ombrellone per il giorno " . date("d/m/Y", strtotime($data_corrente)) . " mentre completavi l'operazione. La prenotazione è stata annullata.");
            }
        }

        $pdo->commit();
        $messaggio = "Prenotazione confermata con successo! Il tuo numero di contratto è {$nuovo_contratto_id}.";
        $successo = true;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $messaggio = "Errore durante la prenotazione: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Esito Prenotazione</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
</head>
<body>
    <div class="container">
        <header>Esito Prenotazione</header>
        <main style="padding-top: 50px;">
             <div class="messaggio <?= $successo ? 'successo' : 'errore' ?>">
                <h2><?= $successo ? 'Congratulazioni!' : 'Attenzione!' ?></h2>
                <p><?= htmlspecialchars($messaggio) ?></p>
                <a href="mappa.php" class="button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Torna alla Mappa</a>
            </div>
        </main>
    </div>
</body>
</html>