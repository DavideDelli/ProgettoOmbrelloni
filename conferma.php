<?php
require_once 'db_connection.php';

$messaggio = '';
$successo = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id_ombrellone'], $_POST['data_prenotazione'], $_POST['codice_cliente'])) {
        $id_ombrellone = $_POST['id_ombrellone'];
        $data_prenotazione = $_POST['data_prenotazione'];
        $codice_cliente = $_POST['codice_cliente'];

        // Iniziamo una transazione: o tutte le query vanno a buon fine, o nessuna viene eseguita
        $pdo->beginTransaction();
        try {
            // 1. Controlliamo se il cliente esiste
            $stmt_cliente = $pdo->prepare("SELECT codice FROM Cliente WHERE codice = :codice");
            $stmt_cliente->execute(['codice' => $codice_cliente]);
            if ($stmt_cliente->rowCount() == 0) {
                throw new Exception("Codice Cliente non valido.");
            }

            // 2. Controlliamo se l'ombrellone è ancora libero per quella data (sicurezza aggiuntiva)
            $stmt_check = $pdo->prepare("SELECT numProgrContratto FROM GiornoDisponibilita WHERE idOmbrellone = :id AND data = :data");
            $stmt_check->execute(['id' => $id_ombrellone, 'data' => $data_prenotazione]);
            $risultato_check = $stmt_check->fetch();
            if ($risultato_check && $risultato_check['numProgrContratto'] !== null) {
                throw new Exception("Spiacenti, questo ombrellone è appena stato prenotato da un altro utente.");
            }

            // 3. Calcoliamo il prezzo (logica di esempio, puoi renderla più complessa)
            $stmt_tipo = $pdo->prepare("SELECT codTipologia FROM Ombrellone WHERE id = :id");
            $stmt_tipo->execute(['id' => $id_ombrellone]);
            $tipologia = $stmt_tipo->fetchColumn();
            $prezzo = ($tipologia == 'VIP') ? 50.00 : 30.00; // Prezzo fisso giornaliero

            // 4. Inseriamo il nuovo contratto
            $sql_contratto = "INSERT INTO Contratto (data, importo, codiceCliente) VALUES (CURDATE(), :importo, :codice)";
            $stmt_contratto = $pdo->prepare($sql_contratto);
            $stmt_contratto->execute(['importo' => $prezzo, 'codice' => $codice_cliente]);
            $nuovo_contratto_id = $pdo->lastInsertId();

            // 5. Aggiorniamo la disponibilità dell'ombrellone
            $sql_aggiorna = "UPDATE GiornoDisponibilita SET numProgrContratto = :id_contratto WHERE idOmbrellone = :id_ombrellone AND data = :data";
            $stmt_aggiorna = $pdo->prepare($sql_aggiorna);
            $stmt_aggiorna->execute([
                'id_contratto' => $nuovo_contratto_id,
                'id_ombrellone' => $id_ombrellone,
                'data' => $data_prenotazione
            ]);

            // Se tutte le query sono andate a buon fine, confermiamo le modifiche
            $pdo->commit();
            $messaggio = "Prenotazione confermata con successo! Il tuo numero di contratto è {$nuovo_contratto_id}.";
            $successo = true;

        } catch (Exception $e) {
            // Se qualcosa è andato storto, annulliamo tutte le modifiche
            $pdo->rollBack();
            $messaggio = "Errore durante la prenotazione: " . $e->getMessage();
        }
    } else {
        $messaggio = "Dati del form mancanti.";
    }
} else {
    header("Location: index.php"); // Reindirizza alla home se si accede direttamente
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esito Prenotazione</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
    <div class="container">
        <header>Esito Prenotazione</header>
        <main>
            <div class="messaggio <?= $successo ? 'successo' : 'errore' ?>">
                <h2><?= $successo ? 'Congratulazioni!' : 'Attenzione!' ?></h2>
                <p><?= htmlspecialchars($messaggio) ?></p>
                <a href="index.php" class="button-link">Torna alla Home Page</a>
            </div>
        </main>
        <footer>© 2025 - Università degli Studi di Bergamo - Tutti i diritti riservati</footer>
    </div>
</body>
</html>
