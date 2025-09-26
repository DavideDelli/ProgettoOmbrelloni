<?php 
$page_title = 'Dashboard';
require_once 'partials/header.php'; 
?>

<h1>Benvenuto nel Pannello di Amministrazione</h1>
<p>Da qui puoi gestire gli aspetti chiave del sito Lido Paradiso.</p>

<div class="riepilogo-box" style="text-align: left;">
    <h3>Azioni Rapide</h3>
    <ul>
        <li><a href="gestione_tariffe.php">Modifica i prezzi delle tariffe</a></li>
        <li><a href="gestione_date.php">Aggiungi nuovi periodi di disponibilità per gli ombrelloni</a></li>
    </ul>
</div>

<?php require_once 'partials/footer.php'; ?>

