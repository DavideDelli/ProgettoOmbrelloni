<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../db_connection.php';

// 1. Protezione della pagina
if (!isset($_SESSION['codice_cliente'])) {
    header('Location: ../accesso.php');
    exit();
}

$codice_cliente = $_SESSION['codice_cliente'];
$nome_cliente = $_SESSION['nome_cliente'];

// 2. Logica di eliminazione
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_elimina'])) {
    try {
        $pdo->beginTransaction();

        // Step 1: Recupera gli ID di tutti i contratti del cliente
        $sql_get_contracts = "SELECT numProgr FROM contratto WHERE codiceCliente = :codice_cliente";
        $stmt_get_contracts = $pdo->prepare($sql_get_contracts);
        $stmt_get_contracts->execute(['codice_cliente' => $codice_cliente]);
        $contratti_ids = $stmt_get_contracts->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($contratti_ids)) {
            // Step 2: Libera i giorni in giornodisponibilita
            $placeholders = implode(',', array_fill(0, count($contratti_ids), '?'));
            $sql_release_days = "UPDATE giornodisponibilita SET numProgrContratto = NULL WHERE numProgrContratto IN ($placeholders)";
            $stmt_release_days = $pdo->prepare($sql_release_days);
            $stmt_release_days->execute($contratti_ids);

            // Step 3: Elimina i contratti
            $sql_delete_contracts = "DELETE FROM contratto WHERE codiceCliente = :codice_cliente";
            $stmt_delete_contracts = $pdo->prepare($sql_delete_contracts);
            $stmt_delete_contracts->execute(['codice_cliente' => $codice_cliente]);
        }

        // Step 4: Elimina il cliente
        $sql_delete_client = "DELETE FROM cliente WHERE codice = :codice_cliente";
        $stmt_delete_client = $pdo->prepare($sql_delete_client);
        $stmt_delete_client->execute(['codice_cliente' => $codice_cliente]);

        $pdo->commit();

        // Step 5: Esegui il logout e reindirizza
        session_unset();
        session_destroy();
        header('Location: ../account_eliminato.php');
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['messaggio_errore_profilo'] = "Errore durante l'eliminazione dell'account: " . $e->getMessage();
        header('Location: ../profilo.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Elimina Account</title>
    <link rel="stylesheet" href="../assets/css/stile.css?v=<?= filemtime('../assets/css/stile.css') ?>">
</head>
<body>
<div class="container">
    <header>Eliminazione Account</header>
    <main style="padding-top: 50px;">
        <div class="form-prenotazione" style="max-width: 700px; text-align: center;">
            <h3 style="color: #c82333; border-color: #c82333;">Sei assolutamente sicuro?</h3>
            <p style="text-align: left;">Stai per eliminare definitivamente il tuo account <strong><?= htmlspecialchars($nome_cliente) ?></strong> (Codice: <?= htmlspecialchars($codice_cliente) ?>).</p>
            <p style="text-align: left;"><strong>Questa azione è irreversibile.</strong> Tutte le tue prenotazioni future verranno cancellate e non potrai più accedere al tuo profilo.</p>
            <form method="POST" action="elimina_account.php?id=<?= $codice_cliente ?>" style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
                <input type="hidden" name="conferma_elimina" value="1">
                <a href="../profilo.php" class="button" style="background: #6c757d;">Annulla</a>
                <button type="submit" style="background: #dc3545;">Sì, Elimina il mio Account</button>
            </form>
        </div>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
