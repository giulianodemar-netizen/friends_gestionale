# Friends of Naples Gestionale

Plugin WordPress completo per la gestione di associazioni non profit con sistema di soci, pagamenti, raccolte fondi e funzionalità avanzate.

## 🎯 Caratteristiche Principali

### Custom Post Types
- **Soci**: Gestione completa dei membri dell'associazione
- **Pagamenti**: Registrazione e tracciamento dei pagamenti
- **Raccolte Fondi**: Campagne di fundraising con tracking obiettivi

### Funzionalità Avanzate
- ✅ Campi personalizzati per ogni post type
- ✅ Upload e gestione documenti
- ✅ Dashboard amministrativa con statistiche in tempo reale
- ✅ Grafici interattivi (andamento pagamenti, distribuzione soci)
- ✅ Sistema di reminder automatico per scadenze quote
- ✅ Notifiche email personalizzabili
- ✅ Esportazione dati in CSV (Soci, Pagamenti, Raccolte)
- ✅ Shortcode per visualizzazione frontend
- ✅ Sistema di filtri e ricerca avanzata
- ✅ Progress bar per raccolte fondi
- ✅ Design responsivo e mobile-friendly

## 📦 Installazione

### Metodo 1: Upload via WordPress
1. Scarica il plugin come file ZIP
2. Vai su **WordPress Admin** → **Plugin** → **Aggiungi Nuovo**
3. Clicca su **Carica Plugin**
4. Seleziona il file ZIP e clicca su **Installa Ora**
5. Attiva il plugin

### Metodo 2: Upload FTP
1. Scarica e decomprimi il plugin
2. Carica la cartella `friends_gestionale` nella directory `/wp-content/plugins/`
3. Vai su **WordPress Admin** → **Plugin**
4. Attiva **Friends of Naples Gestionale**

### Metodo 3: Installazione Manuale
```bash
cd wp-content/plugins/
git clone https://github.com/giulianodemar-netizen/friends_gestionale.git
```

Poi attiva il plugin dal pannello WordPress.

## ⚙️ Configurazione

### Impostazioni Iniziali
1. Vai su **Friends Gestionale** → **Impostazioni**
2. Configura:
   - Quota annuale predefinita (€)
   - Giorni anticipo per reminder (default: 30)
   - Abilita/disabilita notifiche email
   - Personalizza oggetto e messaggio email reminder

### Struttura Menu Admin
Dopo l'attivazione, troverai nel menu laterale:
- **Friends Gestionale**: Dashboard principale
- **Soci**: Gestione membri
- **Pagamenti**: Registro pagamenti
- **Raccolte Fondi**: Campagne fundraising
- **Dashboard**: Statistiche e riepilogo
- **Statistiche**: Grafici dettagliati
- **Esporta Dati**: Export CSV
- **Impostazioni**: Configurazione plugin

### Ruoli Utente

Il plugin crea automaticamente due ruoli personalizzati:

#### Friends Gestionale - Gestore Donatori
Ruolo con accesso completo a tutte le funzionalità del plugin:
- Creazione, modifica e cancellazione di soci, pagamenti, raccolte ed eventi
- Accesso all'import/export dati
- Visualizzazione statistiche e dashboard
- Gestione completa del sistema

#### Donatori Visualizzatore
Ruolo con accesso in sola lettura (NEW in v1.1.0):
- **Può visualizzare**: Tutti i dati (soci, pagamenti, raccolte fondi, eventi, statistiche)
- **Non può**: Creare, modificare, cancellare o importare dati
- **Ideale per**: Auditori, membri del consiglio, personale di controllo, consulenti

**Come assegnare il ruolo:**
1. Vai su **Utenti** → **Tutti gli utenti**
2. Modifica l'utente desiderato
3. Seleziona "Donatori Visualizzatore" dal menu a tendina "Ruolo"
4. Salva le modifiche

## 📚 Utilizzo

### Gestione Soci

