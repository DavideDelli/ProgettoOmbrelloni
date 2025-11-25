<?php
$page_title = 'Gestione Tariffe';
require_once 'partials/header.php';

$messaggio = $_SESSION['messaggio_tariffa'] ?? '';
if ($messaggio) {
    unset($_SESSION['messaggio_tariffa']);
}

$errore = '';

// Recupero tariffe con le tipologie associate
try {
    $sql = "
        SELECT 
            t.*, 
            GROUP_CONCAT(tt.codTipologia ORDER BY tt.codTipologia) AS tipologie
        FROM tariffa t
        LEFT JOIN tipologiatariffa tt ON t.codice = tt.codTariffa
        GROUP BY t.codice
        ORDER BY t.tipo, t.codice
    ";
    $tariffe = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errore = "Impossibile caricare le tariffe: " . $e->getMessage();
    $tariffe = [];
}
?>

<h1>Gestione Tariffe</h1>
<p>Da questa pagina puoi creare, modificare ed eliminare le tariffe e associarle alle tipologie di ombrellone.</p>

<?php if ($messaggio): ?><div class="messaggio successo glass-panel"><p><?= htmlspecialchars($messaggio) ?></p></div><?php endif; ?>
<?php if ($errore): ?><div class="messaggio errore glass-panel"><p><?= htmlspecialchars($errore) ?></p></div><?php endif; ?>

<div style="text-align: right; margin-bottom: 20px;" class="glass-panel">
    <a href="aggiungi_tariffa.php" class="button">Aggiungi Nuova Tariffa</a>
</div>

<table class="admin-table glass-panel">
    <thead>
        <tr>
            <th>Codice</th>
            <th>Prezzo</th>
            <th>Tipo</th>
            <th>Validità</th>
            <th>Tipologie Associate</th>
            <th style="width: 200px;">Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($tariffe)): ?>
            <tr>
                <td colspan="6" style="text-align: center;">Nessuna tariffa trovata.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($tariffe as $tariffa): ?>
            <tr>
                <td><strong><?= htmlspecialchars($tariffa['codice']) ?></strong><br><small><?= htmlspecialchars(getNomeTariffa($tariffa['codice'])) ?></small></td>
                <td>€ <?= htmlspecialchars(number_format($tariffa['prezzo'], 2, ',', '.')) ?></td>
                <td><?= htmlspecialchars($tariffa['tipo']) ?></td>
                <td>Dal <?= htmlspecialchars(date("d/m/Y", strtotime($tariffa['dataInizio']))) ?><br>al <?= htmlspecialchars(date("d/m/Y", strtotime($tariffa['dataFine']))) ?></td>
                <td><?= htmlspecialchars(str_replace(',', ', ', $tariffa['tipologie'] ?? 'Nessuna')) ?></td>
                <td>
                    <div style="display: flex; gap: 5px; justify-content: center;">
                        <a href="modifica_tariffa.php?codice=<?= htmlspecialchars($tariffa['codice']) ?>" class="button-link" style="background-color: #007bff;">Modifica</a>
                        <a href="elimina_tariffa.php?codice=<?= htmlspecialchars($tariffa['codice']) ?>" class="button-link" style="background-color: #dc3545;" onclick="return confirm('Sei sicuro di voler eliminare questa tariffa? L\'azione è irreversibile.');">Elimina</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once 'partials/footer.php'; ?>
