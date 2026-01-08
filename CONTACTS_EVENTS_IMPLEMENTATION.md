# Implementation Summary: Contacts and Event-Fundraiser Linking

## Overview
This implementation adds two major features to the Friends Gestionale plugin as requested in the issue.

## 1. New Contacts Section (Contatti)

### What was added:
- **New Custom Post Type**: `fg_contatto` registered with menu icon, labels, and capabilities
- **Independent Section**: Completely separate from donors/members (fg_socio)
- **Admin Interface**: Full CRUD functionality with custom meta boxes
- **Custom Columns**: Type, Email, Phone, and Company columns in admin list view

### Contact Fields:
- Nome (Name) - Required
- Tipo Contatto (Type) - Dropdown: Fornitore, Istituzione, Partner, Sponsor, Altro
- Azienda/Organizzazione (Company/Organization)
- Email
- Telefono (Phone)
- Indirizzo (Address)
- Note (Notes)

### Independence Guarantee:
- Contacts do NOT appear in payment donor dropdowns (only fg_socio)
- Contacts do NOT appear in event participant lists (only fg_socio)
- Contacts have NO interaction with payments or events
- Completely separate data structure and meta fields

## 2. Event-to-Fundraiser Linking

### What was added:
- **Multi-select Field**: Associates multiple events with a single fundraiser
- **Automatic Calculation**: Total collected from event payments automatically added to fundraiser total
- **Detailed Breakdown**: Shows individual totals for each associated event
- **Visual Feedback**: Green badges and clear totals for better UX

### How it works:
1. In fundraiser edit page, select one or more events from the multi-select field
2. System automatically queries all payments linked to those events
3. Calculates and displays:
   - Total from each individual event
   - Combined total from all events
   - Grand total (Platform + Extra + Events)
4. Progress bar reflects the complete total

### Technical Implementation:
- **Single Query Optimization**: Uses `IN` clause to fetch all event payments at once (no N+1)
- **Caching**: Payment data cached in memory to avoid duplicate queries
- **Performance**: Commented rationale for query approach

## Security

All code follows WordPress security best practices:
- ✅ Nonce verification (`wp_verify_nonce`)
- ✅ Input sanitization (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`)
- ✅ Output escaping (`esc_attr`, `esc_html`, `esc_textarea`)
- ✅ Array sanitization (`array_map('intval', ...)`)
- ✅ Type checking (`is_array()`)

## Testing

Comprehensive test suite included:
- Contact post type registration
- Contact field independence
- Event-to-fundraiser linking logic
- Total calculation from multiple events
- Contacts isolation from payments/events

Run with: `php tests/test-contacts-and-event-linking.php`

## Files Modified

1. **friends_gestionale/includes/class-post-types.php**
   - Added fg_contatto post type registration
   - Added contatto admin columns (set_contatto_columns, render_contatto_columns)

2. **friends_gestionale/includes/class-meta-boxes.php**
   - Added fg_contatto to hide_default_editor list
   - Added contatto meta box registration
   - Added render_contatto_info_meta_box function
   - Added contatto save logic
   - Modified raccolta meta box to include eventi_associati field
   - Optimized event payment queries
   - Added event totals display in fundraiser page
   - Added eventi_associati save logic

3. **friends_gestionale/tests/test-contacts-and-event-linking.php** (NEW)
   - Mock test suite validating implementation logic

## Backward Compatibility

- ✅ No breaking changes to existing functionality
- ✅ All existing post types continue to work as before
- ✅ Fundraisers without associated events work normally
- ✅ New fields are optional and don't affect existing data

## Database Schema

### New Meta Fields:

**fg_contatto post meta:**
- `_fg_nome_contatto` (text)
- `_fg_tipo_contatto` (text)
- `_fg_azienda` (text)
- `_fg_email_contatto` (text)
- `_fg_telefono_contatto` (text)
- `_fg_indirizzo_contatto` (text)
- `_fg_note_contatto` (text)

**fg_raccolta post meta:**
- `_fg_eventi_associati` (array of event IDs)

## Usage

### Creating a Contact:
1. Go to WordPress Admin → Contatti → Add New
2. Fill in contact name (required)
3. Select type from dropdown
4. Add email, phone, address, notes as needed
5. Publish

### Linking Events to Fundraiser:
1. Go to WordPress Admin → Raccolte Fondi → Edit a fundraiser
2. Find "Eventi Associati" field
3. Hold Ctrl (Cmd on Mac) and click to select multiple events
4. Save - totals automatically calculated
5. View "Dettaglio Eventi Associati" section for breakdown

## Performance Considerations

- Single optimized query for all event payments (uses IN clause)
- Cached results prevent duplicate queries
- Only runs on fundraiser edit page (admin only, not frontend)
- Reasonable memory usage for typical association volumes

## Future Enhancements (Not Included)

Possible future improvements:
- Export functionality for contacts
- Contact categories/tags
- Email integration for contacts
- Event payment limit/pagination if volumes become very large
- AJAX refresh of totals when changing event selection

## Testing Checklist

- [x] PHP syntax validation
- [x] Security best practices
- [x] Code review feedback addressed
- [x] Mock test suite passes
- [x] N+1 query optimization
- [x] Documentation added
- [ ] Manual WordPress testing (requires WordPress installation)
- [ ] UI/UX verification (requires WordPress installation)

## Notes

The implementation is complete and ready for testing in a WordPress environment. All code follows plugin conventions and security standards.
