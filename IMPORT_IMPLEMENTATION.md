# Import Feature - Technical Implementation

## Overview

Questa feature aggiunge la capacità di importare donatori/soci da file CSV e XLSX con:
- UI interattiva multi-step
- Mapping flessibile delle colonne
- Validazione robusta dei dati
- Preview e rollback capability
- Gestione errori completa

## Architecture

### Backend (PHP)

#### Classe: `Friends_Gestionale_Import`
**File**: `friends_gestionale/includes/class-import.php`

Responsabilità:
- Gestione upload file
- Parsing CSV e XLSX
- Validazione dati
- Creazione/aggiornamento post WordPress
- Gestione template mapping
- Generazione CSV errori

##### Metodi Principali

```php
// AJAX Handlers
ajax_upload_file()          // Upload e parsing iniziale
ajax_preview_import()       // Anteprima con validazioni
ajax_execute_import()       // Esecuzione import effettiva
ajax_save_mapping_template()  // Salvataggio template
ajax_load_mapping_template()  // Caricamento template

// Parsing
parse_file($file_path, $file_type)
parse_csv($file_path)       // Parsing CSV con auto-detect delimiter
parse_xlsx($file_path)      // Parsing XLSX tramite SimpleXLSX

// Validazione
validate_and_preview_row($row_data, $mapping, $update_existing)
find_donor_by_email($email)

// Operazioni DB
create_or_update_donor($data, $update = false)
generate_error_csv($errors, $headers)
```

##### Endpoints AJAX

| Action | Descrizione | Input | Output |
|--------|-------------|-------|--------|
| `fg_upload_import_file` | Upload file e anteprima | File multipart | import_id, headers, preview_rows |
| `fg_preview_import` | Valida e mostra anteprima | import_id, mapping, options | preview statistiche e rows |
| `fg_execute_import` | Esegue import | import_id, mapping, options | created, updated, skipped, errors |
| `fg_save_mapping_template` | Salva template | template_name, mapping | success message |
| `fg_load_mapping_template` | Carica template | template_key | mapping data |

##### Flusso Dati

```
1. Upload File
   ├─> Salva in wp-content/uploads/fg-import-temp/
   ├─> Parse (CSV o XLSX)
   ├─> Estrai headers
   ├─> Leggi prime 100 righe
   └─> Salva in transient (1h TTL)

2. Preview
   ├─> Recupera da transient
   ├─> Applica mapping
   ├─> Valida ogni riga
   └─> Return statistiche + preview

3. Execute
   ├─> Recupera da transient
   ├─> Applica mapping
   ├─> Per ogni riga:
   │   ├─> Valida
   │   ├─> Crea/Aggiorna post
   │   └─> Log errori
   ├─> Genera CSV errori
   └─> Cleanup (delete file, transient)
```

### Frontend (JavaScript)

#### File: `friends_gestionale/assets/js/import-script.js`

Responsabilità:
- Gestione UI multi-step
- Drag & drop upload
- Costruzione dinamica form mapping
- Auto-mapping colonne
- Comunicazione AJAX con backend
- Visualizzazione risultati

##### Oggetto Principale: `FG_Import`

```javascript
// State
currentStep      // 'upload', 'mapping', 'preview', 'results'
importId         // Identificativo sessione import
headers          // Array intestazioni file
mapping          // Object {field: column_name}
fileData         // Dati file uploaded

// Metodi
init()                          // Inizializzazione
handleFileSelect(file)          // Gestione upload
showMappingStep()              // Costruzione UI mapping
autoMapField(field, header)    // Auto-matching colonne
previewImport()                // Richiesta preview
executeImport()                // Esecuzione import
saveMappingTemplate()          // Salvataggio template
loadMappingTemplate(key)       // Caricamento template
showStep(step)                 // Navigazione tra step
```

##### Auto-Mapping Logic

Il sistema tenta di mappare automaticamente le colonne del file ai campi del donatore usando fuzzy matching:

