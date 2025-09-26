<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db_connection.php';

$codice_cliente_trovato = null;
$errore = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $dataNascita = $_POST['dataNascita'];

    if (empty($nome) || empty($cognome) || empty($dataNascita)) {
        header("Location: recupero_codice.php?errore=1");
        exit();
    }

    try {
        $sql = "SELECT codice FROM cliente WHERE nome = :nome AND cognome = :cognome AND dataNascita = :dataNascita";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'cognome' => $cognome,
            'dataNascita' => $dataNascita
        ]);
        
        // Usiamo fetchAll per gestire il caso raro di omonimie con stessa data di nascita
        $risultati = $stmt->fetchAll();

        if (count($risultati) === 1) {
            // Caso ideale: trovato un solo utente
            $codice_cliente_trovato = $risultati[0]['codice'];
        } else {
            // Nessun utente o (caso rarissimo) più di uno. Per sicurezza, diamo errore.
            $errore = true;
        }

    } catch (PDOException $e) {
        $errore = true;
    }
} else {
    header("Location: recupero_codice.php");
    exit();
}

// Se i dati non corrispondono, reindirizziamo alla pagina di recupero con un messaggio di errore
if ($errore) {
    header("Location: recupero_codice.php?errore=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Codice Cliente Trovato</title>
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
</head>
<body>
<div class="container">
    <header>Codice Trovato</header>
    <main style="padding-top: 50px;">
        <div class="messaggio successo">
            <h2>Ecco il tuo Codice Cliente</h2>
            <p style="font-size: 1.8em; font-weight: bold; color: #3b2a1a; background: #f4f0e9; padding: 20px; border-radius: 8px; margin-top: 20px; border: 2px dashed #ac6730;">
                <?= htmlspecialchars($codice_cliente_trovato) ?>
            </p>
            <a href="accesso.php" class="button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Accedi ora</a>
        </div>
    </main>
</div>
</body>
</html>