#### Aggiungere un Socio
1. Vai su **Soci** → **Aggiungi Socio**
2. Compila i campi:
   - Nome (titolo del post)
   - Codice Fiscale
   - Email
   - Telefono
   - Indirizzo
   - Data Iscrizione
   - Data Scadenza (auto-calcolata)
   - Quota Annuale
   - Stato (Attivo, Sospeso, Scaduto, Inattivo)
   - Note
3. Carica documenti (se necessario)
4. Pubblica

#### Stati Socio
- **Attivo**: Socio con quota valida
- **Sospeso**: Temporaneamente sospeso
- **Scaduto**: Quota scaduta (aggiornato automaticamente)
- **Inattivo**: Non più membro

### Gestione Pagamenti

#### Registrare un Pagamento
1. Vai su **Pagamenti** → **Aggiungi Pagamento**
2. Seleziona il socio
3. Inserisci importo e data
4. Scegli metodo di pagamento (Contanti, Bonifico, Carta, PayPal, Altro)
5. Indica tipo pagamento (Quota, Donazione, Evento, Altro)
6. Aggiungi note (opzionale)
7. Pubblica

L'importo viene auto-compilato dalla quota del socio selezionato.

### Raccolte Fondi

#### Creare una Raccolta Fondi
1. Vai su **Raccolte Fondi** → **Aggiungi Raccolta**
2. Inserisci titolo e descrizione
3. Aggiungi immagine in evidenza
4. Configura:
   - Obiettivo (€)
   - Importo raccolto (€)
   - Data inizio/fine
   - Stato (Attiva, Completata, Sospesa)
5. Pubblica

La progress bar viene calcolata automaticamente.

## 🎨 Shortcodes

### Elenco Soci
```
[fg_elenco_soci categoria="" stato="attivo" limite="10"]
```
**Parametri:**
- `categoria`: Slug della categoria socio
- `stato`: attivo, sospeso, scaduto, inattivo
- `limite`: Numero massimo risultati (-1 per tutti)
- `ordina`: title, date, etc.
- `ordine`: ASC, DESC

### Dettaglio Socio
```
[fg_dettaglio_socio id="123"]
```
**Parametri:**
- `id`: ID del socio (default: post corrente)

### Elenco Raccolte
```
[fg_elenco_raccolte stato="attiva" limite="5"]
```
**Parametri:**
- `stato`: attiva, completata, sospesa
- `limite`: Numero raccolte da mostrare

### Dettaglio Raccolta
```
[fg_dettaglio_raccolta id="123"]
```
**Parametri:**
- `id`: ID della raccolta (default: post corrente)

### Progress Bar
```
[fg_progress_bar raccolta_id="123"]
```
**Parametri:**
- `raccolta_id`: ID della raccolta fondi

### Dashboard Statistica
```
[fg_dashboard]
```
Mostra dashboard con statistiche (solo per amministratori).

### Filtro Soci
```
[fg_filtro_soci]
```
Form di ricerca e filtro per soci.

## 📧 Sistema Reminder

### Reminder Automatici
Il plugin controlla giornalmente le scadenze e invia email automatiche ai soci:
- **Quando**: X giorni prima della scadenza (configurabile)
- **A chi**: Soci con stato "Attivo"
- **Cosa**: Email personalizzabile con dettagli quota

### Stato Automatico
I soci con quota scaduta vengono automaticamente impostati su stato "Scaduto".

### Email Inviate
- **Benvenuto**: Al momento dell'inserimento nuovo socio
- **Reminder**: Prima della scadenza quota
- **Conferma Pagamento**: Alla registrazione di un pagamento

## 📊 Dashboard e Statistiche

### Dashboard Principale
Mostra:
- Totale soci
- Soci attivi
- Soci scaduti
- Totale incassi
- Pagamenti recenti
- Raccolte fondi attive
- Azioni rapide

### Pagina Statistiche

