<?php
session_start();
require_once '../src/db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato.');
}

$codice_tariffa = $_GET['codice'] ?? '';

if (empty($codice_tariffa)) {
    $_SESSION['messaggio_tariffa'] = "Errore: Codice tariffa non specificato.";
    header('Location: gestione_tariffe.php');
    exit();
}

try {
    // 1. Controllo di sicurezza: la tariffa è usata in qualche contratto?
    $sql_check = "SELECT COUNT(*) FROM contratto WHERE codTariffa = :codice_tariffa";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute(['codice_tariffa' => $codice_tariffa]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        throw new Exception("Impossibile eliminare la tariffa '{$codice_tariffa}' perché è associata a {$count} contratti esistenti.");
    }

    // 2. Se non è usata, procedi con l'eliminazione (in una transazione)
    $pdo->beginTransaction();

    // Elimina prima le associazioni
    $sql_delete_assoc = "DELETE FROM tipologiatariffa WHERE codTariffa = :codice_tariffa";
    $stmt_delete_assoc = $pdo->prepare($sql_delete_assoc);
    $stmt_delete_assoc->execute(['codice_tariffa' => $codice_tariffa]);

    // Poi elimina la tariffa
    $sql_delete_tariffa = "DELETE FROM tariffa WHERE codice = :codice_tariffa";
    $stmt_delete_tariffa = $pdo->prepare($sql_delete_tariffa);
    $stmt_delete_tariffa->execute(['codice_tariffa' => $codice_tariffa]);

    $pdo->commit();

    $_SESSION['messaggio_tariffa'] = "Tariffa '{$codice_tariffa}' eliminata con successo!";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['messaggio_tariffa'] = "Errore: " . $e->getMessage();
}

header('Location: gestione_tariffe.php');
exit();
?>
