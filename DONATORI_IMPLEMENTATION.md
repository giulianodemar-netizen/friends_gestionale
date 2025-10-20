# Implementazione Sistema Donatori

## Panoramica delle Modifiche

Questo documento descrive le modifiche apportate al sistema per trasformare la sezione "Soci" in "Donatori", permettendo di distinguere tra donatori semplici e donatori che sono anche soci dell'associazione.

## 1. Modifiche alla Terminologia

### Post Type "Donatori" (fg_socio)
- **Prima**: "Soci" / "Socio"
- **Dopo**: "Donatori" / "Donatore"

Il post type mantiene lo slug interno `fg_socio` per compatibilità con il database esistente, ma tutte le etichette visibili sono state aggiornate.

### File Modificati:
- `includes/class-post-types.php` - Etichette del post type
- `includes/class-meta-boxes.php` - Etichette dei meta box
- `includes/class-admin-dashboard.php` - Etichette dashboard
- `includes/class-export.php` - Etichette esportazione
- `includes/class-shortcodes.php` - Etichette shortcode
- `friends_gestionale.php` - Nome ruolo utente

## 2. Nuova Funzionalità: Tipo Donatore

### Campo "Tipo Donatore" (_fg_tipo_donatore)
Ogni donatore può ora essere classificato come:

1. **Solo Donatore** (`solo_donatore`)
   - Persona che effettua donazioni ma non è socio
   - Visualizza solo il campo "Categoria Donatore"
   
2. **Donatore e Socio** (`anche_socio`)
   - Persona che è sia donatore che socio dell'associazione
   - Visualizza la sezione "Iscrizione" con tipologia socio e quota

### Implementazione UI
- Dropdown "Questa persona è:" nella parte superiore del form
- JavaScript per mostrare/nascondere sezioni in base alla selezione
- Valore predefinito: "anche_socio" (per retrocompatibilità)

## 3. Sistema di Categorie/Tipologie

### Tipologie Socio (fg_categoria_socio)
- **Scopo**: Classificare i soci dell'associazione
- **Visibilità**: Solo per donatori di tipo "anche_socio"
- **Funzionalità**: Include quota associativa automatica
- **Etichette**: Rinominate da "Categorie Soci" a "Tipologie Socio"

### Categorie Donatore (fg_categoria_donatore) - NUOVA
- **Scopo**: Classificare i donatori semplici
- **Visibilità**: Solo per donatori di tipo "solo_donatore"
- **Accesso**: Menu laterale sotto "Donatori"
- **Inizializzazione**: La taxonomy viene registrata automaticamente all'attivazione del plugin
- **Stato Iniziale**: Vuota - le categorie vanno create manualmente dall'amministratore
- **Note**: Non sono previste categorie di default - ogni organizzazione definisce le proprie

### Procedura di Inizializzazione:
1. Il plugin registra automaticamente la taxonomy al caricamento
2. L'amministratore crea le categorie necessarie tramite menu "Donatori" → "Categorie Donatore"
3. Le categorie create sono disponibili nel form donatore per la selezione
4. Esempi di categorie che potrebbero essere create:
   - "Donatore Occasionale"
   - "Donatore Ricorrente"
   - "Grande Donatore"
   - ecc.

## 4. Modifiche alle Colonne della Tabella

### Nuove Colonne:
1. **Tipo** (fg_tipo_donatore)
   - Badge "Socio" per anche_socio
   - Badge "Donatore" per solo_donatore

2. **Tipologia Socio** (fg_tipologia_socio)
   - Mostra la tipologia solo se donatore è anche socio
   - Altrimenti mostra "-"

3. **Categoria** (fg_categoria_donatore)
   - Mostra la categoria solo se donatore non è socio
   - Altrimenti mostra "-"

### Colonne Rimosse:
- taxonomy-fg_categoria_socio (sostituita dalle nuove colonne)

## 5. Logica di Salvataggio

### Save Meta Boxes (class-meta-boxes.php)

```php
// Salvataggio basato sul tipo di donatore
// Include validazione e gestione errori

// Validazione input
$tipo_donatore = isset($_POST['fg_tipo_donatore']) ? sanitize_text_field($_POST['fg_tipo_donatore']) : 'anche_socio';

if ($tipo_donatore === 'anche_socio') {
    // Salva tipologia socio con validazione
    if (isset($_POST['fg_categoria_socio_selector']) && !empty($_POST['fg_categoria_socio_selector'])) {
        $category_id = absint($_POST['fg_categoria_socio_selector']);
        // Verifica che la categoria esista
        $term = get_term($category_id, 'fg_categoria_socio');
        if (!is_wp_error($term) && $term) {
            wp_set_post_terms($post_id, array($category_id), 'fg_categoria_socio', false);
        }
    }
    // Rimuove categoria donatore se presente
    wp_set_post_terms($post_id, array(), 'fg_categoria_donatore', false);
} else {
    // Salva categoria donatore con validazione
    if (isset($_POST['fg_categoria_donatore_selector']) && !empty($_POST['fg_categoria_donatore_selector'])) {
        $donor_category_id = absint($_POST['fg_categoria_donatore_selector']);
        // Verifica che la categoria esista
        $term = get_term($donor_category_id, 'fg_categoria_donatore');
        if (!is_wp_error($term) && $term) {
            wp_set_post_terms($post_id, array($donor_category_id), 'fg_categoria_donatore', false);
        }
    }
    // Rimuove tipologia socio se presente
    wp_set_post_terms($post_id, array(), 'fg_categoria_socio', false);
}
```

