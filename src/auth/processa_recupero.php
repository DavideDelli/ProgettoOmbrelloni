<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../db_connection.php';

// 1. Check if the request is a POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: recupero_codice.php");
    exit();
}

// 2. Sanitize and validate input
$nome = trim($_POST['nome']);
$cognome = trim($_POST['cognome']);
$dataNascita = $_POST['dataNascita'];

if (empty($nome) || empty($cognome) || empty($dataNascita)) {
    header("Location: recupero_codice.php?errore=1");
    exit();
}

$codice_cliente_trovato = null;

try {
    // 3. Prepare and execute the query to find the customer
    $sql = "SELECT codice FROM cliente WHERE nome = :nome AND cognome = :cognome AND dataNascita = :dataNascita";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nome' => $nome,
        'cognome' => $cognome,
        'dataNascita' => $dataNascita
    ]);
    
    $risultati = $stmt->fetchAll();

    // 4. Check if exactly one customer was found
    if (count($risultati) === 1) {
        $codice_cliente_trovato = $risultati[0]['codice'];
    } else {
        // If 0 or more than 1 customer is found, redirect with an error
        header("Location: recupero_codice.php?errore=1");
        exit();
    }

} catch (PDOException $e) {
    // Log the error and redirect
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
<body>
<div class="container">
    <header>Codice Trovato</header>
    <main style="padding-top: 50px;">
        <div class="messaggio-conferma">
            <h2>Ecco il tuo Codice Cliente</h2>
            <p>Puoi usare questo codice per accedere al tuo profilo e prenotare.</p>
            <p style="font-size: 1.8em; font-weight: bold; color: #3b2a1a; background: #f4f0e9; padding: 20px; border-radius: 8px; margin-top: 20px; border: 2px dashed #ac6730;">
                <?= htmlspecialchars($codice_cliente_trovato) ?>
            </p>
            <a href="accesso.php" class="button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Accedi ora</a>
        </div>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
