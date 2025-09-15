# Progetto Web: Gestione Noleggio Ombrelloni

Questo progetto è un'applicazione web per la gestione delle prenotazioni in uno stabilimento balneare, sviluppato per il corso di Programmazione Web.

## Prerequisiti

Per avviare il progetto in locale, è necessario avere installato:
- Un ambiente server locale (es. MAMP, XAMPP, o uno stack LAMP/MAMP su Homebrew).
- PHP 8.x o superiore.
- Un server di database MariaDB o MySQL.
- Python 3.x per eseguire lo script di popolamento dati.

## ⚙️ Guida all'Installazione Locale

Segui questi passaggi per configurare il progetto sul tuo computer dopo aver clonato il repository.

1.  **Clona il Repository**
    ```bash
    git clone [https://github.com/DavideDelli/ProgettoOmbrelloni.git](https://github.com/DavideDelli/ProgettoOmbrelloni.git)
    cd ProgettoOmbrelloni
    ```

2.  **Crea il Database**
    - Accedi al tuo gestore di database (es. phpMyAdmin, DBeaver, o da terminale).
    - Crea un nuovo database vuoto chiamato `noleggio_ombrelloni`.

3.  **Importa la Struttura del Database**
    - Nel database appena creato, importa la struttura delle tabelle eseguendo il contenuto del file `schema.sql`.

4.  **Configura la Connessione**
    - Fai una copia del file `db_connection.php.example` e rinominala in `db_connection.php`.
    - Apri il nuovo file `db_connection.php` e inserisci le credenziali (host, utente, password) del tuo database locale. **Questo file è ignorato da Git e non verrà mai caricato online.**

5.  **Popola il Database con Dati Fittizi (Opzionale)**
    - Per riempire il database con dati di esempio, esegui lo script Python dalla cartella del progetto:
    ```bash
    python script.py
    ```

6.  **Avvia il Progetto**
    - Assicurati che il tuo server locale (Apache/PHP) sia in esecuzione.
    - Apri il browser e naviga all'URL del progetto (es. `http://localhost/ProgettoOmbrelloni/`).

## Struttura del Progetto
- `index.php`: Pagina principale dell'applicazione.
- `db_connection.php`: File locale (ignorato) per la connessione al DB.
- `stile.css`: Foglio di stile.
- `script.py`: Script per popolare il DB con dati fittizi.
- `schema.sql`: Struttura delle tabelle del database.
- `.gitignore`: File che specifica cosa ignorare per Git.