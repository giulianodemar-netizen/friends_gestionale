# Guida Import Donatori da CSV/XLSX

## Panoramica

La funzionalità di import permette di caricare in blocco donatori e soci da file CSV o XLSX. Include:
- Upload tramite drag & drop o selezione file
- Anteprima dati con le prime 100 righe
- Mapping flessibile delle colonne
- Validazione automatica dei dati
- Preview delle azioni prima dell'import
- Gestione errori con download CSV

## Accesso alla Funzionalità

1. Accedi al pannello di amministrazione WordPress
2. Vai su **Donatori** → **Importa da file**
3. Segui i 4 step guidati

## Formati File Supportati

### CSV
- Separatori supportati: virgola (,), punto e virgola (;), tabulazione
- Encoding: UTF-8 (raccomandato) o ISO-8859-1
- Prima riga: intestazioni colonne

### XLSX
- File Excel 2007+ (.xlsx)
- Primo foglio utilizzato
- Prima riga: intestazioni colonne

## Campi Mappabili

| Campo | Obbligatorio | Note |
|-------|--------------|------|
| Nome | Sì* | *Obbligatorio se non presente Ragione Sociale |
| Cognome | Sì* | *Obbligatorio se non presente Ragione Sociale |
| Ragione Sociale | No | Per società - se presente, Nome e Cognome diventano opzionali |
| Email | No | Se presente viene validato il formato |
| Telefono | No | |
| Indirizzo | No | |
| Città | No | |
| CAP | No | |
| Provincia | No | |
| Nazione | No | |
| Ruolo | No | Default: "anche_socio" (Donatore e Socio) |
| Data Iscrizione | No | Default: oggi (solo per ruolo "socio") |
| Codice Fiscale | No | |
| Partita IVA | No | |
| Note | No | |

## Regole di Validazione

### 1. Nome/Cognome vs Ragione Sociale
- **Per privati**: Nome E Cognome obbligatori
- **Per società**: Ragione Sociale obbligatoria, Nome e Cognome opzionali (possono essere usati per il referente)

✅ Valido:
```
Nome: Mario, Cognome: Rossi, Ragione Sociale: (vuoto)
Nome: (vuoto), Cognome: (vuoto), Ragione Sociale: Azienda SRL
Nome: Mario, Cognome: Rossi, Ragione Sociale: Azienda SRL
```

❌ Non valido:
```
Nome: Mario, Cognome: (vuoto), Ragione Sociale: (vuoto) → Errore: Cognome richiesto
Nome: (vuoto), Cognome: Rossi, Ragione Sociale: (vuoto) → Errore: Nome richiesto
```

### 2. Email
- Formato email valido se presente
- Campo opzionale
- Se email esiste già: vedi "Gestione Duplicati"

### 3. Ruolo (Tipo Donatore)
Valori accettati (case-insensitive):
- **Per "Donatore e Socio"**: socio, membro, member, anche_socio
- **Per "Solo Donatore"**: donatore, donor, solo_donatore

Default: "anche_socio" se non specificato

### 4. Data Iscrizione
- **Per ruolo "socio"**: Se non specificata, viene impostata automaticamente alla data odierna
- **Per ruolo "donatore"**: Non viene impostata se non specificata
- Formato accettato: YYYY-MM-DD (es: 2024-01-15)

## Processo di Import Step-by-Step

### Step 1: Upload File
1. Trascina il file CSV/XLSX nell'area designata, oppure
2. Clicca su "Scegli file" per selezionarlo
3. Il file viene caricato e analizzato automaticamente

### Step 2: Mapping Colonne
1. Associa ogni campo del donatore a una colonna del file
2. Il sistema tenta un auto-mapping intelligente basato sui nomi delle colonne
3. Opzioni disponibili per ogni campo:
   - **Colonna specifica**: Usa il valore dalla colonna
   - **Non importare**: Salta questo campo
   - **Valore statico**: Imposta un valore fisso per tutti i record

#### Template Mapping
- **Salva mapping**: Salva la configurazione corrente per riutilizzi futuri
- **Carica template**: Riapplica un mapping salvato precedentemente

#### Opzioni Import
- ☑ **Aggiorna i record esistenti con la stessa email**: Se selezionato, i donatori con email già presente vengono aggiornati invece di creare duplicati

### Step 3: Anteprima
1. Visualizza statistiche:
   - Righe da creare
   - Righe da aggiornare
   - Righe da saltare
   - Righe con errori
2. Controlla l'anteprima delle prime 50 righe
3. Verifica errori e avvisi
4. Torna al mapping se necessario

