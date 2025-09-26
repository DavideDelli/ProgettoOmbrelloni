<?php
session_start();

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: accesso.php');
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connection.php';

$codice_cliente = $_SESSION['codice_cliente'];
$prenotazioni = [];
$errore = '';

// --- LOGICA DI ORDINAMENTO ---
$ordinamento_selezionato = $_GET['ordina_per'] ?? 'data_desc';

// Whitelist per la sicurezza
$allowed_sorts = [
    'data_desc'   => 'ORDER BY data_inizio DESC',
    'data_asc'    => 'ORDER BY data_inizio ASC',
    'prezzo_desc' => 'ORDER BY c.importo DESC',
    'prezzo_asc'  => 'ORDER BY c.importo ASC',
];

// Se l'opzione non è valida, usa il default
$orderByClause = $allowed_sorts[$ordinamento_selezionato] ?? $allowed_sorts['data_desc'];



try {
    // Query per recuperare le prenotazioni dell'utente.
    // Raggruppiamo per contratto per gestire correttamente sia le prenotazioni singole che settimanali.
    $sql = "
        SELECT 
            c.numProgr,
            c.importo,
            MIN(gd.data) AS data_inizio,
            MAX(gd.data) AS data_fine,
            o.settore,
            o.numFila,
            o.numPostoFila,
            tip.nome AS nome_tipologia
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
    <title>Le mie Prenotazioni - Lido Paradiso</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
    <style>
        .prenotazione-card { background: #fffaf5; border: 1px solid #d3a27f; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: left; max-width: 800px; margin-left: auto; margin-right: auto; }
        .prenotazione-card h3 { margin-top: 0; border-bottom: 2px solid #c08457; padding-bottom: 10px; margin-bottom: 15px; }
        .prenotazione-details p { margin: 5px 0; }
        .actions { margin-top: 15px; text-align: right; }
        .actions a, .actions .btn-disabled { text-decoration: none; padding: 8px 15px; border-radius: 5px; color: white; margin-left: 10px; display: inline-block; }
        .btn-delete { background-color: #dc3545; }
        .btn-delete:hover { background-color: #c82333; }
        .btn-edit { background-color: #007bff; }
        .btn-edit:hover { background-color: #0069d9; }
        .btn-disabled { background-color: grey; cursor: not-allowed; }

        .filtro-ordinamento { background: #e6cbb4; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: right; max-width: 800px; margin-left: auto; margin-right: auto; }
        .filtro-ordinamento form { display: inline-flex; gap: 10px; align-items: center; margin: 0; }
        .filtro-ordinamento label { font-weight: bold; color: #3b2a1a; }
        .filtro-ordinamento select { font-size: 1em; padding: 8px; border-radius: 6px; border: 1px solid #ac6730; background-color: #fffaf5; cursor: pointer; }
        .filtro-ordinamento button { font-size: 0.9em; padding: 8px 16px; border-radius: 20px; }
    </style>
</head>
<body>
<div class="container">
    <header>Le mie Prenotazioni</header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php" class="active">Le mie Prenotazioni</a>
        <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['nome_cliente']) ?>)</a>
    </nav>
    <main>
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'deleted'): ?><div class="messaggio successo"><p>Prenotazione cancellata con successo.</p></div><?php endif; ?>
            <?php if ($_GET['status'] === 'updated'): ?><div class="messaggio successo"><p>Prenotazione modificata con successo.</p></div><?php endif; ?>
            <?php if ($_GET['status'] === 'error'): ?><div class="messaggio errore"><p>Si è verificato un errore. Riprova.</p></div><?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($errore)): ?>
            <div class="messaggio errore"><p><?= htmlspecialchars($errore) ?></p></div>
        <?php elseif (empty($prenotazioni)): ?>
            <p>Non hai ancora nessuna prenotazione attiva.</p>
            <a href="mappa.php" class="button" style="text-decoration:none;">Prenota ora il tuo ombrellone!</a>
        <?php else: ?>
            <div class="filtro-ordinamento">
                <form method="GET" action="le_mie_prenotazioni.php">
                    <label for="ordina_per">Ordina per:</label>
                    <select name="ordina_per" id="ordina_per" onchange="this.form.submit()">
                        <option value="data_desc" <?= ($ordinamento_selezionato === 'data_desc') ? 'selected' : '' ?>>Data (più recenti)</option>
                        <option value="data_asc" <?= ($ordinamento_selezionato === 'data_asc') ? 'selected' : '' ?>>Data (meno recenti)</option>
                        <option value="prezzo_desc" <?= ($ordinamento_selezionato === 'prezzo_desc') ? 'selected' : '' ?>>Prezzo (più caro)</option>
                        <option value="prezzo_asc" <?= ($ordinamento_selezionato === 'prezzo_asc') ? 'selected' : '' ?>>Prezzo (più economico)</option>
                    </select>
                    <noscript><button type="submit">Ordina</button></noscript>
                </form>
            </div>
            <?php foreach ($prenotazioni as $p): ?>
                <?php
                    $data_oggi = date("Y-m-d");
                    $puo_modificare_cancellare = strtotime($p['data_inizio']) > strtotime($data_oggi);
                ?>
                <div class="prenotazione-card">
                    <h3><?= ($p['data_inizio'] !== $p['data_fine']) ? 'Abbonamento Settimanale' : 'Prenotazione Giornaliera'; ?></h3>
                    <div class="prenotazione-details">
                        <p><strong>N° Contratto:</strong> <?= htmlspecialchars($p['numProgr']) ?></p>
                        <p><strong>Ombrellone:</strong> Settore <?= htmlspecialchars($p['settore']) ?>, Fila <?= htmlspecialchars($p['numFila']) ?>, Posto <?= htmlspecialchars($p['numPostoFila']) ?> (<?= htmlspecialchars($p['nome_tipologia']) ?>)</p>
                        <p><strong>Periodo:</strong> <?php if ($p['data_inizio'] !== $p['data_fine']): ?>dal <?= htmlspecialchars(date("d/m/Y", strtotime($p['data_inizio']))) ?> al <?= htmlspecialchars(date("d/m/Y", strtotime($p['data_fine']))) ?><?php else: ?>il <?= htmlspecialchars(date("d/m/Y", strtotime($p['data_inizio']))) ?><?php endif; ?></p>
                        <p><strong>Importo:</strong> €<?= htmlspecialchars(number_format($p['importo'], 2, ',', '.')) ?></p>
                    </div>
                    <div class="actions">
                        <?php if ($puo_modificare_cancellare): ?>
                            <a href="modifica_prenotazione.php?id=<?= $p['numProgr'] ?>" class="btn-edit">Modifica</a>
                            <a href="elimina_prenotazione.php?id=<?= $p['numProgr'] ?>" class="btn-delete">Cancella</a>
                            <?php else: ?>
                            <span class="btn-disabled" title="Non puoi modificare o cancellare prenotazioni in corso o passate.">Modifica</span>
                            <span class="btn-disabled" title="Non puoi modificare o cancellare prenotazioni in corso o passate.">Cancella</span>
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

