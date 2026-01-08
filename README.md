# 🏖️ Noleggio Ombrelloni
**Progetto di Programmazione WEB — Università degli Studi di Bergamo**
*Ex 7: Noleggio Ombrelloni con interfaccia 4 e palette marrone*

## 📖 Introduzione
Il progetto consiste nello sviluppo di un sito web dedicato alla gestione e al noleggio di ombrelloni in spiaggia. L'obiettivo principale è semplificare e rendere intuitiva la procedura di prenotazione per gli utenti, offrendo al contempo strumenti di amministrazione per il gestore.

## 👥 Team di Sviluppo
Il progetto è stato realizzato da:
* **Francesca Corrente**: Progettazione grafica, sviluppo tecnico e pubblicazione.
* **Davide Dell’Anno**: Progettazione grafica, sviluppo tecnico e pubblicazione.

---

## 🚀 Funzionalità

### 👤 Area Utente
Il sito offre un'esperienza completa per la gestione delle prenotazioni balneari:

**Gestione Account**
* **Registrazione:** Creazione account con nome, cognome, data di nascita e indirizzo (opzionale).
* **Login & Recupero:** Accesso all'area riservata e recupero del "Codice Cliente" tramite dati anagrafici.
* **Gestione Profilo:** Modifica dei dati personali ed eliminazione definitiva dell'account.

**Sistema di Prenotazione**
* **Prenotazione Giornaliera:** Selezione ombrellone per singolo giorno.
    * *Pacchetti:* Standard, Premium (con asciugamani), Giornaliero con aperitivo.
* **Abbonamento Settimanale:** Prenotazione per più giorni consecutivi.
    * *Pacchetti:* Settimanale VIP, Premium VIP (con asciugamani), Settimanale VIP con aperitivo.
* **Gestione Prenotazioni:**
    * Modifica data o tipologia pacchetto.
    * Cancellazione prenotazione.
    * Visualizzazione storico (Le mie prenotazioni) con dettagli su contratto, posizione (settore/fila/posto) e costi.

**Mappa Interattiva**
Visualizzazione grafica della spiaggia per la scelta dell'ombrellone.
* 🟢 **Verde:** Ombrelloni Standard disponibili.
* 🟡 **Giallo:** Ombrelloni VIP disponibili.
* ⚪ **Bianco:** Ombrelloni già prenotati.

### 🛠️ Area Amministrazione (Admin)
Pannello dedicato al gestore del lido.
* **Modifica Tariffe:** Gestione dei prezzi per le diverse tipologie di pacchetti.
* **Gestione Date:** Modifica dei giorni e periodi di apertura/disponibilità.

> **Credenziali Admin di default:**
> Password: `admin123`

---

## 🗺️ Struttura e Navigazione

### Navigazione Pubblica (Utente Ospite)
* **Home:** Pagina introduttiva.
* **Accedi:** Login per utenti registrati.
* **Registrati:** Form di creazione nuovo account.
* **Admin:** Accesso all'area riservata al gestore.

### Navigazione Privata (Utente Loggato)
* **Home:** Dashboard con accesso rapido.
* **Mappa Spiaggia:** Interfaccia visiva per le prenotazioni.
* **Le mie prenotazioni:** Elenco contratti attivi e storico.
* **Il mio profilo:** Modifica dati, cancellazione account e logout.

---

## 💻 Tech Stack e Strumenti

### Linguaggi
* ![PHP](https://img.shields.io/badge/PHP-Logic-blue) **PHP**: Gestione logica backend e interazione con il database.
* ![HTML/CSS](https://img.shields.io/badge/HTML5%2FCSS3-Design-orange) **HTML/CSS**: Struttura semantica e design grafico (Palette Marrone).

### Strumenti di Sviluppo
* **Visual Studio Code**: Editor di codice.
* **GitHub**: Version control e collaborazione.
* **Altervista**: Piattaforma di hosting e database.
