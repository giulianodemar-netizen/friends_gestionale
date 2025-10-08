# Friends of Naples Gestionale - Quick Start Guide

## 🚀 Installazione Rapida

### Passo 1: Upload del Plugin
Copia la cartella `friends_gestionale` nella directory `/wp-content/plugins/` del tuo sito WordPress.

### Passo 2: Attivazione
1. Vai su **WordPress Admin** → **Plugin**
2. Trova "Friends of Naples Gestionale"
3. Clicca su **Attiva**

### Passo 3: Configurazione Iniziale
1. Vai su **Friends Gestionale** → **Impostazioni**
2. Imposta:
   - Quota annuale predefinita: €50 (o il tuo importo)
   - Giorni anticipo reminder: 30
   - Abilita notifiche email: ✓

### Passo 4: Verifica Installazione
Dopo l'attivazione dovresti vedere nel menu admin:
- ✅ Friends Gestionale (menu principale)
- ✅ Soci
- ✅ Pagamenti
- ✅ Raccolte Fondi

## 📝 Primi Passi

### 1. Aggiungi il Primo Socio
```
Admin → Soci → Aggiungi Socio
- Nome: Mario Rossi
- Email: mario@example.com
- Data Iscrizione: 2024-01-01
- Quota Annuale: 50
- Stato: Attivo
→ Pubblica
```

### 2. Registra un Pagamento
```
Admin → Pagamenti → Aggiungi Pagamento
- Socio: Mario Rossi
- Importo: 50
- Data: 2024-01-01
- Metodo: Bonifico
- Tipo: Quota Associativa
→ Pubblica
```

### 3. Crea una Raccolta Fondi
```
Admin → Raccolte Fondi → Aggiungi Raccolta
- Titolo: Nuova Sede
- Obiettivo: 5000
- Raccolto: 1500
- Stato: Attiva
→ Pubblica
```

### 4. Visualizza Dashboard
Vai su **Friends Gestionale** → **Dashboard** per vedere:
- Statistiche soci
- Totale incassi
- Pagamenti recenti
- Raccolte attive

## 🎨 Uso Shortcode sul Sito Pubblico

Crea una pagina WordPress e inserisci:

```
[fg_elenco_soci stato="attivo"]
```

Oppure per le raccolte fondi:

```
[fg_elenco_raccolte stato="attiva"]
```

## 📧 Configurazione Email

### SMTP Consigliato
Per email affidabili, installa un plugin SMTP:
1. Installa "WP Mail SMTP"
2. Configura con il tuo provider
3. Testa l'invio email

### Verifica Email Funzionanti
1. Aggiungi un socio con la tua email
2. Verifica di ricevere l'email di benvenuto
3. Se non arriva, controlla spam e configurazione SMTP

## 🔍 Risoluzione Problemi Comuni

### Email Non Arrivano
✅ Installa plugin SMTP
✅ Verifica cartella spam
✅ Controlla impostazioni server

### Dashboard Vuota
✅ Verifica di essere Amministratore
✅ Pulisci cache browser
✅ Ricarica la pagina

### Shortcode Non Funzionano
✅ Verifica sintassi shortcode
✅ Controlla che il plugin sia attivo
✅ Disabilita temporaneamente altri plugin per test

## 📊 Export Dati

Per esportare i dati:
1. Vai su **Friends Gestionale** → **Esporta Dati**
2. Scegli tipo: Soci / Pagamenti / Raccolte
3. Seleziona filtri
4. Clicca "Esporta CSV"

Il file CSV sarà compatibile con Excel e Google Sheets.

## 🎯 Funzionalità Automatiche

### Il Plugin Automaticamente:
- ✅ Aggiorna stato "Scaduto" per soci con quota scaduta
- ✅ Invia reminder X giorni prima della scadenza
- ✅ Invia email di benvenuto ai nuovi soci
- ✅ Invia conferme di pagamento
- ✅ Mostra notifiche admin per scadenze imminenti

### Cron Jobs
Il sistema di reminder usa WordPress Cron:
- Controllo: 1 volta al giorno
- Orario: In base al cron WordPress
- Log: Verifica in Tools → Site Health

## 📱 Accesso Mobile

Il plugin è completamente responsivo:
- ✅ Dashboard admin mobile-friendly
- ✅ Form ottimizzati per touch
- ✅ Shortcode responsive
- ✅ Tabelle scrollabili su mobile

## 🔐 Sicurezza

Il plugin implementa:
- ✅ Verifica permessi utente
- ✅ Protezione CSRF
- ✅ Sanitizzazione input
- ✅ Escape output
- ✅ Nonce verification

## 📞 Supporto

Hai bisogno di aiuto?
- 📖 Leggi la documentazione completa: `friends_gestionale/README.md`
- 🐛 Segnala problemi: GitHub Issues
- 💬 Chiedi supporto: Email team

## ✅ Checklist Post-Installazione

Dopo l'installazione verifica:
- [ ] Plugin attivato correttamente
- [ ] Menu "Friends Gestionale" visibile
- [ ] Impostazioni configurate
- [ ] Test email funzionanti
- [ ] Dashboard accessibile
- [ ] Shortcode testati su pagina pubblica
- [ ] Export CSV funzionante
- [ ] Backup database eseguito

## 🎓 Prossimi Passi

1. **Importa Dati Esistenti** (se hai un database esterno)
2. **Configura Categorie Soci** (Admin → Soci → Categorie)
3. **Personalizza Email Template** (Impostazioni → Email)
4. **Crea Pagine Pubbliche** con shortcode
5. **Imposta Backup Automatici**
6. **Forma il Team** sull'uso del plugin

---

**Buon Lavoro con Friends of Naples Gestionale!** 🎉

Per supporto dettagliato consulta il README.md completo.
