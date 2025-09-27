<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../db_connection.php';

$messaggio = '';
$codice_cliente_generato = null;
$errori = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $dataNascita = $_POST['dataNascita'];
    $indirizzo = trim($_POST['indirizzo']) ?: NULL;

    if (empty($nome)) { $errori[] = "Il campo Nome è obbligatorio."; }
    if (empty($cognome)) { $errori[] = "Il campo Cognome è obbligatorio."; }
    if (empty($dataNascita)) { $errori[] = "Il campo Data di Nascita è obbligatorio."; }

    if (!empty($nome) && !preg_match("/^[a-zA-Z' ]+$/u", $nome)) {
        $errori[] = "Il Nome può contenere solo lettere, spazi e apostrofi.";
    }
    if (!empty($cognome) && !preg_match("/^[a-zA-Z' ]+$/u", $cognome)) {
        $errori[] = "Il Cognome può contenere solo lettere, spazi e apostrofi.";
    }

    if (!empty($dataNascita)) {
        try {
            $data_nascita_obj = new DateTime($dataNascita);
            $oggi = new DateTime();
            if ($data_nascita_obj > $oggi) {
                $errori[] = "La data di nascita non può essere nel futuro.";
            } else {
                $eta = $oggi->diff($data_nascita_obj)->y;
                if ($eta < 18) { $errori[] = "Devi avere almeno 18 anni per registrarti."; }
                if ($eta > 120) { $errori[] = "La data di nascita inserita non è valida."; }
            }
        } catch (Exception $e) {
            $errori[] = "Formato data di nascita non valido.";
        }
    }

    if (empty($errori)) {
        try {
            $codice_cliente_generato = 'CL' . strtoupper(substr($nome, 0, 1)) . strtoupper(substr($cognome, 0, 1)) . uniqid();
            
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
            error_log("Errore registrazione: " . $e->getMessage());
            $errori[] = "Si è verificato un errore tecnico durante la registrazione. Riprova più tardi.";
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
    <link rel="stylesheet" href="../../assets/css/stile.css?v=<?= filemtime('../../assets/css/stile.css') ?>">
</head>
<body class="glass-ui">
<div class="container">
    <header>Esito Registrazione</header>
    <main style="text-align: center;">
        <?php if (!empty($errori)): ?>
            <div class="messaggio errore glass-panel">
                <h2>Errore nella registrazione</h2>
                <ul style="text-align: left; display: inline-block; margin-top: 10px;">
                    <?php foreach ($errori as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <br>
                <a href="registrazione.php" class="button" style="margin-top: 20px;">Torna e Riprova</a>
            </div>
        <?php else: ?>
            <div class="messaggio-conferma glass-panel">
                <h2><?= htmlspecialchars($messaggio) ?></h2>
                <p style="font-size: 1.2em;">Conserva con cura il tuo Codice Cliente. Ti servirà per accedere e prenotare.</p>
                <div style="font-size: 1.6em; font-weight: bold; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid rgba(255,255,255,0.3); background: rgba(0,0,0,0.1);">
                    <?= htmlspecialchars($codice_cliente_generato) ?>
                </div>
                <a href="accesso.php" class="button" style="margin-top: 20px;">Vai alla pagina di Accesso</a>
            </div>
        <?php endif; ?>
    </main>
    <footer>© 2025 - Università degli Studi di Bergamo - Progetto Programmazione WEB</footer>
</div>
</body>
</html>
