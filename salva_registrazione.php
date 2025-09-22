<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db_connection.php';

$messaggio = '';
$codice_cliente_generato = null;
$errore = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['nome']) || empty($_POST['cognome']) || empty($_POST['dataNascita'])) {
        $messaggio = "Errore: Nome, cognome e data di nascita sono obbligatori.";
        $errore = true;
    } else {
        $nome = trim($_POST['nome']);
        $cognome = trim($_POST['cognome']);
        $dataNascita = $_POST['dataNascita'];
        $indirizzo = trim($_POST['indirizzo']) ?: NULL;

        try {
            // Genera un codice cliente univoco
            $codice_cliente_generato = 'CL' . strtoupper(substr($nome, 0, 1)) . strtoupper(substr($cognome, 0, 1)) . time();

            // NON C'È LA PASSWORD
            $sql = "INSERT INTO cliente (codice, nome, cognome, dataNascita, indirizzo) VALUES (:codice, :nome, :cognome, :dataNascita, :indirizzo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'codice' => $codice_cliente_generato,
                'nome' => $nome,
                'cognome' => $cognome,
                'dataNascita' => $dataNascita,
                'indirizzo' => $indirizzo,
            ]);
            
            $messaggio = "Registrazione completata con successo!";

        } catch (PDOException $e) {
            $messaggio = "Errore durante la registrazione: " . $e->getMessage();
            $errore = true;
        }
    }
} else {
    header("Location: registrazione.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Esito Registrazione</title>
    <link rel="stylesheet" href="stile.css">
</head>
<body>
<div class="container">
    <header>Esito Registrazione</header>
    <main style="padding-top: 50px;">
        <div class="messaggio <?= $errore ? 'errore' : 'successo' ?>">
            <h2><?= $messaggio ?></h2>
            <?php if (!$errore && $codice_cliente_generato): ?>
                <p style="font-size: 1.2em;">
                    Conserva con cura il tuo Codice Cliente. Ti servirà per accedere e prenotare.
                </p>
                <p style="font-size: 1.8em; font-weight: bold; color: #3b2a1a; background: #f4f0e9; padding: 20px; border-radius: 8px; margin-top: 20px; border: 2px dashed #ac6730;">
                    <?= htmlspecialchars($codice_cliente_generato) ?>
                </p>
                <a href="accesso.php" class="button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Vai alla pagina di Accesso</a>
            <?php else: ?>
                 <a href="registrazione.php" class="button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Riprova</a>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>