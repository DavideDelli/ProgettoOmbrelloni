<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../db_connection.php';

if (!isset($_SESSION['codice_cliente'])) {
    header('Location: ../auth/accesso.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../le_mie_prenotazioni.php?status=error');
    exit();
}

$id_contratto = $_GET['id'];
$codice_cliente = $_SESSION['codice_cliente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_elimina'])) {
    try {
        $pdo->beginTransaction();

        $sql_check_owner = "SELECT COUNT(*) FROM contratto WHERE numProgr = :id_contratto AND codiceCliente = :codice_cliente";
        $stmt_check = $pdo->prepare($sql_check_owner);
        $stmt_check->execute(['id_contratto' => $id_contratto, 'codice_cliente' => $codice_cliente]);
        
        if ($stmt_check->fetchColumn() == 0) {
            throw new Exception("Non sei autorizzato a cancellare questa prenotazione.");
        }

        $sql_update = "UPDATE giornodisponibilita SET numProgrContratto = NULL WHERE numProgrContratto = :id_contratto";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute(['id_contratto' => $id_contratto]);

        $sql_delete = "DELETE FROM contratto WHERE numProgr = :id_contratto";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute(['id_contratto' => $id_contratto]);

        $pdo->commit();
        header('Location: ../../le_mie_prenotazioni.php?status=deleted');
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: ../../le_mie_prenotazioni.php?status=error');
        exit();
    }
}

try {
    $sql = "SELECT c.numProgr, MIN(gd.data) AS data_inizio, MAX(gd.data) AS data_fine, o.settore, o.numFila, o.numPostoFila, t.descrizione AS nome_tariffa FROM contratto c JOIN giornodisponibilita gd ON c.numProgr = gd.numProgrContratto JOIN ombrellone o ON gd.idOmbrellone = o.id LEFT JOIN tariffa t ON c.codTariffa = t.codice WHERE c.numProgr = :id_contratto AND c.codiceCliente = :codice_cliente GROUP BY c.numProgr";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_contratto' => $id_contratto, 'codice_cliente' => $codice_cliente]);
    $prenotazione = $stmt->fetch();

    if (!$prenotazione) {
        header('Location: ../../le_mie_prenotazioni.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: ../../le_mie_prenotazioni.php?status=error');
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Conferma Cancellazione</title>
    <link rel="stylesheet" href="../../assets/css/stile.css?v=<?= filemtime('../../assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Conferma Cancellazione</header>
    <main style="text-align: center;">
    <div class="messaggio-conferma glass-panel">
            <h2>Sei sicuro?</h2>
            <p>Stai per cancellare la seguente prenotazione. L'azione è irreversibile.</p>
            <div class="riepilogo-box" style="background: rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.2); box-shadow: none; text-align:left; margin-top: 20px;">
                <p><strong>N° Contratto:</strong> <?= htmlspecialchars($prenotazione['numProgr']) ?></p>
                <p><strong>Ombrellone:</strong> Settore <?= htmlspecialchars($prenotazione['settore']) ?>, Fila <?= htmlspecialchars($prenotazione['numFila']) ?>, Posto <?= htmlspecialchars($prenotazione['numPostoFila']) ?></p>
                <p><strong>Tariffa:</strong> <?= htmlspecialchars($prenotazione['nome_tariffa'] ?? 'N/D') ?></p>
                <p>
                    <strong>Periodo:</strong> 
                    <?php if ($prenotazione['data_inizio'] !== $prenotazione['data_fine']): ?>
                        dal <?= htmlspecialchars(date("d/m/Y", strtotime($prenotazione['data_inizio']))) ?> al <?= htmlspecialchars(date("d/m/Y", strtotime($prenotazione['data_fine']))) ?>
                    <?php else: ?>
                        il <?= htmlspecialchars(date("d/m/Y", strtotime($prenotazione['data_inizio']))) ?>
                    <?php endif; ?>
                </p>
            </div>
                <form method="POST" action="elimina_prenotazione.php?id=<?= $id_contratto ?>" style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
                <input type="hidden" name="conferma_elimina" value="1">
                <a href="../../le_mie_prenotazioni.php" class="button-link" style="background: rgba(0,0,0,0.2) !important;">Annulla</a>
                <button type="submit" style="background: #B22222 !important;">Sì, Cancella Prenotazione</button>
            </form>
        </div>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
