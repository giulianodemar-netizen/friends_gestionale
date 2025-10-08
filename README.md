# Friends of Naples Gestionale

Plugin WordPress completo per la gestione di associazioni non profit.

## 📋 Descrizione

**Friends of Naples Gestionale** è un plugin WordPress professionale progettato per la gestione completa di associazioni, organizzazioni non profit e club. Include funzionalità avanzate per la gestione di soci, pagamenti, raccolte fondi, con dashboard statistiche, reminder automatici e molto altro.

## ✨ Caratteristiche Principali

- **Custom Post Types**: Soci, Pagamenti, Raccolte Fondi
- **Dashboard Amministrativa**: Statistiche in tempo reale e grafici interattivi
- **Sistema Reminder**: Notifiche automatiche email per scadenze quote
- **Esportazione Dati**: Export CSV di soci, pagamenti e raccolte fondi
- **Shortcode Frontend**: Visualizzazione dati sul sito pubblico
- **Progress Bar**: Tracking obiettivi raccolte fondi
- **Gestione Documenti**: Upload e archiviazione documenti soci
- **Email Automatiche**: Conferme pagamento e benvenuto nuovi soci
- **Design Responsivo**: Ottimizzato per tutti i dispositivi

## 📦 Installazione

### Requisiti
- WordPress 5.0+
- PHP 7.2+
- MySQL 5.6+

### Installazione Standard

1. **Download del Plugin**
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/giulianodemar-netizen/friends_gestionale.git
   ```

2. **Attivazione**
   - Vai su WordPress Admin → Plugin
   - Trova "Friends of Naples Gestionale"
   - Clicca su "Attiva"

3. **Configurazione**
   - Vai su Friends Gestionale → Impostazioni
   - Configura quota annuale e reminder

## 📚 Documentazione Completa

Per la documentazione dettagliata, consulta il file [README.md](friends_gestionale/README.md) all'interno della directory del plugin.

## 🎯 Utilizzo Rapido

### Gestione Soci
```
1. Vai su Soci → Aggiungi Socio
2. Compila i dati del socio
3. Imposta data iscrizione e quota
4. Pubblica
```

### Shortcode Frontend
```html
<!-- Elenco soci attivi -->
[fg_elenco_soci stato="attivo" limite="10"]

<!-- Raccolta fondi con progress bar -->
[fg_elenco_raccolte stato="attiva"]

<!-- Dashboard statistica -->
[fg_dashboard]
```

## 🛠️ Struttura Plugin

```
friends_gestionale/
├── friends_gestionale.php       # File principale plugin
├── includes/                    # Classi PHP
│   ├── class-post-types.php    # Custom Post Types
│   ├── class-meta-boxes.php    # Meta box e campi custom
│   ├── class-shortcodes.php    # Shortcodes
│   ├── class-admin-dashboard.php # Dashboard admin
│   ├── class-reminders.php      # Sistema reminder
│   ├── class-export.php         # Esportazione CSV
│   └── class-email.php          # Notifiche email
├── assets/                      # Risorse frontend/backend
│   ├── css/
│   │   ├── admin-style.css     # Stili admin
│   │   └── frontend-style.css  # Stili frontend
│   ├── js/
│   │   ├── admin-script.js     # JavaScript admin
│   │   └── frontend-script.js  # JavaScript frontend
│   └── images/                 # Icone e immagini
├── README.md                    # Documentazione completa
└── .gitignore                   # File da ignorare in Git
```

## 🔧 Sviluppo e Contributi

### Setup Ambiente di Sviluppo

```bash
# Clone repository
git clone https://github.com/giulianodemar-netizen/friends_gestionale.git

# Naviga nella directory
cd friends_gestionale

