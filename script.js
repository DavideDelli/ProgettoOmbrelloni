// Aspetta che tutta la pagina HTML sia stata caricata prima di eseguire lo script
document.addEventListener('DOMContentLoaded', function() {

    // Seleziona tutti i bottoni dei settori
    const bottoniSettore = document.querySelectorAll('.bottone-settore');
    // Seleziona la vista principale della spiaggia
    const vistaSpiaggia = document.getElementById('vista-spiaggia');
    // Seleziona tutti i contenitori delle griglie degli ombrelloni
    const griglieContainer = document.querySelectorAll('.sector-grid-container');
    // Seleziona tutti i link "torna indietro"
    const linkTornaIndietro = document.querySelectorAll('.torna-alla-mappa');

    // Aggiungi un gestore di eventi a ogni bottone di settore
    bottoniSettore.forEach(function(bottone) {
        bottone.addEventListener('click', function() {
            // Prende il nome del settore dal bottone cliccato (es. 'A', 'B', ...)
            const settoreTarget = bottone.dataset.settore;
            
            // Nascondi la vista della spiaggia
            if (vistaSpiaggia) {
                vistaSpiaggia.style.display = 'none';
            }

            // Nascondi TUTTE le griglie prima di mostrare quella giusta
            griglieContainer.forEach(function(container) {
                container.classList.add('hidden');
            });

            // Trova e mostra la griglia giusta
            const grigliaTarget = document.getElementById('settore-grid-' + settoreTarget);
            if (grigliaTarget) {
                grigliaTarget.classList.remove('hidden');
            }
        });
    });

    // Aggiungi un gestore di eventi a ogni link "torna indietro"
    linkTornaIndietro.forEach(function(link) {
        link.addEventListener('click', function(event) {
            // Impedisce al link di comportarsi come un link normale
            event.preventDefault();

            // Nascondi tutte le griglie
            griglieContainer.forEach(function(container) {
                container.classList.add('hidden');
            });

            // Mostra di nuovo la vista della spiaggia
            if (vistaSpiaggia) {
                vistaSpiaggia.style.display = 'block';
            }
        });
    });
});