### Step 4: Esecuzione
1. Clicca "Esegui Import"
2. Conferma l'operazione
3. Attendi il completamento
4. Visualizza il riepilogo:
   - Donatori creati
   - Donatori aggiornati
   - Righe saltate
   - Errori rilevati
5. Scarica il CSV degli errori se presenti

## Gestione Duplicati

### Opzione: "Aggiorna i record esistenti con la stessa email" NON selezionata (default)
- Email esistente → Crea nuovo record duplicato
- Tutti i record vengono creati come nuovi

### Opzione: "Aggiorna i record esistenti con la stessa email" SELEZIONATA
- Email esistente → Aggiorna il record esistente
- Email nuova → Crea nuovo record
- Record senza email → Sempre creati come nuovi

## Gestione Errori

### Durante la Preview
- Righe con errori evidenziate in rosso
- Messaggi di errore visualizzati nella colonna "Messaggi"
- Le righe con errori NON vengono importate

### Dopo l'Import
- Download automatico CSV con errori
- Il CSV contiene:
  - Numero riga originale
  - Descrizione errori
  - Dati completi della riga

## Esempi Pratici

### Esempio 1: Import Soci Privati
```csv
Nome,Cognome,Email,Telefono,Ruolo
Mario,Rossi,mario.rossi@example.com,3331234567,socio
Luigi,Verdi,luigi.verdi@example.com,3337654321,socio
```

Risultato:
- 2 donatori creati tipo "anche_socio"
- Data iscrizione: oggi (default)

### Esempio 2: Import Società
```csv
Ragione Sociale,Email,Telefono,Ruolo,Data Iscrizione
Tech Solutions SRL,info@techsolutions.com,0612345678,socio,2024-01-15
Consulting Group SpA,contatti@consulting.com,0687654321,socio,2024-02-01
```

Risultato:
- 2 donatori creati tipo "anche_socio" (società)
- Data iscrizione: date specificate

### Esempio 3: Import Misto
```csv
Nome,Cognome,Ragione Sociale,Email,Ruolo,Data Iscrizione
Mario,Rossi,,mario.rossi@example.com,socio,2024-01-15
Luigi,Verdi,,luigi.verdi@example.com,donatore,
,,,Azienda SRL,info@azienda.com,socio,
```

Risultato:
- Riga 1: Socio privato, data 2024-01-15
- Riga 2: Donatore privato, nessuna data iscrizione
- Riga 3: Socio società, data oggi (default)

## Limitazioni e Best Practices

### Limitazioni
- Anteprima limitata a 100 righe (tutte le righe vengono comunque importate)
- File molto grandi (>10MB) potrebbero richiedere più tempo
- Timeout: 120 secondi per l'operazione di import

### Best Practices
1. **Prepara i dati**: Pulisci e valida i dati prima dell'import
2. **Test con campione**: Importa prima un piccolo campione per verificare il mapping
3. **Backup**: Fai un backup del database prima di import di grandi dimensioni
4. **Usa template**: Salva i mapping per import ricorrenti
5. **Controlla errori**: Analizza sempre il CSV degli errori dopo l'import
6. **Email uniche**: Per aggiornamenti, assicurati che le email siano uniche e corrette
7. **Codifica UTF-8**: Usa UTF-8 per caratteri speciali e accenti

## Troubleshooting

### Problema: "File vuoto" o "Impossibile leggere le intestazioni"
**Soluzione**: Verifica che:
- Il file contenga dati
- La prima riga contenga le intestazioni
- Il separatore sia corretto (per CSV)

### Problema: "Email non valida"
**Soluzione**: Verifica il formato email nel file originale

### Problema: Caratteri strani (es: Ã , Ã¨)
**Soluzione**: Salva il CSV con encoding UTF-8

### Problema: "Nome richiesto" per società
**Soluzione**: Compila la colonna "Ragione Sociale"

### Problema: Data iscrizione non impostata per soci
**Soluzione**: Verifica che il campo "Ruolo" sia mappato correttamente e contenga valori come "socio" o "anche_socio"

## Test Automatici

Per verificare la correttezza delle validazioni, eseguire:

```bash
cd friends_gestionale/tests
php test-import-validation.php
```

Tutti i test dovrebbero risultare PASS (✓).

## File di Esempio

Nella directory `tests/` è disponibile:
- `sample-import.csv`: File CSV di esempio con vari scenari

## Supporto

Per problemi o domande:
1. Consulta questa guida
2. Verifica i test di validazione
3. Contatta il supporto con:
   - File CSV/XLSX problematico
   - Screenshot dell'errore
   - CSV degli errori generato (se disponibile)
