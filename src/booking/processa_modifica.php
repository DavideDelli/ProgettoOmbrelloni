<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../db_connection.php';

// 1. Check user authentication
if (!isset($_SESSION['codice_cliente'])) {
    header('Location: ../auth/accesso.php');
    exit();
}

// 2. Ensure the request is a POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ../../le_mie_prenotazioni.php');
    exit();
}

// 3. Retrieve and validate POST data
$id_contratto = $_POST['id_contratto'] ?? null;
$data_nuova = $_POST['data_nuova'] ?? null;
$cod_tariffa_nuovo = $_POST['cod_tariffa_nuovo'] ?? null;
$tipo_prenotazione = $_POST['tipo_prenotazione'] ?? null;

if (!$id_contratto || !$data_nuova || !$cod_tariffa_nuovo || !$tipo_prenotazione) {
    $_SESSION['errore_modifica'] = "Dati del modulo mancanti.";
    $redirect_url = $id_contratto ? 'modifica_prenotazione.php?id=' . $id_contratto : '../../le_mie_prenotazioni.php';
    header('Location: ' . $redirect_url);
    exit();
}

$codice_cliente = $_SESSION['codice_cliente'];

try {
    $pdo->beginTransaction();

    // Step 1: Lock and verify the existing contract
    $sql_contratto = "
        SELECT c.codiceCliente, c.data AS data_vecchia, gd.idOmbrellone
        FROM contratto c
        JOIN giornodisponibilita gd ON c.numProgr = gd.numProgrContratto
        WHERE c.numProgr = :id_contratto
        GROUP BY c.numProgr
        FOR UPDATE
    ";
    $stmt_contratto = $pdo->prepare($sql_contratto);
    $stmt_contratto->execute(['id_contratto' => $id_contratto]);
    $contratto_vecchio = $stmt_contratto->fetch();

    if (!$contratto_vecchio || $contratto_vecchio['codiceCliente'] !== $codice_cliente) {
        throw new Exception("Prenotazione non trovata o non sei autorizzato a modificarla.");
    }
    
    // Step 2: Check if the reservation is in the future
    if (strtotime($contratto_vecchio['data_vecchia']) <= strtotime(date("Y-m-d"))) {
        throw new Exception("Non è possibile modificare una prenotazione passata o odierna.");
    }
    
    $id_ombrellone = $contratto_vecchio['idOmbrellone'];

    // Step 3: Check availability for the new dates
    $data_inizio_nuova = $data_nuova;
    $data_fine_nuova = ($tipo_prenotazione === 'settimanale') ? date('Y-m-d', strtotime($data_inizio_nuova . ' +6 days')) : $data_inizio_nuova;

    $sql_check_disp = "
        SELECT COUNT(*) FROM giornodisponibilita 
        WHERE idOmbrellone = :id_ombrellone AND data BETWEEN :data_inizio AND :data_fine
        AND numProgrContratto IS NOT NULL AND numProgrContratto != :id_contratto
    ";
    $stmt_check_disp = $pdo->prepare($sql_check_disp);
    $stmt_check_disp->execute([
        'id_ombrellone' => $id_ombrellone,
        'data_inizio' => $data_inizio_nuova,
        'data_fine' => $data_fine_nuova,
        'id_contratto' => $id_contratto
    ]);

    if ($stmt_check_disp->fetchColumn() > 0) {
        throw new Exception("L'ombrellone non è disponibile per il nuovo periodo selezionato.");
    }

    // Step 4: Get the price for the new tariff
    $sql_prezzo = "SELECT prezzo FROM tariffa WHERE codice = :cod_tariffa";
    $stmt_prezzo = $pdo->prepare($sql_prezzo);
    $stmt_prezzo->execute(['cod_tariffa' => $cod_tariffa_nuovo]);
    $nuovo_importo = $stmt_prezzo->fetchColumn();
    if ($nuovo_importo === false) {
        throw new Exception("Tariffa selezionata non valida.");
    }

    // Step 5: Release the old reservation days
    $sql_release = "UPDATE giornodisponibilita SET numProgrContratto = NULL WHERE numProgrContratto = :id_contratto";
    $pdo->prepare($sql_release)->execute(['id_contratto' => $id_contratto]);

    // Step 6: Occupy the new reservation days
    $giorni_da_prenotare = ($tipo_prenotazione === 'settimanale') ? 7 : 1;
    for ($i = 0; $i < $giorni_da_prenotare; $i++) {
        $data_corrente = date('Y-m-d', strtotime($data_inizio_nuova . " +$i days"));
        $sql_occupa = "UPDATE giornodisponibilita SET numProgrContratto = :id_contratto WHERE idOmbrellone = :id_ombrellone AND data = :data AND numProgrContratto IS NULL";
        $stmt_occupa = $pdo->prepare($sql_occupa);
        $stmt_occupa->execute(['id_contratto' => $id_contratto, 'id_ombrellone' => $id_ombrellone, 'data' => $data_corrente]);
        if ($stmt_occupa->rowCount() === 0) {
            throw new Exception("Errore di concorrenza. Qualcuno ha prenotato l'ombrellone per il giorno " . date("d/m/Y", strtotime($data_corrente)) . " mentre completavi la modifica. L'operazione è stata annullata.");
        }
    }

    // Step 7: Update the contract with the new details
    $sql_update_contratto = "
        UPDATE contratto SET data = :data_nuova, dataFine = :data_fine_nuova, importo = :importo_nuovo, codTariffa = :cod_tariffa_nuovo
        WHERE numProgr = :id_contratto
    ";
    $stmt_update_contratto = $pdo->prepare($sql_update_contratto);
    $stmt_update_contratto->execute([
        'data_nuova' => $data_inizio_nuova,
        'data_fine_nuova' => ($tipo_prenotazione === 'settimanale') ? $data_fine_nuova : NULL,
        'importo_nuovo' => $nuovo_importo,
        'cod_tariffa_nuovo' => $cod_tariffa_nuovo,
        'id_contratto' => $id_contratto
    ]);

    // Step 8: Commit the transaction
    $pdo->commit();
    header('Location: ../../le_mie_prenotazioni.php?status=updated');
    exit();

} catch (Exception $e) {
    // If anything goes wrong, roll back the transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['errore_modifica'] = $e->getMessage();
    
    // Redirect back to the modification page with an error
    $redirect_url = 'modifica_prenotazione.php?id=' . $id_contratto;
    header('Location: ' . $redirect_url);
    exit();
}