Nota: Il codice di produzione include controlli di errore completi usando `is_wp_error()` e validazione dei term IDs.

## 6. Sezioni del Form

### Sezione "Tipo Donatore" (Sempre Visibile)
- Dropdown per selezionare tipo donatore
- Posizionata all'inizio del form per chiarezza

### Sezione "Categoria Donatore" (Condizionale)
- **Visibile**: Solo se tipo = "solo_donatore"
- **Contenuto**: Dropdown categorie donatore
- **CSS**: `display: none` per anche_socio

### Sezione "Iscrizione" (Condizionale)
- **Visibile**: Solo se tipo = "anche_socio"
- **Contenuto**: 
  - Tipologia Socio (con quota automatica)
  - Data Iscrizione
  - Data Scadenza
  - Quota Annuale (readonly)
  - Stato
- **CSS**: `display: none` per solo_donatore

## 7. JavaScript

### admin-script.js
Gestisce il toggle delle sezioni:

```javascript
$('#fg_tipo_donatore').on('change', function() {
    var tipoDonatore = $(this).val();
    
    if (tipoDonatore === 'solo_donatore') {
        $('.fg-categoria-donatore-section').show();
        $('.fg-iscrizione-section').hide();
    } else if (tipoDonatore === 'anche_socio') {
        $('.fg-categoria-donatore-section').hide();
        $('.fg-iscrizione-section').show();
    }
});
```

**Nota AJAX**: Per chiamate AJAX in JavaScript separato, i nonce vengono passati tramite `wp_localize_script()` nel PHP:
```php
// In PHP (friends_gestionale.php)
wp_localize_script('friends-gestionale-admin', 'friendsGestionale', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('friends_gestionale_nonce')
));

// In JavaScript
$.ajax({
    url: friendsGestionale.ajaxUrl,
    data: {
        nonce: friendsGestionale.nonce,
        // altri parametri
    }
});
```

## 8. Dashboard e Statistiche

Tutte le etichette aggiornate:
- "Totale Soci" → "Totale Donatori"
- "Soci Attivi" → "Donatori Attivi"
- "Soci Scaduti" → "Donatori Scaduti"
- "Nuovi Soci Questo Mese" → "Nuovi Donatori Questo Mese"
- Grafici e statistiche mantengono la stessa logica

## 9. Export CSV

### Modifiche:
- Header: "Socio ID" → "Donatore ID"
- Header: "Nome Socio" → "Nome Donatore"
- Filtri: Aggiornati con nuova terminologia
- Pulsante: "Esporta Soci CSV" → "Esporta Donatori CSV"

## 10. Shortcodes

Shortcode rimangono compatibili:
- `[fg_elenco_soci]` - Funziona ancora, etichette aggiornate
- `[fg_dettaglio_socio]` - Funziona ancora, etichette aggiornate
- `[fg_filtro_soci]` - Funziona ancora, etichette aggiornate

## 11. Gestione Pagamenti

### Form Pagamenti:
- Dropdown "Socio:" → "Donatore:"
- Selezione: "Seleziona Socio" → "Seleziona Donatore"
- Backend mantiene `_fg_socio_id` per compatibilità

### Colonne Tabella:
- "Socio" → "Donatore"

## 12. Eventi e Partecipanti

### Form Eventi:
- "Seleziona un socio..." → "Seleziona un donatore..."
- Alert JS: "Seleziona un socio dalla lista" → "Seleziona un donatore dalla lista"
- Alert JS: "Questo socio è già stato aggiunto" → "Questo donatore è già stato aggiunto"

## 13. Compatibilità e Migrazione

### Retrocompatibilità:
- Slug post type rimane `fg_socio`
- Meta field names rimangono invariati
- Taxonomy slug `fg_categoria_socio` mantiene lo stesso nome
- Tutti i donatori esistenti vengono considerati "anche_socio" per default

