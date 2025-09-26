<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'db_connection.php';

$messaggio = '';
$codice_cliente_generato = null;
$errori = []; // Usiamo un array per collezionare tutti gli errori

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $dataNascita = $_POST['dataNascita'];
    $indirizzo = trim($_POST['indirizzo']) ?: NULL;

    // --- NUOVO BLOCCO DI VALIDAZIONE ---

    // 1. Controllo campi obbligatori
    if (empty($nome)) { $errori[] = "Il campo Nome è obbligatorio."; }
    if (empty($cognome)) { $errori[] = "Il campo Cognome è obbligatorio."; }
    if (empty($dataNascita)) { $errori[] = "Il campo Data di Nascita è obbligatorio."; }

    // 2. Controllo formato Nome e Cognome (solo lettere, spazi, apostrofi)
    if (!empty($nome) && !preg_match("/^[a-zA-Z' ]+$/", $nome)) {
        $errori[] = "Il Nome può contenere solo lettere, spazi e apostrofi.";
    }
    if (!empty($cognome) && !preg_match("/^[a-zA-Z' ]+$/", $cognome)) {
        $errori[] = "Il Cognome può contenere solo lettere, spazi e apostrofi.";
    }

    // 3. Controllo avanzato sulla Data di Nascita
    if (!empty($dataNascita)) {
        try {
            $data_nascita_obj = new DateTime($dataNascita);
            $oggi = new DateTime();
            
            if ($data_nascita_obj > $oggi) {
                $errori[] = "La data di nascita non può essere nel futuro.";
            } else {
                $eta = $oggi->diff($data_nascita_obj)->y;
                if ($eta < 18) {
                    $errori[] = "Devi avere almeno 18 anni per registrarti.";
                }
                if ($eta > 120) {
                    $errori[] = "La data di nascita inserita non è valida.";
                }
            }
        } catch (Exception $e) {
            $errori[] = "Formato data di nascita non valido.";
        }
    }
    
    // --- FINE BLOCCO DI VALIDAZIONE ---

    // Se non ci sono errori, procedi con la registrazione
    if (empty($errori)) {
        try {
            $codice_cliente_generato = 'CL' . strtoupper(substr($nome, 0, 1)) . strtoupper(substr($cognome, 0, 1)) . time();
            
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
            $errori[] = "Errore durante la registrazione: " . $e->getMessage();
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
    <link rel="stylesheet" href="stile.css?v=<?= filemtime('stile.css') ?>">
</head>
<body>
<div class="container">
    <header>Esito Registrazione</header>
    <main style="padding-top: 50px;">
        <?php if (!empty($errori)): ?>
            <div class="messaggio errore">
                <h2>Errore nella registrazione</h2>
                <ul style="text-align: left; display: inline-block; margin-top: 10px;">
                    <?php foreach ($errori as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <br>
                <a href="registrazione.php" class="button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Torna e Riprova</a>
            </div>
        <?php else: ?>
            <div class="messaggio successo">
                <h2><?= $messaggio ?></h2>
                <p style="font-size: 1.2em;">Conserva con cura il tuo Codice Cliente. Ti servirà per accedere e prenotare.</p>
                <p style="font-size: 1.8em; font-weight: bold; color: #3b2a1a; background: #f4f0e9; padding: 20px; border-radius: 8px; margin-top: 20px; border: 2px dashed #ac6730;">
                    <?= htmlspecialchars($codice_cliente_generato) ?>
                </p>
                <a href="accesso.php" class="button" style="text-decoration: none; display:inline-block; margin-top: 20px;">Vai alla pagina di Accesso</a>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>