**Filtro per Date (NEW in v1.1.0):**
La pagina statistiche ora include un filtro per intervallo date che permette di visualizzare i dati per un periodo specifico:
- **Data Inizio**: Data di inizio del periodo da analizzare (opzionale)
- **Data Fine**: Data di fine del periodo da analizzare (opzionale)
- Puoi usare entrambe le date per un intervallo specifico, oppure solo una per filtrare da/fino a una data
- Le date devono essere in formato ISO (YYYY-MM-DD)
- Il sistema valida che la data inizio sia <= data fine
- Tutti i grafici e statistiche si aggiornano automaticamente in base al filtro

**Grafici Interattivi:**
- **Andamento Pagamenti**: Mostra i pagamenti per mese nel periodo selezionato (default: ultimi 12 mesi)
- **Distribuzione Donatori per Stato**: Pie chart con distribuzione attiva/sospeso/scaduto/inattivo
- **Donazioni per Tipo**: Distribuzione dei pagamenti per tipo (quota/donazione/raccolta/evento/altro) - filtrato per date
- **Nuovi Donatori**: Trend di nuovi iscritti per mese nel periodo selezionato
- **Distribuzione Metodi di Pagamento**: Contanti, bonifico, carta, PayPal, altro - filtrato per date
- **Top Donatori**: Classifica dei maggiori contributori nel periodo filtrato
- **Eventi Prossimi**: Lista degli eventi in programma

Utilizza Chart.js per visualizzazioni avanzate e interattive.

## 💾 Esportazione e Importazione Dati

### Importazione da File CSV/XLSX (NEW in v1.1.0)

Il plugin supporta l'importazione di donatori da file CSV o Excel (XLSX):

1. Vai su **Friends Gestionale** → **Importa da file**
2. Carica il file CSV o XLSX
3. Associa le colonne del file ai campi del donatore (mapping)
4. Configura le opzioni di import:
   - **Aggiorna i record esistenti**: Se selezionato, i record con email già presenti vengono aggiornati
   - **Ignora record esistenti**: ⭐ NUOVA OPZIONE - Se selezionato, i record con email già presenti vengono saltati (non modificati)
5. Rivedi l'anteprima e conferma l'import

**Opzioni di Import:**
- **Ignora record esistenti (per email)**: Utile quando si vuole importare solo nuovi record senza modificare quelli esistenti. I record con email già presente nel database vengono saltati completamente, preservando i dati esistenti.
- **Aggiorna i record esistenti**: Permette di aggiornare i record esistenti con nuovi dati dal file.
- Entrambe le opzioni possono essere deselezionate per creare sempre nuovi record (duplicati).

**Tooltip migliorato per "Tipo Socio/Donatore":**
Il campo "Ruolo" ora ha un tooltip completo che spiega: "Se contiene 'socio' o 'donatore' (case-insensitive), il record verrà classificato rispettivamente come Socio o Donatore. Esempio: 'socio sostenitore' => Socio; 'donatore occasionale' => Donatore."

### Tipi di Export Disponibili

#### Esporta Soci
- Filtri per stato (Attivo, Sospeso, Scaduto, Inattivo)
- Include: ID, Nome, Codice Fiscale, Email, Telefono, Indirizzo, Date, Quota, Stato, Note
- Formato: CSV con UTF-8 BOM

#### Esporta Pagamenti
- Filtri per intervallo date
- Include: ID, Data, Socio, Importo, Metodo, Tipo, Note
- Formato: CSV con UTF-8 BOM

#### Esporta Raccolte Fondi
- Filtri per stato
- Include: ID, Titolo, Obiettivo, Raccolto, Percentuale, Date, Stato, Descrizione
- Formato: CSV con UTF-8 BOM

Tutti i file vengono generati con encoding UTF-8 e BOM per compatibilità Excel.

## 🎨 Personalizzazione

### CSS Personalizzato
Aggiungi CSS custom in **Aspetto** → **Personalizza** → **CSS Aggiuntivo**

