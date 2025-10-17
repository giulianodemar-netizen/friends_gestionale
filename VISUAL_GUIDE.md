# Guida Visuale: Sistema Donatori

## 📋 Menu di Amministrazione

### Prima (Soci)
```
Dashboard
├── Soci
│   └── Categorie
├── Pagamenti
├── Raccolte Fondi
└── Eventi
```

### Dopo (Donatori)
```
Dashboard
├── Donatori
│   ├── Tipologie Socio
│   └── Categorie Donatore
├── Pagamenti
├── Raccolte Fondi
└── Eventi
```

---

## 📝 Form Donatore

### Struttura Form

```
┌─────────────────────────────────────────────────┐
│ INFORMAZIONI DONATORE                           │
├─────────────────────────────────────────────────┤
│                                                 │
│ ┌─────────────────────────────────────────┐   │
│ │ TIPO DONATORE                           │   │
│ │                                         │   │
│ │ Questa persona è: [▼ Dropdown]         │   │
│ │   • Solo Donatore                      │   │
│ │   • Donatore e Socio                   │   │
│ └─────────────────────────────────────────┘   │
│                                                 │
│ ┌─────────────────────────────────────────┐   │
│ │ DATI ANAGRAFICI                         │   │
│ │                                         │   │
│ │ Nome: [________]  Cognome: [________]  │   │
│ │ Codice Fiscale: [_________________]    │   │
│ └─────────────────────────────────────────┘   │
│                                                 │
│ ┌─────────────────────────────────────────┐   │
│ │ CONTATTI                                │   │
│ │                                         │   │
│ │ Email: [__________________]             │   │
│ │ Telefono: [__________________]          │   │
│ │ Indirizzo: [___________________]        │   │
│ └─────────────────────────────────────────┘   │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Sezioni Condizionali

#### Se "Solo Donatore" Selezionato:

```
┌─────────────────────────────────────────────────┐
│ ✅ CATEGORIA DONATORE (VISIBILE)               │
│                                                 │
│ Categoria: [▼ Seleziona categoria...]         │
│   • Donatore Occasionale                       │
│   • Donatore Ricorrente                        │
│   • Grande Donatore                            │
│                                                 │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ ❌ ISCRIZIONE (NASCOSTA)                        │
└─────────────────────────────────────────────────┘
```

#### Se "Donatore e Socio" Selezionato:

```
┌─────────────────────────────────────────────────┐
│ ❌ CATEGORIA DONATORE (NASCOSTA)                │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ ✅ ISCRIZIONE (VISIBILE)                        │
│                                                 │
│ Tipologia Socio: [▼ Seleziona...]             │
│   • Socio Ordinario - €50,00                   │
│   • Socio Sostenitore - €100,00                │
│   • Socio Onorario - €0,00                     │
│                                                 │
│ Data Iscrizione: [____-__-__]                  │
│ Data Scadenza: [____-__-__]                    │
│ Quota Annuale: [50,00] (auto-calcolata)        │
│                                                 │
│ Stato: [▼ Attivo]                              │
│   • Attivo                                     │
│   • Sospeso                                    │
│   • Scaduto                                    │
│   • Inattivo                                   │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 📊 Tabella Donatori

### Layout Colonne

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│ ☑ │ Nome Completo    │ Email         │ Telefono    │ Tipo    │ Tipologia Socio │
├───┼──────────────────┼───────────────┼─────────────┼─────────┼─────────────────┤
│ ☐ │ Mario Rossi      │ mario@...     │ 333-123...  │ [Socio] │ Socio Ordinario │
│ ☐ │ [foto]           │               │             │         │                 │
├───┼──────────────────┼───────────────┼─────────────┼─────────┼─────────────────┤
│ ☐ │ Laura Bianchi    │ laura@...     │ 340-456...  │[Donator]│       -         │
│ ☐ │ [foto]           │               │             │         │                 │
└───┴──────────────────┴───────────────┴─────────────┴─────────┴─────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│ Categoria        │ Stato    │ Data Iscrizione │ Quota     │ Totale Donato │
├──────────────────┼──────────┼─────────────────┼───────────┼───────────────┤
│        -         │ [Attivo] │ 01/01/2024      │ €50,00    │ €250,00       │
│                  │          │                 │           │               │
├──────────────────┼──────────┼─────────────────┼───────────┼───────────────┤
│ Don. Ricorrente  │    -     │       -         │     -     │ €150,00       │
│                  │          │                 │           │               │
└──────────────────┴──────────┴─────────────────┴───────────┴───────────────┘
```

### Legenda Badge

```
┌─────────────────────────────────────┐
│ TIPO DONATORE:                      │
│                                     │
│ [Socio]    → Donatore e Socio      │
│ [Donatore] → Solo Donatore         │
│                                     │
├─────────────────────────────────────┤
│ STATO (solo per soci):              │
│                                     │
│ [Attivo]   → Socio attivo          │
│ [Sospeso]  → Socio sospeso         │
│ [Scaduto]  → Socio scaduto         │
│ [Inattivo] → Socio inattivo        │
└─────────────────────────────────────┘
```

---

## 💰 Form Pagamento

### Prima
```
Socio: [▼ Seleziona Socio...]
```

### Dopo
```
Donatore: [▼ Seleziona Donatore...]
```

---

## 🎪 Form Evento - Partecipanti

### Prima
```
Aggiungi Partecipante: [▼ Seleziona un socio...]
```

### Dopo
```
Aggiungi Partecipante: [▼ Seleziona un donatore...]
```

---

## 📈 Dashboard

### Card Statistiche

#### Prima
```
┌────────────┐ ┌────────────┐ ┌────────────┐
│  Totale    │ │   Soci     │ │   Soci     │
│   Soci     │ │  Attivi    │ │  Scaduti   │
│            │ │            │ │            │
│    150     │ │    120     │ │     30     │
└────────────┘ └────────────┘ └────────────┘
```

#### Dopo
```
┌────────────┐ ┌────────────┐ ┌────────────┐
│  Totale    │ │  Donatori  │ │  Donatori  │
│  Donatori  │ │   Attivi   │ │  Scaduti   │
│            │ │            │ │            │
│    150     │ │    120     │ │     30     │
└────────────┘ └────────────┘ └────────────┘
```

### Grafici

```
┌─────────────────────────────────────────────────┐
│ Distribuzione Donatori per Stato                │
│                                                 │
│     [Grafico a Torta]                          │
│                                                 │
│  • Attivo: 80%                                 │
│  • Scaduto: 15%                                │
│  • Sospeso: 3%                                 │
│  • Inattivo: 2%                                │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│ Nuovi Donatori (Ultimi 12 Mesi)                │
│                                                 │
│     [Grafico a Barre]                          │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 📤 Export CSV

