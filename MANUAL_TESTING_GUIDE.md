# Manual Testing Guide for PR: Friends Gestionale Enhancements

## Overview
This PR adds 4 new features to the Friends Gestionale plugin. Use this guide to manually verify each feature.

---

## Feature 1: Import Skip Existing Records

### How to Test:
1. Navigate to **Friends Gestionale → Importa da file**
2. Upload a CSV/XLSX file with donor records
3. Complete the mapping step (Step 2)
4. In Step 2, observe the new **"Ignora record esistenti (per email)"** checkbox
5. Check the tooltip (ⓘ icon) - should show: "Se selezionato, i record con email già presenti non verranno aggiornati né sovrascritti; verranno saltati."

### Test Scenarios:

**Scenario A: Skip Existing Records (NEW behavior)**
1. Import a file with some records that have emails already in the database
2. ✅ CHECK the "Ignora record esistenti" checkbox
3. Click "Anteprima Import"
4. ✅ VERIFY: Records with existing emails show status "Salta" (not "Aggiorna")
5. Click "Esegui Import"
6. ✅ VERIFY: 
   - Existing records are NOT modified
   - Only new records are created
   - Skipped count matches number of existing emails

**Scenario B: Update Existing Records (Original behavior)**
1. Import the same file again
2. ❌ UNCHECK "Ignora record esistenti"
3. ✅ CHECK "Aggiorna i record esistenti"
4. ✅ VERIFY: Records with existing emails show status "Aggiorna"
5. Execute import
6. ✅ VERIFY: Existing records are updated with new data

**Scenario C: Create Duplicates**
1. Import the same file again
2. ❌ UNCHECK both checkboxes
3. ✅ VERIFY: Records show status "Crea nuovo" with warning about duplicates

---

## Feature 2: Improved Tooltip for "Tipo Socio/Donatore"

### How to Test:
1. Navigate to **Friends Gestionale → Importa da file**
2. Upload a CSV/XLSX file
3. On the mapping step (Step 2), find the **"Ruolo (socio/donatore)"** field
4. Hover over or click the ⓘ tooltip icon

### Expected Behavior:
✅ VERIFY: Tooltip shows complete text:
"Se contiene 'socio' o 'donatore' (case-insensitive), il record verrà classificato rispettivamente come Socio o Donatore. Esempio: 'socio sostenitore' => Socio; 'donatore occasionale' => Donatore."

### Old Behavior (for comparison):
❌ OLD: Tooltip only showed "Se contiene..."

---

## Feature 3: Statistics Date Range Filter

### How to Test:
1. Navigate to **Friends Gestionale → Statistiche**
2. Observe the new **"Filtro Periodo"** section at the top

### Test Scenarios:

**Scenario A: Filter by Date Range**
1. Enter a start date (e.g., 2024-01-01)
2. Enter an end date (e.g., 2024-06-30)
3. Click **"Applica Filtro"**
4. ✅ VERIFY:
   - All charts update to show only data within the date range
   - Active filter indicator appears: "Filtro attivo: Dal 2024-01-01 al 2024-06-30"
   - "Rimuovi Filtro" button appears
   - Payment trends chart shows only months in range
   - Top donors ranking shows only donations in period
   - All other statistics respect the date filter

**Scenario B: Filter by Start Date Only**
1. Enter only a start date (e.g., 2024-03-01)
2. Leave end date empty
3. Click **"Applica Filtro"**
4. ✅ VERIFY: Shows data from March 2024 onwards

**Scenario C: Filter by End Date Only**
1. Clear start date
2. Enter only an end date (e.g., 2024-06-30)
3. Click **"Applica Filtro"**
4. ✅ VERIFY: Shows data up to June 2024

**Scenario D: Remove Filter**
1. With a filter active, click **"Rimuovi Filtro"**
2. ✅ VERIFY: Returns to default view (last 12 months)

**Scenario E: Invalid Date Format**
1. Enter invalid date like "01/01/2024" or "2024-13-01"
2. Click **"Applica Filtro"**
3. ✅ VERIFY: Error message appears: "Formato data non valido. Usa YYYY-MM-DD."

**Scenario F: Invalid Date Range**
1. Enter start date: 2024-12-31
2. Enter end date: 2024-01-01 (before start)
3. Click **"Applica Filtro"**
4. ✅ VERIFY: Error message: "La data inizio deve essere precedente o uguale alla data fine."

**Scenario G: Same Date**
1. Enter same date for both (e.g., 2024-06-15)
2. ✅ VERIFY: Shows only data from that single day (valid scenario)

### Charts to Verify:
- ✅ Andamento Pagamenti (line chart)
- ✅ Distribuzione Donatori per Stato (pie chart)
- ✅ Donazioni per Tipo (pie chart)
- ✅ Nuovi Donatori (line chart)
- ✅ Distribuzione Metodi di Pagamento (pie chart)
- ✅ Top Donatori (table)

