# Bug Fix: Mapping Initialization Issue

## Issue Report

**Reported by**: @giulianodemar-netizen  
**Date**: 2024-10-20  
**Symptom**: When doing import preview, cognome (last name) and email fields were not being read from the CSV data, even though the column names were detected correctly in the mapping interface.

## Root Cause Analysis

The bug was in the JavaScript file `import-script.js`, specifically in the `showMappingStep()` function (lines 191-198).

### Problematic Code

```javascript
// Set initial mapping
if (self.autoMapField(key, self.headers)) {  // ❌ BUG HERE
    var matched = self.findMatchingHeader(key);
    if (matched) {
        self.mapping[key] = matched;
    }
}
```

### Why It Failed

1. **Incorrect condition**: The code checked `if (self.autoMapField(key, self.headers))`
   - This passed the **entire headers array** to `autoMapField()`
   - `autoMapField()` expects a single header string, not an array
   - The comparison `this.findMatchingHeader(field) === header` would never be true when `header` is an array

2. **Result**: Even though the dropdown selects showed the correct `selected` options (which worked because of lines 182-183), the `self.mapping` object was never properly initialized with the auto-matched values.

3. **Impact**: When the user clicked "Anteprima Import", the AJAX request sent an incomplete or empty `mapping` object, so the backend couldn't map the columns correctly, resulting in empty values for cognome and email.

## Fix Applied

### Commit: `429dc1c`

**Changed Code**:

```javascript
// Set initial mapping based on auto-match
var matched = self.findMatchingHeader(key);  // ✅ FIXED
if (matched) {
    self.mapping[key] = matched;
}
```

### Why This Works

1. **Direct call**: Calls `self.findMatchingHeader(key)` directly without the faulty conditional
2. **Proper initialization**: For each field where a matching header is found, `self.mapping[key]` is set to the matched header name
3. **Complete mapping**: All auto-matched fields (nome, cognome, email, etc.) are now properly stored in the mapping object
4. **Preview works**: When the preview AJAX call is made, the complete mapping is sent to the backend

## Verification

### Before Fix
- User uploads CSV with columns: Nome, Cognome, Email, etc.
- Mapping UI shows correct selections in dropdowns ✓
- `self.mapping` object = `{}` or incomplete ❌
- Preview shows empty values for cognome and email ❌

### After Fix
- User uploads CSV with columns: Nome, Cognome, Email, etc.
- Mapping UI shows correct selections in dropdowns ✓
- `self.mapping` object = `{nome: "Nome", cognome: "Cognome", email: "Email", ...}` ✓
- Preview correctly displays all mapped values ✓

## Testing Instructions

1. **Clear browser cache** to ensure the new JavaScript is loaded
2. Go to: WordPress Admin → Donatori → Importa da file
3. Upload the test file: `friends_gestionale/tests/sample-import.csv`
4. Verify auto-mapping shows correct selections in all dropdowns
5. Click "Anteprima Import"
6. Verify the preview table shows:
   - **Nome column**: Mario, Luigi, Anna (correctly displayed)
   - **Cognome column**: Rossi, Verdi, Bianchi (now correctly displayed) ✅
   - **Email column**: mario.rossi@example.com, luigi.verdi@example.com, anna.bianchi@example.com (now correctly displayed) ✅

## Related Files

- **Fixed**: `friends_gestionale/assets/js/import-script.js` (lines 191-198)
- **Backend** (no changes needed): `friends_gestionale/includes/class-import.php`

## Additional Notes

This was a subtle bug that only affected the mapping initialization. The rest of the import functionality (backend validation, CSV parsing, file upload, etc.) was working correctly. The fix is minimal (8 insertions, 6 deletions) and focused on the specific issue.

## Commit Details

```
Commit: 429dc1c
Message: Fix mapping initialization bug - ensure auto-mapped fields are properly stored in mapping object
Files: friends_gestionale/assets/js/import-script.js
Changes: +8 -6
```