```javascript
findMatchingHeader(field) {
    var matches = {
        'nome': ['nome', 'name', 'first name', 'firstname'],
        'cognome': ['cognome', 'surname', 'last name', 'lastname'],
        'email': ['email', 'e-mail', 'mail'],
        // ... altre corrispondenze
    };
    
    // Cerca match case-insensitive e parziale
    for (header in headers) {
        if (header matches any pattern in matches[field]) {
            return header;
        }
    }
}
```

### Dependencies

#### SimpleXLSX Library
**File**: `friends_gestionale/includes/lib/simplexlsx.php`  
**Versione**: Latest from [shuchkin/simplexlsx](https://github.com/shuchkin/simplexlsx)  
**Licenza**: MIT  
**Utilizzo**: Parsing file XLSX/XLS

```php
use Shuchkin\SimpleXLSX;

$xlsx = SimpleXLSX::parse($file_path);
$rows = $xlsx->rows();
```

## Data Model

### Custom Post Type: `fg_socio`

Meta fields utilizzati dall'import:

```php
$meta_mapping = [
    'ragione_sociale' => '_fg_ragione_sociale',
    'nome'            => '_fg_nome',
    'cognome'         => '_fg_cognome',
    'email'           => '_fg_email',
    'telefono'        => '_fg_telefono',
    'indirizzo'       => '_fg_indirizzo',
    'citta'           => '_fg_citta',
    'cap'             => '_fg_cap',
    'provincia'       => '_fg_provincia',
    'nazione'         => '_fg_nazione',
    'codice_fiscale'  => '_fg_codice_fiscale',
    'partita_iva'     => '_fg_partita_iva',
    'data_iscrizione' => '_fg_data_iscrizione',
    'note'            => '_fg_note',
    'tipo_donatore'   => '_fg_tipo_donatore',  // 'solo_donatore' | 'anche_socio'
    'tipo_persona'    => '_fg_tipo_persona',    // 'privato' | 'societa'
    'stato'           => '_fg_stato'             // Default: 'attivo'
];
```

### WordPress Options

```php
// Template mapping salvati
'fg_import_mapping_templates' => [
    'template_key' => [
        'name'    => 'Template Name',
        'mapping' => [...],
        'created' => 'YYYY-MM-DD HH:MM:SS'
    ]
]
```

### Transients

```php
// Dati sessione import (TTL: 1 ora)
'fg_import_{import_id}' => [
    'file_path'  => '/path/to/temp/file',
    'file_type'  => 'csv|xlsx',
    'file_name'  => 'original_filename.csv',
    'headers'    => ['Nome', 'Cognome', ...],
    'row_count'  => 1234
]
```

## Validation Rules

Implementate in `validate_and_preview_row()`:

### 1. Nome/Cognome vs Ragione Sociale
```php
if (empty($ragione_sociale)) {
    if (empty($nome) && empty($cognome)) {
        $errors[] = 'Required at least nome/cognome or ragione_sociale';
    } elseif (empty($nome)) {
        $errors[] = 'Nome required';
    } elseif (empty($cognome)) {
        $errors[] = 'Cognome required';
    }
}
// Se ragione_sociale presente, nome/cognome opzionali
```

### 2. Email Format
```php
if (!empty($email) && !is_email($email)) {
    $errors[] = 'Invalid email format';
}
```

### 3. Ruolo Normalization
```php
$mappings = [
    ['socio', 'membro', 'member', 'anche_socio'] => 'anche_socio',
    ['donatore', 'donor', 'solo_donatore']       => 'solo_donatore'
];
$tipo_donatore = normalize($ruolo, $mappings);
```

### 4. Data Iscrizione Default
```php
if ($tipo_donatore === 'anche_socio' && empty($data_iscrizione)) {
    $data_iscrizione = current_time('Y-m-d');
    $warnings[] = 'Data iscrizione set to today';
}
```

### 5. Tipo Persona Auto-detection
```php
$tipo_persona = !empty($ragione_sociale) ? 'societa' : 'privato';
```

## Security

### Nonce Verification
Tutti gli endpoint AJAX richiedono nonce:
```php
check_ajax_referer('fg-import-nonce', 'nonce');
```

### Capability Check
```php
if (!current_user_can('edit_posts')) {
    wp_send_json_error(['message' => 'Insufficient permissions']);
}
```

### File Upload Validation
- Whitelist estensioni: `.csv`, `.xlsx`, `.xls`
- File salvati in directory dedicata con nome univoco
- Cleanup automatico dopo import o timeout (transient expiry)

### Input Sanitization
```php
$import_id = sanitize_text_field($_POST['import_id']);
$mapping = isset($_POST['mapping']) ? $_POST['mapping'] : array();
// Meta values sanitized with sanitize_text_field() before update
```

## Testing

### Unit Tests
**File**: `friends_gestionale/tests/test-import-validation.php`

Eseguire con:
```bash
cd friends_gestionale/tests
php test-import-validation.php
```

Test coverage:
- ✓ Nome/Cognome requirement
- ✓ Ragione Sociale handling
- ✓ Email validation
- ✓ Data iscrizione default
- ✓ Ruolo normalization
- ✓ Combined scenarios

### Manual Testing
**File**: `friends_gestionale/tests/sample-import.csv`

Scenari testati:
1. Privato socio con data
2. Privato donatore senza data
3. Società socio senza data (default oggi)
4. Società con referente

## Performance Considerations

### Upload Phase
- Preview limitato a 100 righe per performance
- Count totale righe efficiente (stream-based)

### Import Phase
- Processing sequenziale riga-per-riga
- No batch operations (WordPress standard)
- Per file molto grandi (>5000 righe): considerare implementazione async job

### Memory Management
- File temporanei eliminati dopo import
- Transients con TTL di 1 ora
- Parse CSV stream-based (no load all in memory)

## Extensibility

### Hooks Disponibili

Possibili hook per future estensioni (da implementare se necessario):

```php
// Before import
apply_filters('fg_import_before_create', $data, $row_data);

// After import
do_action('fg_import_after_create', $post_id, $data);

// Customize validation
apply_filters('fg_import_validation_rules', $errors, $row_data);

// Customize mapping fields
apply_filters('fg_import_mappable_fields', $fields);
```

## Future Enhancements

### Possibili Miglioramenti
1. **Async Import**: Background job per file grandi (WP Cron o Action Scheduler)
2. **Import History**: Log degli import eseguiti
3. **Rollback**: Capacità di annullare un import
4. **Advanced Mapping**: Transformazioni custom (es: uppercase, concatenazione)
5. **Validation Profiles**: Set di regole personalizzabili
6. **Scheduled Imports**: Import automatici da FTP/URL
7. **Conflict Resolution**: UI per gestire manualmente i duplicati
8. **Import Wizard**: Guida interattiva step-by-step
9. **Preview PDF**: Anteprima più ricca dei dati
10. **Multi-sheet XLSX**: Supporto per più fogli

## Troubleshooting

### Debug Mode

Abilitare WP_DEBUG per log dettagliati:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Common Issues

**Issue**: SimpleXLSX class not found  
**Fix**: Verifica che `includes/lib/simplexlsx.php` esista

**Issue**: Upload fails  
**Fix**: Controlla permessi directory `wp-content/uploads/fg-import-temp/`

**Issue**: Timeout during import  
**Fix**: Aumenta `max_execution_time` in php.ini o `.htaccess`

**Issue**: Memory limit  
**Fix**: Aumenta `memory_limit` in php.ini

## References

- [WordPress Post Functions](https://developer.wordpress.org/reference/functions/wp_insert_post/)
- [WordPress AJAX Guide](https://codex.wordpress.org/AJAX_in_Plugins)
- [SimpleXLSX Library](https://github.com/shuchkin/simplexlsx)
- [CSV RFC 4180](https://tools.ietf.org/html/rfc4180)

## Changelog

### Version 1.0.0
- ✅ Initial implementation
- ✅ CSV and XLSX support
- ✅ Column mapping with auto-detection
- ✅ Validation rules
- ✅ Template system
- ✅ Error handling and CSV export
- ✅ Unit tests

## License

This feature is part of Friends of Naples Gestionale plugin and is licensed under GPL v2 or later.