### Headers Aggiornati

```
Donatore ID, Nome Donatore, Email, Telefono, ...
1, Mario Rossi, mario@example.com, 333-1234567, ...
2, Laura Bianchi, laura@example.com, 340-9876543, ...
```

---

## 🔄 Workflow Utente

### Scenario 1: Creazione Donatore Semplice

1. Click "Aggiungi Donatore"
2. Compilare dati anagrafici
3. Selezionare "Solo Donatore" dal dropdown
4. **Appare** campo "Categoria Donatore"
5. Selezionare categoria (es. "Donatore Occasionale")
6. Salvare
7. ✅ Donatore creato con categoria donatore

### Scenario 2: Creazione Socio

1. Click "Aggiungi Donatore"
2. Compilare dati anagrafici
3. Selezionare "Donatore e Socio" dal dropdown
4. **Appare** sezione "Iscrizione"
5. Selezionare tipologia socio (es. "Socio Ordinario")
6. Quota si compila automaticamente (€50,00)
7. Inserire date iscrizione/scadenza
8. Selezionare stato
9. Salvare
10. ✅ Socio creato con tipologia e quota

### Scenario 3: Conversione Donatore → Socio

1. Aprire donatore esistente (tipo: Solo Donatore)
2. Cambiare dropdown da "Solo Donatore" a "Donatore e Socio"
3. **JavaScript nasconde** campo categoria donatore
4. **JavaScript mostra** sezione iscrizione
5. Compilare dati socio
6. Salvare
7. ✅ Categoria donatore rimossa, tipologia socio assegnata

---

## 🎨 Stili e Colori (CSS)

### Badge Tipo Donatore

```css
/* Socio - Azzurro */
.fg-badge.fg-stato-attivo {
    background: #0073aa;
    color: white;
}

/* Donatore - Grigio */
.fg-badge {
    background: #7e8993;
    color: white;
}
```

### Sezioni Condizionali

```css
/* Categoria Donatore */
.fg-categoria-donatore-section {
    display: none; /* Nascosta di default */
}

/* Iscrizione */
.fg-iscrizione-section {
    display: block; /* Visibile di default (anche_socio) */
}
```

---

## ⚙️ Comportamento JavaScript

### Event Listener

```javascript
// Quando cambia il tipo donatore
$('#fg_tipo_donatore').on('change', function() {
    var tipo = $(this).val();
    
    if (tipo === 'solo_donatore') {
        // Mostra categoria donatore
        $('.fg-categoria-donatore-section').show();
        // Nascondi iscrizione
        $('.fg-iscrizione-section').hide();
    } else {
        // Nascondi categoria donatore
        $('.fg-categoria-donatore-section').hide();
        // Mostra iscrizione
        $('.fg-iscrizione-section').show();
    }
});
```

---

## 📱 Responsive Design

Tutte le modifiche mantengono la responsività esistente del plugin. Le sezioni condizionali si adattano automaticamente a schermi mobile e tablet.

---

## ✨ Animazioni (Opzionale)

Per migliorare UX, si potrebbero aggiungere transizioni CSS:

```css
.fg-categoria-donatore-section,
.fg-iscrizione-section {
    transition: opacity 0.3s ease;
}
```

---

## 🔍 Dettagli Visivi Importanti

1. **Icone Menu**
   - Donatori: `dashicons-groups` (invariato)
   
2. **Colori Badge**
   - Socio: Azzurro (#0073aa)
   - Donatore: Grigio (#7e8993)
   - Attivo: Verde
   - Scaduto: Rosso
   - Sospeso: Arancione
   
3. **Form Layout**
   - Sezioni con bordi
   - Titoli sezioni in grassetto
   - Campi ben spaziati
   - Descrizioni in grigio chiaro

4. **Tabella**
   - Righe alternate (zebra striping)
   - Hover effect
   - Badge inline
   - Link cliccabili

---

**Nota**: Questa è una guida visuale testuale. Per screenshot reali, il plugin deve essere installato in un ambiente WordPress.
