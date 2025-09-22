<?php
// Abilita la visualizzazione degli errori per il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

$messaggio = '';
$successo = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera i dati dal form
    if (isset($_POST['id_ombrellone'], $_POST['data_prenotazione'], $_POST['nome'], $_POST['cognome'], $_POST['data_nascita'], $_POST['importo'])) {
        
        $id_ombrellone = $_POST['id_ombrellone'];
        $data_prenotazione = $_POST['data_prenotazione'];
        $nome_cliente = trim($_POST['nome']);
        $cognome_cliente = trim($_POST['cognome']);
        $data_nascita_cliente = $_POST['data_nascita'];
        $importo_totale = $_POST['importo'];

        // Validazione base
        if (empty($nome_cliente) || empty($cognome_cliente) || empty($data_nascita_cliente)) {
            $messaggio = "Dati del form non validi. Controlla nome, cognome e data di nascita.";
        } else {
            $pdo->beginTransaction();
            try {
                // 1. Controlla se l'ombrellone è ancora libero
                $stmt_check = $pdo->prepare("SELECT numProgrContratto FROM giornodisponibilita WHERE idOmbrellone = :id AND data = :data");
                $stmt_check->execute(['id' => $id_ombrellone, 'data' => $data_prenotazione]);
                if ($stmt_check->fetch()['numProgrContratto'] !== null) {
                    throw new Exception("Spiacenti, questo ombrellone è appena stato prenotato da un altro utente.");
                }

                // 2. Cerca il cliente. Se non esiste, crealo.
                $stmt_find_cliente = $pdo->prepare("SELECT codice FROM cliente WHERE nome = :nome AND cognome = :cognome AND dataNascita = :data_nascita");
                $stmt_find_cliente->execute([
                    'nome' => $nome_cliente,
                    'cognome' => $cognome_cliente,
                    'data_nascita' => $data_nascita_cliente
                ]);
                $codice_cliente = $stmt_find_cliente->fetchColumn();

                if (!$codice_cliente) {
                    // Cliente non trovato, creiamo uno nuovo
                    $codice_cliente = 'CLIENTE' . strtoupper(uniqid());
                    $sql_cliente = "INSERT INTO cliente (codice, nome, cognome, dataNascita) VALUES (:codice, :nome, :cognome, :data_nascita)";
                    $stmt_cliente = $pdo->prepare($sql_cliente);
                    $stmt_cliente->execute([
                        'codice' => $codice_cliente,
                        'nome' => $nome_cliente,
                        'cognome' => $cognome_cliente,
                        'data_nascita' => $data_nascita_cliente
                    ]);
                }

                // 3. Inserisci il nuovo contratto
                $sql_contratto = "INSERT INTO contratto (data, importo, codiceCliente) VALUES (CURDATE(), :importo, :codice)";
                $stmt_contratto = $pdo->prepare($sql_contratto);
                $stmt_contratto->execute(['importo' => $importo_totale, 'codice' => $codice_cliente]);
                $nuovo_contratto_id = $pdo->lastInsertId();

                // 4. Aggiorna la disponibilità dell'ombrellone
                $sql_aggiorna = "UPDATE giornodisponibilita SET numProgrContratto = :id_contratto WHERE idOmbrellone = :id_ombrellone AND data = :data";
                $stmt_aggiorna = $pdo->prepare($sql_aggiorna);
                $stmt_aggiorna->execute([
                    'id_contratto' => $nuovo_contratto_id,
                    'id_ombrellone' => $id_ombrellone,
                    'data' => $data_prenotazione
                ]);

                // Conferma la transazione
                $pdo->commit();
                $messaggio = "Prenotazione confermata con successo! Il tuo numero di contratto è {$nuovo_contratto_id}.";
                $successo = true;

            } catch (Exception $e) {
                $pdo->rollBack();
                $messaggio = "Errore durante la prenotazione: " . $e->getMessage();
            }
        }
    } else {
        $messaggio = "Dati del form mancanti.";
    }
} else {
    header("Location: index.php");
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
<body class="page-prenota">
    <div class="booking-container">
         <div class="booking-header">
            <a href="index.php" class="back-link">← Lido Paradiso</a>
        </div>
        <main style="padding-top: 50px;">
             <div class="messaggio <?= $successo ? 'successo' : 'errore' ?>">
                <h2><?= $successo ? 'Congratulazioni!' : 'Attenzione!' ?></h2>
                <p><?= htmlspecialchars($messaggio) ?></p>
                <a href="index.php" class="payment-button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Torna alla Home Page</a>
            </div>
        </main>
    </div>
</body>
</html>