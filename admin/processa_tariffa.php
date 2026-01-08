<?php
session_start();
// Non includere l'header qui per evitare output prima del reindirizzamento
require_once '../src/db_connection.php'; 

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accesso negato.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: gestione_tariffe.php');
    exit();
}

$azione = $_POST['azione'] ?? '';

try {
    $pdo->beginTransaction();

    if ($azione === 'crea') {
        $codice = $_POST['codice'];
        $prezzo = $_POST['prezzo'];
        $tipo = $_POST['tipo'];
        $descrizione = $_POST['descrizione'] ?? '';
        $numMinGiorni = !empty($_POST['numMinGiorni']) ? $_POST['numMinGiorni'] : null;
        $dataInizio = $_POST['dataInizio'];
        $dataFine = $_POST['dataFine'];
        $tipologie = $_POST['tipologie'] ?? [];

        if (empty($codice) || empty($prezzo) || empty($tipo) || empty($dataInizio) || empty($dataFine)) {
            throw new Exception("Tutti i campi obbligatori devono essere compilati.");
        }

        $sql_tariffa = "INSERT INTO tariffa (codice, prezzo, tipo, numMinGiorni, dataInizio, dataFine, descrizione) VALUES (:codice, :prezzo, :tipo, :numMinGiorni, :dataInizio, :dataFine, :descrizione)";
        $stmt_tariffa = $pdo->prepare($sql_tariffa);
        $stmt_tariffa->execute([
            ':codice' => $codice,
            ':prezzo' => $prezzo,
            ':tipo' => $tipo,
            ':numMinGiorni' => $numMinGiorni,
            ':dataInizio' => $dataInizio,
            ':dataFine' => $dataFine,
            ':descrizione' => $descrizione
        ]);

        if (!empty($tipologie)) {
            $sql_assoc = "INSERT INTO tipologiatariffa (codTariffa, codTipologia) VALUES (:codTariffa, :codTipologia)";
            $stmt_assoc = $pdo->prepare($sql_assoc);
            foreach ($tipologie as $codTipologia) {
                $stmt_assoc->execute([':codTariffa' => $codice, ':codTipologia' => $codTipologia]);
            }
        }

        $_SESSION['messaggio_tariffa'] = "Tariffa '{$codice}' creata con successo!";

    } elseif ($azione === 'modifica') {
        $codice_originale = $_POST['codice_originale'];
        $codice = $_POST['codice'];
        $prezzo = $_POST['prezzo'];
        $tipo = $_POST['tipo'];
        $descrizione = $_POST['descrizione'] ?? '';
        $numMinGiorni = !empty($_POST['numMinGiorni']) ? $_POST['numMinGiorni'] : null;
        $dataInizio = $_POST['dataInizio'];
        $dataFine = $_POST['dataFine'];
        $tipologie = $_POST['tipologie'] ?? [];

        if (empty($codice) || empty($prezzo) || empty($tipo) || empty($dataInizio) || empty($dataFine)) {
            throw new Exception("Tutti i campi obbligatori devono essere compilati.");
        }

        $sql_update = "UPDATE tariffa SET codice = :codice, prezzo = :prezzo, tipo = :tipo, numMinGiorni = :numMinGiorni, dataInizio = :dataInizio, dataFine = :dataFine, descrizione = :descrizione WHERE codice = :codice_originale";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([
            ':codice' => $codice,
            ':prezzo' => $prezzo,
            ':tipo' => $tipo,
            ':numMinGiorni' => $numMinGiorni,
            ':dataInizio' => $dataInizio,
            ':dataFine' => $dataFine,
            ':descrizione' => $descrizione,
            ':codice_originale' => $codice_originale
        ]);

        $sql_delete_assoc = "DELETE FROM tipologiatariffa WHERE codTariffa = :codTariffa";
        $stmt_delete = $pdo->prepare($sql_delete_assoc);
        $stmt_delete->execute(['codTariffa' => $codice]);

        if (!empty($tipologie)) {
            $sql_insert_assoc = "INSERT INTO tipologiatariffa (codTariffa, codTipologia) VALUES (:codTariffa, :codTipologia)";
            $stmt_insert = $pdo->prepare($sql_insert_assoc);
            foreach ($tipologie as $codTipologia) {
                $stmt_insert->execute([':codTariffa' => $codice, ':codTipologia' => $codTipologia]);
            }
        }

        $_SESSION['messaggio_tariffa'] = "Tariffa '{$codice}' aggiornata con successo!";

    } else {
        throw new Exception("Azione non valida.");
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['messaggio_tariffa'] = "Errore: " . $e->getMessage();
}

header('Location: gestione_tariffe.php');
exit();
?>
