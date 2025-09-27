<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: recupero_codice.php");
    exit();
}

$nome = trim($_POST['nome']);
$cognome = trim($_POST['cognome']);
$dataNascita = $_POST['dataNascita'];

if (empty($nome) || empty($cognome) || empty($dataNascita)) {
    header("Location: recupero_codice.php?errore=1");
    exit();
}

$codice_cliente_trovato = null;

try {
    $sql = "SELECT codice FROM cliente WHERE nome = :nome AND cognome = :cognome AND dataNascita = :dataNascita";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nome' => $nome,
        'cognome' => $cognome,
        'dataNascita' => $dataNascita
    ]);
    
    $risultati = $stmt->fetchAll();

    if (count($risultati) === 1) {
        $codice_cliente_trovato = $risultati[0]['codice'];
    } else {
        header("Location: recupero_codice.php?errore=1");
        exit();
    }

} catch (PDOException $e) {
    error_log("Errore recupero codice: " . $e->getMessage());
    header("Location: recupero_codice.php?errore=1");
    exit();
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Codice Cliente Trovato</title>
    <link rel="stylesheet" href="../../assets/css/stile.css?v=<?= filemtime('../../assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Codice Trovato</header>
    <main style="text-align: center;">
        <div class="messaggio-conferma glass-panel">
            <h2>Ecco il tuo Codice Cliente</h2>
            <p>Puoi usare questo codice per accedere al tuo profilo e prenotare.</p>
            <div style="font-size: 1.6em; font-weight: bold; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.1);">
                <?= htmlspecialchars($codice_cliente_trovato) ?>
            </div>
            <a href="accesso.php" class="button" style="margin-top: 20px;">Accedi ora</a>
        </div>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
