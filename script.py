import sqlite3
import random
import csv  # <-- NUOVO: Importa la libreria per gestire i file CSV
from datetime import date, timedelta

# --- IMPOSTAZIONI ---
DB_FILE = "ombrelloni.db"
NUM_CLIENTI = 500
NUM_OMBRELLONI = 200
STAGIONI = [2023, 2024, 2025]

# Funzione per connettersi al database
def crea_connessione(db_file):
    conn = None
    try:
        conn = sqlite3.connect(db_file)
    except sqlite3.Error as e:
        print(e)
    return conn

def popola_clienti(conn):
    print("Popolamento Clienti...")
    cursor = conn.cursor()
    clienti_dati = []
    for i in range(1, NUM_CLIENTI + 1):
        codice = f"CLIENTE{i:04d}"
        nome = f"Nome{i}"
        cognome = f"Cognome{i}"
        anno_nascita = date.today().year - random.randint(18, 70)
        data_nascita = date(anno_nascita, random.randint(1, 12), random.randint(1, 28))
        indirizzo = f"Via Esempio {i}"
        clienti_dati.append((codice, nome, cognome, data_nascita.isoformat(), indirizzo))
    
    cursor.executemany("INSERT OR IGNORE INTO Cliente (codice, nome, cognome, dataNascita, indirizzo) VALUES (?, ?, ?, ?, ?)", clienti_dati)
    conn.commit()
    print(f"-> {len(clienti_dati)} clienti inseriti nel DB.")
    
    # --- NUOVA PARTE: Scrittura su file CSV ---
    with open('clienti.csv', 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['codice', 'nome', 'cognome', 'dataNascita', 'indirizzo']) # Intestazione
        writer.writerows(clienti_dati)
    print("-> File 'clienti.csv' creato.")

def popola_ombrelloni(conn):
    print("Popolamento Ombrelloni...")
    cursor = conn.cursor()
    ombrelloni_dati = []
    for i in range(1, NUM_OMBRELLONI + 1):
        tipo = 'STD' if random.random() < 0.75 else 'VIP'
        settore = random.choice(['A', 'B', 'C', 'D', 'E'])
        fila = random.randint(1, 10)
        posto = random.randint(1, 15)
        ombrelloni_dati.append((i, settore, fila, posto, tipo))

    cursor.executemany("INSERT OR IGNORE INTO Ombrellone (id, settore, numFila, numPostoFila, codTipologia) VALUES (?, ?, ?, ?, ?)", ombrelloni_dati)
    conn.commit()
    print(f"-> {len(ombrelloni_dati)} ombrelloni inseriti nel DB.")
    
    # --- NUOVA PARTE: Scrittura su file CSV ---
    with open('ombrelloni.csv', 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['id', 'settore', 'numFila', 'numPostoFila', 'codTipologia'])
        writer.writerows(ombrelloni_dati)
    print("-> File 'ombrelloni.csv' creato.")