Esempio:
```css
.fg-stat-card {
    border-radius: 10px;
}

.fg-progress-fill {
    background: linear-gradient(90deg, #ff6b6b, #ee5a6f);
}
```

### Hook e Filtri
Il plugin supporta hook WordPress per personalizzazioni avanzate:

```php
// Modifica oggetto email reminder
add_filter('fg_reminder_email_subject', function($subject, $member_id) {
    return "Promemoria: " . $subject;
}, 10, 2);

// Modifica messaggio email
add_filter('fg_reminder_email_message', function($message, $member_id) {
    return $message . "\n\nGrazie per il tuo supporto!";
}, 10, 2);
```

## 🔒 Sicurezza

Il plugin implementa:
- ✅ Nonce verification per tutte le operazioni
- ✅ Capability checks per permessi utente
- ✅ Sanitizzazione input dati
- ✅ Escape output dati
- ✅ Prepared statements per query database
- ✅ CSRF protection

## 🛠️ Requisiti Tecnici

- **WordPress**: 5.0 o superiore
- **PHP**: 7.2 o superiore
- **MySQL**: 5.6 o superiore

### Requisiti Consigliati
- WordPress 6.0+
- PHP 8.0+
- MySQL 8.0+
- HTTPS abilitato

## 📱 Compatibilità

### Browser Supportati
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

### Temi WordPress
Compatibile con tutti i temi standard WordPress. Testato con:
- Twenty Twenty-One
- Twenty Twenty-Two
- Twenty Twenty-Three
- Astra
- GeneratePress

## 🐛 Troubleshooting

### Email Non Vengono Inviate
1. Verifica impostazioni SMTP server
2. Installa plugin SMTP (es. WP Mail SMTP)
3. Controlla che le email non finiscano in spam

### Dashboard Non Visualizza Dati
1. Assicurati di avere il ruolo Amministratore
2. Pulisci cache browser
3. Disabilita eventuali plugin di cache

### Shortcode Non Funzionano
1. Verifica sintassi shortcode
2. Controlla che il plugin sia attivo
3. Verifica compatibilità con page builder

## 🔄 Aggiornamenti

### Changelog

#### Version 1.0.0 (2024)
- ✨ Release iniziale
- ✅ Custom Post Types (Soci, Pagamenti, Raccolte Fondi)
- ✅ Dashboard amministrativa
- ✅ Sistema reminder email
- ✅ Export CSV
- ✅ Shortcodes frontend
- ✅ Statistiche e grafici
- ✅ Gestione documenti

## 👥 Supporto

Per assistenza e supporto:
- **Repository GitHub**: [https://github.com/giulianodemar-netizen/friends_gestionale](https://github.com/giulianodemar-netizen/friends_gestionale)
- **Issues**: Apri una issue su GitHub
- **Email**: Contatta il team di sviluppo

## 📄 Licenza

Questo plugin è rilasciato sotto licenza GPL v2 o successiva.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🙏 Credits

Sviluppato da **Friends of Naples Team**

### Librerie Utilizzate
- Chart.js per grafici
- jQuery per interazioni
- WordPress Media Library per upload

## 🚀 Roadmap

### Prossime Funzionalità
- [ ] Integrazione gateway pagamento online
- [ ] App mobile companion
- [ ] Report PDF avanzati
- [ ] Calendario eventi
- [ ] Sistema tessere associate
- [ ] QR Code per check-in
- [ ] Multi-lingua (WPML ready)
- [ ] Integrazione newsletter

## 📸 Screenshot

*(Gli screenshot saranno aggiunti nella documentazione online)*

1. Dashboard principale
2. Gestione soci
3. Registrazione pagamenti
4. Statistiche e grafici
5. Esportazione dati
6. Impostazioni plugin
7. Frontend shortcodes

---

**Made with ❤️ by Friends of Naples**

Per domande o suggerimenti, non esitare a contattarci!
