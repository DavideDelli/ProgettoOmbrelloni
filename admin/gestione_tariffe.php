<?php
$page_title = 'Gestione Tariffe';
require_once 'partials/header.php';

$messaggio = '';
$errore = '';

// Logica di aggiornamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prezzi'])) {
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE tariffa SET prezzo = :prezzo WHERE codice = :codice";
        $stmt = $pdo->prepare($sql);

        foreach ($_POST['prezzi'] as $codice => $prezzo) {
            $prezzo_float = filter_var($prezzo, FILTER_VALIDATE_FLOAT);
            if ($prezzo_float === false || $prezzo_float < 0) {
                throw new Exception("Il prezzo per la tariffa '{$codice}' non è valido.");
            }
            $stmt->execute(['prezzo' => $prezzo_float, 'codice' => $codice]);
        }

        $pdo->commit();
        $messaggio = "Tariffe aggiornate con successo!";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $errore = "Errore durante l'aggiornamento: " . $e->getMessage();
    }
}

// Recupero tariffe
try {
    $tariffe = $pdo->query("SELECT * FROM tariffa ORDER BY tipo, codice")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errore = "Impossibile caricare le tariffe: " . $e->getMessage();
    $tariffe = [];
}
?>

<h1>Gestione Tariffe</h1>
<p>Modifica i prezzi per ogni tipo di tariffa. I cambiamenti saranno applicati a tutte le nuove prenotazioni.</p>

<?php if ($messaggio): ?><div class="messaggio successo" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724;"><p><?= htmlspecialchars($messaggio) ?></p></div><?php endif; ?>
<?php if ($errore): ?><div class="messaggio errore"><p><?= htmlspecialchars($errore) ?></p></div><?php endif; ?>

<form method="POST" action="gestione_tariffe.php">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Codice</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Prezzo (€)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tariffe as $tariffa): ?>
            <tr>
                <td><?= htmlspecialchars($tariffa['codice']) ?></td>
                <td><?= htmlspecialchars(getNomeTariffa($tariffa['codice'])) ?></td>
                <td><?= htmlspecialchars($tariffa['tipo']) ?></td>
                <td>
                    <input type="number" step="0.01" min="0" name="prezzi[<?= htmlspecialchars($tariffa['codice']) ?>]" value="<?= htmlspecialchars(number_format($tariffa['prezzo'], 2, '.', '')) ?>" required>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="text-align: right; margin-top: 20px;">
        <button type="submit">Salva Modifiche</button>
    </div>
</form>

<?php require_once 'partials/footer.php'; ?>