def simula_prenotazioni(conn):
    """Simula contratti e crea i file CSV relativi."""
    print("Simulazione prenotazioni...")
    cursor = conn.cursor()
    
    lista_clienti = [f"CLIENTE{i:04d}" for i in range(1, NUM_CLIENTI + 1)]
    id_contratto = 1000
    
    # Liste per contenere i dati da scrivere su CSV
    contratti_dati = []
    prenotazioni_giorni_dati = []

    for anno in STAGIONI:
        print(f"  Simulazione per l'anno {anno}...")
        prob_prenotazioni = {5: 5, 6: 15, 7: 30, 8: 40, 9: 10}

        for mese, num_contratti_giorno in prob_prenotazioni.items():
            for _ in range(num_contratti_giorno * 30):
                cliente = random.choice(lista_clienti)
                ombrellone_id = random.randint(1, NUM_OMBRELLONI)
                durata = 1 if random.random() < 0.4 else random.randint(7, 14)
                giorno_inizio = random.randint(1, 28)
                data_inizio = date(anno, mese, giorno_inizio)
                
                date_da_controllare = [data_inizio + timedelta(days=i) for i in range(durata)]
                placeholders = ','.join(['?'] * len(date_da_controllare))
                query = f"SELECT COUNT(*) FROM GiornoDisponibilita WHERE idOmbrellone = ? AND data IN ({placeholders}) AND numProgrContratto IS NOT NULL"
                params = [ombrellone_id] + [d.isoformat() for d in date_da_controllare]
                cursor.execute(query, params)
                
                if cursor.fetchone()[0] == 0:
                    id_contratto += 1
                    data_contratto = data_inizio - timedelta(days=random.randint(1, 30))
                    importo = durata * (random.randint(20, 50))
                    
                    # Aggiungi a lista contratti per CSV
                    contratti_dati.append((id_contratto, data_contratto.isoformat(), importo, cliente))
                    
                    # Aggiungi a lista prenotazioni per CSV
                    for d in date_da_controllare:
                        prenotazioni_giorni_dati.append((ombrellone_id, d.isoformat(), id_contratto))

    # Inserimento massivo nel DB (più veloce)
    print("Inserimento contratti e aggiornamento disponibilità nel DB...")
    cursor.executemany("INSERT INTO Contratto (numProgr, data, importo, codiceCliente) VALUES (?, ?, ?, ?)", contratti_dati)
    cursor.executemany("UPDATE GiornoDisponibilita SET numProgrContratto = ? WHERE idOmbrellone = ? AND data = ?", [(c, o, d) for o, d, c in prenotazioni_giorni_dati])
    conn.commit()
    print("-> Dati inseriti nel DB.")

    # Scrittura dei file CSV
    with open('contratti.csv', 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['numProgr', 'data', 'importo', 'codiceCliente'])
        writer.writerows(contratti_dati)
    print("-> File 'contratti.csv' creato.")
    
    with open('prenotazioni.csv', 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['idOmbrellone', 'data', 'numProgrContratto'])
        writer.writerows(prenotazioni_giorni_dati)
    print("-> File 'prenotazioni.csv' creato (contiene i giorni venduti).")

# --- ESECUZIONE SCRIPT ---
if __name__ == '__main__':
    connection = crea_connessione(DB_FILE)
    if connection:
        # Pulisce e pre-popola le tabelle necessarie
        cur = connection.cursor()
        cur.execute("DELETE FROM GiornoDisponibilita;"); cur.execute("DELETE FROM Contratto;"); cur.execute("DELETE FROM Cliente;"); cur.execute("DELETE FROM Ombrellone;")
        connection.commit()
        
        popola_clienti(connection)
        popola_ombrelloni(connection)
        # Popoliamo i giorni disponibili nel DB ma non creiamo un CSV perché sarebbe enorme
        popola_disponibilita(connection)
        # La funzione di simulazione ora popola il DB e crea i CSV
        simula_prenotazioni(connection)
        
        connection.close()
        print("\nDatabase popolato e file CSV generati con successo! 🎉")

# Funzione 'popola_disponibilita' necessaria allo script (da non modificare)
def popola_disponibilita(conn):
    print("Pre-popolamento GiornoDisponibilita (richiesto per la simulazione)...")
    cursor = conn.cursor()
    giorni_da_inserire = []
    for anno in STAGIONI:
        for mese in range(5, 10): # da Maggio a Settembre
            giorni_mese = 31 if mese in [5, 7, 8] else 30
            for giorno in range(1, giorni_mese + 1):
                data_corrente = date(anno, mese, giorno)
                for id_ombrellone in range(1, NUM_OMBRELLONI + 1):
                    giorni_da_inserire.append((id_ombrellone, data_corrente.isoformat()))
    cursor.executemany("INSERT OR IGNORE INTO GiornoDisponibilita (idOmbrellone, data) VALUES (?, ?)", giorni_da_inserire)
    conn.commit()
    print("-> Disponibilità di base inserite nel DB.")