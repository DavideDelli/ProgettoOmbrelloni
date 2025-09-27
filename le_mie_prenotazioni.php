<?php
session_start();

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: src/auth/accesso.php');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'src/db_connection.php';

$codice_cliente = $_SESSION['codice_cliente'];
$prenotazioni = [];
$errore = '';

$ordinamento_selezionato = $_GET['ordina_per'] ?? 'data_desc';
$allowed_sorts = [
    'data_desc'   => 'ORDER BY data_inizio DESC',
    'data_asc'    => 'ORDER BY data_inizio ASC',
    'prezzo_desc' => 'ORDER BY c.importo DESC',
    'prezzo_asc'  => 'ORDER BY c.importo ASC',
];
$orderByClause = $allowed_sorts[$ordinamento_selezionato] ?? $allowed_sorts['data_desc'];

try {
    $sql = "
        SELECT 
            c.numProgr, c.importo, MIN(gd.data) AS data_inizio, MAX(gd.data) AS data_fine,
            o.settore, o.numFila, o.numPostoFila, tip.nome AS nome_tipologia
        FROM contratto c
        JOIN giornodisponibilita gd ON c.numProgr = gd.numProgrContratto
        JOIN ombrellone o ON gd.idOmbrellone = o.id
        JOIN tipologia tip ON o.codTipologia = tip.codice
        WHERE c.codiceCliente = :codice_cliente
        GROUP BY c.numProgr
        $orderByClause
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codice_cliente' => $codice_cliente]);
    $prenotazioni = $stmt->fetchAll();
} catch (PDOException $e) {
    $errore = "Errore nel recupero delle prenotazioni: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le mie Prenotazioni - Lido Codici Sballati</title>
    <link rel="stylesheet" href="assets/css/stile.css?v=<?= filemtime('assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Le mie Prenotazioni</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php" class="active">Le mie Prenotazioni</a>
        <a href="profilo.php">Il mio profilo (<?= htmlspecialchars($_SESSION['nome_cliente']) ?>)</a>
    </nav>
    <main>
        <?php if (isset($_GET['status'])):
            $status_class = ($_GET['status'] === 'error') ? 'messaggio errore' : 'messaggio-conferma';
            $status_message = '';
            if ($_GET['status'] === 'deleted') { $status_message = 'Prenotazione cancellata con successo.'; }
            if ($_GET['status'] === 'updated') { $status_message = 'Prenotazione modificata con successo.'; }
            if ($_GET['status'] === 'error') { $status_message = 'Si è verificato un errore. Riprova.'; }
            if ($status_message) {
                echo "<div class=\"$status_class glass-panel\" style='max-width: 800px; margin: 0 auto 2rem auto;'><p>$status_message</p></div>";
            }
        endif; ?>

        <?php if (!empty($errore)):
            echo "<div class='messaggio errore glass-panel' style='max-width: 800px; margin: 0 auto 2rem auto;'><p>" . htmlspecialchars($errore) . "</p></div>";
        elseif (empty($prenotazioni)):
            echo "<div class='glass-panel' style='max-width: 800px; margin: auto; text-align: center;'><p>Non hai ancora nessuna prenotazione attiva.</p><a href='mappa.php' class='button' style='text-decoration:none; margin-top: 1rem;'>Prenota ora il tuo ombrellone!</a></div>";
        else: ?>
            <div class="filtro-ordinamento glass-panel" style="max-width: 800px; margin: 2rem auto; padding: 15px; text-align: right;">
                <form method="GET" action="le_mie_prenotazioni.php" style="display: inline-flex; gap: 10px; align-items: center; margin: 0;">
                    <label for="ordina_per">Ordina per:</label>
                    <select name="ordina_per" id="ordina_per" onchange="this.form.submit()">
                        <option value="data_desc" <?= ($ordinamento_selezionato === 'data_desc') ? 'selected' : '' ?>>Data (più recenti)</option>
                        <option value="data_asc" <?= ($ordinamento_selezionato === 'data_asc') ? 'selected' : '' ?>>Data (meno recenti)</option>
                        <option value="prezzo_desc" <?= ($ordinamento_selezionato === 'prezzo_desc') ? 'selected' : '' ?>>Prezzo (più caro)</option>
                        <option value="prezzo_asc" <?= ($ordinamento_selezionato === 'prezzo_asc') ? 'selected' : '' ?>>Prezzo (più economico)</option>
                    </select>
                </form>
            </div>
            <?php foreach ($prenotazioni as $p):
                $data_oggi = date("Y-m-d");
                $puo_modificare_cancellare = strtotime($p['data_inizio']) > strtotime($data_oggi);
            ?>
                <div class="prenotazione-card glass-panel" style="max-width: 800px; margin: 0 auto 1.5rem auto;">
                    <h3><?= ($p['data_inizio'] !== $p['data_fine']) ? 'Abbonamento Settimanale' : 'Prenotazione Giornaliera'; ?></h3>
                    <div class="prenotazione-details">
                        <p><strong>N° Contratto:</strong> <?= htmlspecialchars($p['numProgr']) ?></p>
                        <p><strong>Ombrellone:</strong> Settore <?= htmlspecialchars($p['settore']) ?>, Fila <?= htmlspecialchars($p['numFila']) ?>, Posto <?= htmlspecialchars($p['numPostoFila']) ?> (<?= htmlspecialchars($p['nome_tipologia']) ?>)</p>
                        <p><strong>Periodo:</strong> <?php if ($p['data_inizio'] !== $p['data_fine']): ?>dal <?= htmlspecialchars(date("d/m/Y", strtotime($p['data_inizio']))) ?> al <?= htmlspecialchars(date("d/m/Y", strtotime($p['data_fine']))) ?><?php else: ?>il <?= htmlspecialchars(date("d/m/Y", strtotime($p['data_inizio']))) ?><?php endif; ?></p>
                        <p><strong>Importo:</strong> €<?= htmlspecialchars(number_format($p['importo'], 2, ',', '.')) ?></p>
                    </div>
                    <div class="actions" style="margin-top: 15px; text-align: right;">
                        <?php if ($puo_modificare_cancellare): ?>
                            <a href="src/booking/modifica_prenotazione.php?id=<?= $p['numProgr'] ?>" class="button-link" style="background: #007bff;">Modifica</a>
                            <a href="src/booking/elimina_prenotazione.php?id=<?= $p['numProgr'] ?>" class="button-link" style="background: #dc3545;">Cancella</a>
                        <?php else: ?>
                            <span class="button-link" style="background: grey; cursor: not-allowed;" title="Non puoi modificare o cancellare prenotazioni in corso o passate.">Modifica</span>
                            <span class="button-link" style="background: grey; cursor: not-allowed;" title="Non puoi modificare o cancellare prenotazioni in corso o passate.">Cancella</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
