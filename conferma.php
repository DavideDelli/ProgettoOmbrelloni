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
    $codice_cliente = $_SESSION['codice_cliente'];

    try {
        // --- RICALCOLO SICURO DEL PREZZO SUL SERVER ---
        // 1. Recupera il tipo di ombrellone (e quindi il suo costo) direttamente dal database
        $stmt_tipo = $pdo->prepare("SELECT codTipologia FROM ombrellone WHERE id = :id");
        $stmt_tipo->execute(['id' => $id_ombrellone]);
        $ombrellone_db = $stmt_tipo->fetch();

        if (!$ombrellone_db) {
            throw new Exception("Ombrellone non trovato.");
        }

        // 2. Determina il prezzo giornaliero
        $prezzo_giornaliero_base = ($ombrellone_db['codTipologia'] === 'VIP') ? 50 : 30;
        
        $importo_finale = 0;

        // 3. Calcola il prezzo totale in base al tipo di prenotazione e alle opzioni scelte
        if ($tipo_prenotazione === 'settimanale') {
            // CALCOLO PER ABBONAMENTO: (prezzo giornaliero * 7) + extra
            $importo_finale = $prezzo_giornaliero_base * 7;
            if (isset($_POST['abbonamento_extra'])) {
                switch ($_POST['abbonamento_extra']) {
                    case 'premium':
                        $importo_finale += 20; // Aggiunge il costo del pacchetto Premium
                        break;
                    case 'vip':
                        $importo_finale += 40; // Aggiunge il costo del pacchetto VIP
                        break;
                }
            }
        } else { // CALCOLO PER GIORNALIERO
            $importo_finale = $prezzo_giornaliero_base;
            if (isset($_POST['ombrelloni_extra'])) {
                $importo_finale += intval($_POST['ombrelloni_extra']) * 25;
            }
            if (!empty($_POST['servizi_extra']) && is_array($_POST['servizi_extra'])) {
                foreach ($_POST['servizi_extra'] as $servizio) {
                    switch ($servizio) {
                        case 'aperitivo': $importo_finale += 10; break;
                        case 'pedalo': $importo_finale += 20; break;
                        case 'asciugamani': $importo_finale += 5; break;
                    }
                }
            }
        }

        // --- LOGICA DI PRENOTAZIONE CON TRANSAZIONE ---
        $pdo->beginTransaction();
        
        // Inserisce il contratto con l'importo finale calcolato qui sul server
        $sql_contratto = "INSERT INTO contratto (data, importo, codiceCliente) VALUES (CURDATE(), :importo, :codice)";
        $stmt_contratto = $pdo->prepare($sql_contratto);
        $stmt_contratto->execute(['importo' => $importo_finale, 'codice' => $codice_cliente]);
        $nuovo_contratto_id = $pdo->lastInsertId();

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
                throw new Exception("L'ombrellone non è più disponibile per il giorno " . date("d/m/Y", strtotime($data_corrente)) . ". La prenotazione è stata annullata.");
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