### Gestione Record Esistenti:
Quando si accede a un donatore esistente senza il campo `_fg_tipo_donatore`:
1. Il form assume "anche_socio" come valore default
2. Al primo salvataggio, il campo viene creato con valore "anche_socio"
3. Le categorie socio esistenti rimangono assegnate
4. Il comportamento è identico al sistema precedente

```php
// Nel render del form (class-meta-boxes.php)
$tipo_donatore = get_post_meta($post->ID, '_fg_tipo_donatore', true);
if (empty($tipo_donatore)) {
    $tipo_donatore = 'anche_socio'; // Default per retrocompatibilità
}
```

### Migrazione Dati:
Non è necessaria una migrazione dati. Il sistema:
1. Assume "anche_socio" come default per record esistenti
2. Mantiene le categorie socio esistenti
3. Le nuove categorie donatore sono vuote inizialmente
4. I record vengono aggiornati progressivamente al salvataggio

## 14. Menu di Amministrazione

### Menu "Donatori":
- Voce principale: "Donatori"
- Sottomenu automatico: "Tipologie Socio" (taxonomy)
- Sottomenu automatico: "Categorie Donatore" (taxonomy)

Nota: WordPress aggiunge automaticamente i submenu per le taxonomy registrate.

## 15. Ruolo Utente

### Ruolo Plugin Manager:
- **Nome Precedente**: "Friends Gestionale - Gestore Soci"
- **Nome Nuovo**: "Friends Gestionale - Gestore Donatori"
- Capabilities rimangono invariate

## 16. Testing Raccomandato

### Test Manuali Richiesti:
1. **Creazione Donatore Solo Donatore**
   - Verifica che sezione iscrizione sia nascosta
   - Verifica che categoria donatore sia visibile
   - Verifica salvataggio categoria donatore

2. **Creazione Donatore e Socio**
   - Verifica che sezione iscrizione sia visibile
   - Verifica che categoria donatore sia nascosta
   - Verifica salvataggio tipologia socio e quota

3. **Cambio Tipo Donatore**
   - Da solo_donatore a anche_socio
   - Verifica che categorie siano aggiornate correttamente

4. **Tabella Donatori**
   - Verifica colonna "Tipo" mostra badge corretti
   - Verifica colonna "Tipologia Socio" solo per soci
   - Verifica colonna "Categoria" solo per donatori semplici

5. **Dashboard**
   - Verifica che statistiche funzionino correttamente
   - Verifica grafici e conteggi

6. **Export CSV**
   - Verifica che export includa tutti i campi
   - Verifica encoding UTF-8

7. **Pagamenti**
   - Verifica associazione donatore a pagamento
   - Verifica dropdown donatori funzioni

8. **Eventi**
   - Verifica aggiunta partecipanti
   - Verifica dropdown donatori

## 17. File Modificati - Riepilogo

1. `friends_gestionale/includes/class-post-types.php`
   - Post type labels
   - Taxonomy labels e registrazione nuova taxonomy
   - Table columns
   - Column rendering

2. `friends_gestionale/includes/class-meta-boxes.php`
   - Meta box labels
   - Form donatore con tipo donatore
   - Sezioni condizionali
   - Save logic per taxonomies

3. `friends_gestionale/assets/js/admin-script.js`
   - Toggle sezioni basato su tipo donatore
   - Alert messages aggiornati

4. `friends_gestionale/includes/class-admin-dashboard.php`
   - Tutte le etichette aggiornate

5. `friends_gestionale/includes/class-export.php`
   - Labels export aggiornate

6. `friends_gestionale/includes/class-shortcodes.php`
   - Labels shortcode aggiornate

7. `friends_gestionale/friends_gestionale.php`
   - Ruolo utente aggiornato
   - Commenti menu aggiornati

## 18. Note Tecniche

### Default Values:
- `_fg_tipo_donatore`: Default "anche_socio" se non impostato
- Questo garantisce retrocompatibilità con donatori esistenti

### CSS Classes:
- `.fg-categoria-donatore-section`: Sezione categoria donatore
- `.fg-iscrizione-section`: Sezione iscrizione (solo soci)
- Toggle via `display: none/block`

### Database Impact:
- **Nuova meta key**: `_fg_tipo_donatore`
- **Nuova taxonomy**: `fg_categoria_donatore`
- **No migrazione richiesta**: Tutti i campi esistenti rimangono invariati

## 19. Conclusioni

L'implementazione è stata completata con successo mantenendo la compatibilità con i dati esistenti. Il sistema ora supporta:

✅ Distinzione tra donatori semplici e soci  
✅ Gestione separata delle categorie  
✅ UI condizionale basata sul tipo  
✅ Retrocompatibilità completa  
✅ Terminologia aggiornata in tutto il gestionale  

Per qualsiasi domanda o supporto, fare riferimento alla documentazione WordPress o aprire una issue su GitHub.