---

## Feature 4: New Role "Donatori Visualizzatore"

### How to Test:

**Step 1: Verify Role Creation**
1. Navigate to **Utenti → Tutti gli utenti**
2. Edit any user or create a test user
3. In the "Ruolo" dropdown, look for **"Donatori Visualizzatore"**
4. ✅ VERIFY: Role is available in the list

**Step 2: Assign Role and Test Permissions**
1. Assign "Donatori Visualizzatore" role to a test user
2. Log in as that user (or use a different browser/incognito)

**What the user CAN do (READ-ONLY):**
- ✅ View Soci (Donatori) list
- ✅ View individual Socio details
- ✅ View Pagamenti list and details
- ✅ View Raccolte Fondi list and details
- ✅ View Eventi list and details
- ✅ View Statistiche page
- ✅ View Dashboard

**What the user CANNOT do:**
- ❌ Create new Soci/Pagamenti/Raccolte/Eventi
- ❌ Edit existing records
- ❌ Delete records
- ❌ Import data (Import menu should not be visible)
- ❌ Export data (if export requires higher permissions)
- ❌ Modify settings

**Step 3: Test Specific Restrictions**
1. Go to Soci list
2. ✅ VERIFY: Cannot see "Aggiungi Nuovo" button
3. Click on a Socio to view
4. ✅ VERIFY: Cannot see "Modifica" or "Elimina" options
5. Try to access import page directly via URL
6. ✅ VERIFY: Access denied (403 or redirect)

**Step 4: Compare with Gestore Donatori Role**
1. Log in with a user having "Friends Gestionale - Gestore Donatori" role
2. ✅ VERIFY: This user CAN create, edit, delete, and import
3. Confirms role separation works correctly

---

## Automated Tests

All features include automated unit tests. To run them:

```bash
cd friends_gestionale/tests

# Test import skip functionality
php test-skip-existing-import.php

# Test viewer role
php test-viewer-role.php

# Test statistics date filter
php test-statistics-date-filter.php

# Test existing import validation
php test-import-validation.php
```

Expected: All tests should pass (79/79 total)

---

## Browser Compatibility Testing

Test in the following browsers:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

Test responsive behavior:
- [ ] Desktop (1920x1080)
- [ ] Tablet (768px width)
- [ ] Mobile (375px width)

---

## Regression Testing

Verify existing functionality still works:
- [ ] Import without new options still works as before
- [ ] Statistics without date filter shows last 12 months
- [ ] Existing role "Friends Gestionale - Gestore Donatori" still works
- [ ] Dashboard displays correctly
- [ ] Export functionality works
- [ ] Email notifications work

---

## Documentation Verification

Check that documentation is updated:
- [ ] README.md includes all 4 features
- [ ] CHANGELOG.md documents version 1.1.0 changes
- [ ] Tooltips are clear and helpful
- [ ] Error messages are user-friendly

---

## Performance Testing

For large datasets:
- [ ] Import with skip_existing doesn't timeout
- [ ] Statistics with date filter loads in reasonable time (<5s)
- [ ] No memory issues with large result sets

---

## Security Testing

- [ ] Date inputs are properly sanitized
- [ ] SQL injection prevented in date filters
- [ ] Role capabilities properly checked
- [ ] Viewer role cannot bypass restrictions via direct URLs
- [ ] Import skip feature doesn't expose sensitive data

---

## Notes for Reviewer

1. All features maintain backward compatibility
2. Default behavior unchanged unless new options are used
3. 79/79 automated tests passing
4. Code follows WordPress coding standards
5. Proper escaping and sanitization implemented
6. Localization-ready (uses __() functions)

---

## Known Limitations

- Date filter on statistics page uses GET parameters (shareable URLs)
- Date format must be YYYY-MM-DD (ISO format)
- Viewer role is read-only; cannot customize specific permissions
- Import skip is based on email field only

---

## Screenshots to Capture

Please capture screenshots of:
1. ✅ Import page with new "Ignora record esistenti" checkbox
2. ✅ Improved tooltip on "Ruolo" field
3. ✅ Statistics page with date filter UI
4. ✅ Statistics page with active filter
5. ✅ User role dropdown showing "Donatori Visualizzatore"
6. ✅ Viewer user's restricted interface (no edit buttons)
7. ✅ Date validation error messages

---

## Post-Merge Tasks

After merging this PR:
- [ ] Update version number in plugin header to 1.1.0
- [ ] Tag release as v1.1.0
- [ ] Deploy to production/staging
- [ ] Notify users of new features
- [ ] Update user documentation/help pages

---

**Questions or Issues?**
Contact the development team or refer to test files for technical details.
