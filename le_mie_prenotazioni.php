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
    $errore = "Errore database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Le mie Prenotazioni</title>
    <link rel="stylesheet" href="assets/css/stile.css?v=<?= filemtime('assets/css/stile.css') ?>">
</head>
<body>
<div class="container">
    <header><h1>Le mie Prenotazioni</h1></header>
    <nav>
        <a href="index.php">Home</a>
        <a href="mappa.php">Mappa Spiaggia</a>
        <a href="le_mie_prenotazioni.php" class="active">Prenotazioni</a>
        <a href="profilo.php">Profilo</a>
    </nav>
    <main>
        <div class="glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 15px;">
                <h2 style="margin: 0; border: none; padding: 0;">I tuoi Ordini</h2>
                
                <?php if (!empty($prenotazioni)): ?>
                <form method="GET" action="le_mie_prenotazioni.php" style="display: flex; gap: 10px; align-items: center;">
                    <label for="ordina_per" style="margin:0;">Ordina:</label>
                    <select name="ordina_per" id="ordina_per" onchange="this.form.submit()" style="width: auto; padding: 8px 15px;">
                        <option value="data_desc" <?= ($ordinamento_selezionato === 'data_desc') ? 'selected' : '' ?>>Data (Recenti)</option>
                        <option value="data_asc" <?= ($ordinamento_selezionato === 'data_asc') ? 'selected' : '' ?>>Data (Vecchi)</option>
                        <option value="prezzo_desc" <?= ($ordinamento_selezionato === 'prezzo_desc') ? 'selected' : '' ?>>Prezzo (Alto)</option>
                        <option value="prezzo_asc" <?= ($ordinamento_selezionato === 'prezzo_asc') ? 'selected' : '' ?>>Prezzo (Basso)</option>
                    </select>
                </form>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['status'])): 
                 $msg = '';
                 if ($_GET['status'] === 'deleted') $msg = 'Prenotazione cancellata.';
                 if ($_GET['status'] === 'updated') $msg = 'Prenotazione aggiornata.';
                 if ($msg) echo "<div class='messaggio-conferma' style='margin-bottom: 20px;'>$msg</div>";
            endif; ?>

            <?php if (!empty($errore)): ?>
                <div class="messaggio errore"><?= htmlspecialchars($errore) ?></div>
            <?php elseif (empty($prenotazioni)): ?>
                <div style="text-align: center; padding: 50px;">
                    <p style="font-size: 1.2em; color: #5d4037;">Non hai ancora prenotazioni attive.</p>
                    <a href="mappa.php" class="button" style="margin-top: 20px;">Prenota Ombrellone</a>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                <?php foreach ($prenotazioni as $p):
                    $data_oggi = date("Y-m-d");
                    $puo_modificare = strtotime($p['data_inizio']) > strtotime($data_oggi);
                    $tipo = ($p['data_inizio'] !== $p['data_fine']) ? 'SETTIMANALE' : 'GIORNALIERO';
                ?>
                    <div class="prenotazione-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <span style="background: rgba(141, 110, 99, 0.2); color: #5d4037; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: bold; letter-spacing: 1px; border: 1px solid rgba(141, 110, 99, 0.3);"><?= $tipo ?></span>
                            <span style="font-weight: bold; color: #8d6e63;">#<?= $p['numProgr'] ?></span>
                        </div>
                        
                        <div class="prenotazione-details">
                            <h3 style="margin: 0 0 10px 0; font-size: 1.3rem; border: none; padding: 0;">Settore <?= $p['settore'] ?> <span style="font-weight: 300; opacity: 0.7;">|</span> Posto <?= $p['numPostoFila'] ?></h3>
                            <p><strong>Fila:</strong> <?= $p['numFila'] ?> (<?= $p['nome_tipologia'] ?>)</p>
                            <p><strong>Quando:</strong> <?= date("d/m", strtotime($p['data_inizio'])) ?> 
                                <?php if($tipo == 'SETTIMANALE') echo " - " . date("d/m", strtotime($p['data_fine'])); ?>
                            </p>
                            <p style="font-size: 1.5em; margin-top: 15px; color: #2e7d32; font-weight: 900;">€<?= number_format($p['importo'], 2, ',', '.') ?></p>
                        </div>

                        <div style="margin-top: 25px; display: flex; gap: 10px;">
                            <?php if ($puo_modificare): ?>
                                <a href="src/booking/modifica_prenotazione.php?id=<?= $p['numProgr'] ?>" class="button" style="background: linear-gradient(135deg, #42a5f5, #1e88e5); flex: 1; text-align: center; padding: 10px; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(30,136,229,0.3);">Modifica</a>
                                <a href="src/booking/elimina_prenotazione.php?id=<?= $p['numProgr'] ?>" class="button" style="background: linear-gradient(135deg, #ef5350, #c62828); flex: 1; text-align: center; padding: 10px; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(198,40,40,0.3);">Cancella</a>
                            <?php else: ?>
                                <button disabled style="background: #e0e0e0; color: #999; width: 100%; cursor: not-allowed; box-shadow: none;">Conclusa</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer>© 2025 - Lido Codici Sballati</footer>
</div>
</body>
</html>