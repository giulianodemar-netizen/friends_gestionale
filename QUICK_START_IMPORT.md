# Quick Start - Import Donatori

## 🚀 Get Started in 5 Minutes

### Step 1: Accedi alla Funzionalità
```
WordPress Admin → Donatori → Importa da file
```

### Step 2: Prepara il File

**Formato CSV** (raccomandato):
```csv
Nome,Cognome,Email,Telefono,Ruolo
Mario,Rossi,mario@example.com,3331234567,socio
Luigi,Verdi,luigi@example.com,3337654321,donatore
```

**O XLSX**: File Excel standard con prima riga = intestazioni

### Step 3: Upload
- Trascina file nell'area
- **O** Clicca "Scegli file"

### Step 4: Mapping (Auto)
Il sistema mappa automaticamente. Verifica e conferma.

### Step 5: Anteprima
Controlla statistiche e righe. Se OK, procedi.

### Step 6: Esegui
Clicca "Esegui Import" e attendi completamento.

### Step 7: Verifica
Vai su Donatori e controlla i record importati.

---

## 📋 Regole Importanti

### Nome/Cognome vs Ragione Sociale

**Per Privati** (persone):
```csv
Nome,Cognome,Ragione Sociale
Mario,Rossi,           ← OK (privato)
Mario,,                ← ERRORE (manca cognome)
,Rossi,                ← ERRORE (manca nome)
```

**Per Società**:
```csv
Nome,Cognome,Ragione Sociale
,,,Tech Solutions SRL  ← OK (società)
Mario,Rossi,Azienda    ← OK (società + referente)
```

### Ruolo (Tipo)

Valori accettati (case-insensitive):
- **Socio**: `socio`, `Socio`, `membro`, `member`, `anche_socio`
- **Donatore**: `donatore`, `Donatore`, `donor`, `solo_donatore`

### Data Iscrizione

- **Soci**: Se vuota → oggi (default automatico)
- **Donatori**: Se vuota → rimane vuota

---

## ✅ File Esempio

Usa il file incluso per testare:
```bash
friends_gestionale/tests/sample-import.csv
```

Contiene esempi di:
- Privati soci con data
- Privati donatori
- Società
- Vari scenari

---

## 🧪 Test Rapido

```bash
cd friends_gestionale/tests
php test-import-validation.php
```

Output atteso: `42/42 PASS`

---

## 🎨 UI Demo

Apri in browser per vedere l'interfaccia:
```bash
friends_gestionale/tests/ui-demo.html
```

---

## 📚 Documentazione Completa

- **Utenti**: `friends_gestionale/tests/IMPORT_GUIDE.md`
- **Dev**: `IMPORT_IMPLEMENTATION.md`
- **Overview**: `IMPORT_FEATURE_README.md`

---

## 🆘 Troubleshooting Quick

### "File vuoto"
✓ Verifica che file contenga dati e prima riga = intestazioni

### "Email non valida"
✓ Controlla formato email (deve contenere @ e dominio)

### "Nome richiesto" per società
✓ Compila campo "Ragione Sociale"

### Caratteri strani (Ã¨, Ã )
✓ Salva CSV come UTF-8 (non ISO-8859-1)

### "Libreria XLSX non disponibile"
✓ Usa CSV invece di XLSX, oppure verifica che `includes/lib/simplexlsx.php` esista

---

## 💡 Tips

1. **Primo import**: Usa file piccolo (10-20 righe) per testare
2. **Template**: Salva mapping se importi regolarmente dallo stesso formato
3. **Backup**: Fai backup DB prima di import grandi (>1000 righe)
4. **Duplicati**: Decidi se aggiornare o creare nuovi record
5. **Errori**: Scarica CSV errori per correggere e re-importare

---

## 🎯 Campi Chiave

| Campo | Obbligatorio | Note |
|-------|--------------|------|
| Nome + Cognome | Sì* | *Se no Ragione Sociale |
| Ragione Sociale | Sì* | *Se no Nome+Cognome |
| Email | No | Validato se presente |
| Ruolo | No | Default: socio |
| Data Iscrizione | No | Default: oggi (solo soci) |

---

**That's it!** Ora sei pronto per importare donatori. 🚀

Per dubbi, consulta `IMPORT_GUIDE.md` per guida completa.