# Avvia ambiente WordPress locale (XAMPP, Local, Docker, ecc.)
```

### Contribuire al Progetto

1. Fork del repository
2. Crea un branch per la tua feature (`git checkout -b feature/nuova-funzionalita`)
3. Commit delle modifiche (`git commit -am 'Aggiungi nuova funzionalità'`)
4. Push del branch (`git push origin feature/nuova-funzionalita`)
5. Apri una Pull Request

## 📊 Funzionalità Dettagliate

### Custom Post Types

#### Soci (fg_socio)
- Nome e dati anagrafici
- Codice fiscale
- Contatti (email, telefono)
- Date iscrizione/scadenza
- Quota annuale
- Stato (Attivo, Sospeso, Scaduto, Inattivo)
- Categorie personalizzabili
- Upload documenti

#### Pagamenti (fg_pagamento)
- Associazione al socio
- Importo e data
- Metodo pagamento
- Tipo pagamento (Quota, Donazione, Evento)
- Note

#### Raccolte Fondi (fg_raccolta)
- Titolo e descrizione
- Obiettivo in €
- Importo raccolto
- Date inizio/fine
- Progress bar automatica
- Stato (Attiva, Completata, Sospesa)

### Dashboard Amministrativa

- **Statistiche Live**: Totale soci, attivi, scaduti, incassi
- **Grafici Interattivi**: Chart.js per andamento mensile
- **Pagamenti Recenti**: Ultimi 5 pagamenti registrati
- **Raccolte Attive**: Monitoraggio campagne in corso
- **Azioni Rapide**: Collegamenti veloci alle funzioni principali

### Sistema Email

- **Email Benvenuto**: Invio automatico ai nuovi soci
- **Reminder Scadenza**: Notifica X giorni prima della scadenza
- **Conferma Pagamento**: Ricezione automatica pagamento
- **Template Personalizzabili**: Oggetto e corpo email modificabili
- **Placeholder Dinamici**: {nome}, {data_scadenza}, {quota}

### Esportazione Dati

Tutti gli export sono in formato CSV con encoding UTF-8 + BOM per compatibilità Excel:

- **Export Soci**: Filtri per stato, tutti i campi
- **Export Pagamenti**: Filtri per data, collegamento al socio
- **Export Raccolte**: Filtri per stato, con percentuali

## 🎨 Personalizzazione

### Stili CSS Personalizzati

```css
/* Modifica colori progress bar */
.fg-progress-fill {
    background: linear-gradient(90deg, #your-color1, #your-color2);
}

/* Modifica stile card statistiche */
.fg-stat-card {
    background: #your-background;
    border-color: #your-border;
}
```

### Hook WordPress

```php
// Modifica email reminder
add_filter('fg_reminder_email_subject', function($subject) {
    return 'Urgente: ' . $subject;
});

// Aggiungi azioni dopo salvataggio socio
add_action('save_post_fg_socio', function($post_id) {
    // Tua logica personalizzata
});
```

## 🔐 Sicurezza

- ✅ Nonce verification
- ✅ Capability checks
- ✅ Input sanitization
- ✅ Output escaping
- ✅ SQL injection prevention
- ✅ CSRF protection

## 📞 Supporto

- **GitHub Issues**: [Apri una issue](https://github.com/giulianodemar-netizen/friends_gestionale/issues)
- **Email**: Contatta il team di sviluppo
- **Documentazione**: Consulta il README completo nel plugin

## 📄 Licenza

Questo plugin è rilasciato sotto licenza **GPL v2 o successiva**.

```
Copyright (C) 2024 Friends of Naples

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 🙏 Credits

Sviluppato con ❤️ dal team **Friends of Naples**

### Tecnologie Utilizzate
- WordPress API
- Chart.js per grafici
- jQuery per interazioni
- PHP 7.2+

## 🚀 Roadmap

### Versioni Future
- [ ] Integrazione gateway pagamento (Stripe, PayPal)
- [ ] App mobile companion
- [ ] Report PDF dettagliati
- [ ] Calendario eventi associazione
- [ ] Sistema tessere con QR Code
- [ ] Modulo presenze riunioni
- [ ] Multi-lingua (WPML/Polylang)
- [ ] REST API estesa

## 📈 Changelog

### Version 1.0.0 (2024)
- ✨ Release iniziale
- ✅ Custom Post Types completi
- ✅ Dashboard amministrativa
- ✅ Sistema reminder e email
- ✅ Export CSV
- ✅ Shortcodes frontend
- ✅ Statistiche e grafici

---

**Made with ❤️ in Naples, Italy**

Per domande, suggerimenti o collaborazioni: [GitHub](https://github.com/giulianodemar-netizen/friends_gestionale)