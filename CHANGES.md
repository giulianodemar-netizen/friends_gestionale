# Changes Made - Payment Form Enhancements and User Role

## Summary
This implementation adds several enhancements to the payment management system as requested:

1. ✅ WordPress title/description editor is hidden in payment add/edit pages
2. ✅ Event selection logic for payments with "Altro Evento" option
3. ✅ Member category selection for "Rinnovo Quota" payment type
4. ✅ New user role "Friends Gestionale - Gestore Pagamenti" for payment management only

## Detailed Changes

### 1. Hide WordPress Title/Description in Payment Pages
**Status:** Already implemented (no changes needed)
- The `hide_default_editor()` method in `class-meta-boxes.php` already removes title and editor support for `fg_pagamento` post type (line 32)

### 2. Event Selection Logic for Payments
**Files Modified:** 
- `friends_gestionale/includes/class-meta-boxes.php`
- `friends_gestionale/assets/js/admin-script.js`

**Changes:**
- Added three new meta fields to payments:
  - `_fg_evento_id`: Stores the selected event ID or "altro_evento"
  - `_fg_evento_custom`: Stores custom event title when "Altro Evento" is selected
  - `_fg_categoria_socio_id`: Stores selected member category for quota renewals

- In `render_pagamento_info_meta_box()`:
  - Added query to fetch all events from `fg_evento` post type
  - Added query to fetch all member categories from `fg_categoria_socio` taxonomy
  - Added hidden field `#fg_evento_field` containing event dropdown
  - Added "Altro Evento" option in event dropdown
  - Added hidden field `#fg_evento_custom_field` for manual event title input
  - Fields are hidden by default and shown via JavaScript based on payment type

- In `save_meta_boxes()`:
  - Added saving logic for `fg_evento_id`, `fg_evento_custom`, and `fg_categoria_socio_id` fields

- In `admin-script.js`:
  - Added `togglePaymentFields()` function to show/hide fields based on payment type
  - When "evento" is selected: Shows event dropdown
  - When "Altro Evento" is selected in event dropdown: Shows custom event title input
  - When "quota" is selected: Shows member category dropdown
  - Function runs on page load and when payment type changes

### 3. Member Category Selection for Quota Renewals
**Files Modified:** Same as above

**Changes:**
- Added `#fg_categoria_socio_field` containing member category dropdown
- Loads all categories from `fg_categoria_socio` taxonomy
- Field is hidden by default and shown when payment type is "quota"
- Category ID is saved to `_fg_categoria_socio_id` meta field

### 4. Payment Manager User Role
**Files Modified:**
- `friends_gestionale/friends_gestionale.php`
- `friends_gestionale/includes/class-post-types.php`

**Changes:**

#### Role Creation (`friends_gestionale.php`):
- Added `create_payment_manager_role()` method called during plugin activation
- Created new role `fg_payment_manager` with display name "Friends Gestionale - Gestore Pagamenti"
- Role capabilities:
  - Can read, edit, publish, and delete payment posts
  - Can upload files (for attachments)
  - Full CRUD capabilities on `fg_pagamento` custom post type
  - **Cannot** access other WordPress areas (posts, pages, media, etc.)
  - **Cannot** edit others' posts or private posts outside payments

#### Custom Capabilities (`class-post-types.php`):
- Changed `fg_pagamento` post type to use custom capability type: `array('fg_pagamento', 'fg_pagamentos')`
- Added `map_meta_cap` => true to enable proper capability mapping
- This allows fine-grained control over who can access payments

#### Menu Restrictions (`friends_gestionale.php`):
- Added `restrict_payment_manager_menu()` method hooked to `admin_menu` with priority 999
- Removes all WordPress default menus for payment manager users:
  - Dashboard, Posts, Media, Pages, Comments, Appearance, Plugins, Users, Tools, Settings
- Removes other Friends Gestionale post type menus:
  - Soci, Raccolte Fondi, Eventi
- Only Pagamenti menu remains visible

#### Access Control (`friends_gestionale.php`):
- Added `redirect_payment_manager()` method hooked to `admin_init`
- Redirects payment managers from dashboard to payments listing
- Prevents direct URL access to other post types
- Shows "Non hai i permessi per accedere a questa pagina" error if unauthorized access attempted

## How to Use

### For Administrators:

#### Creating a Payment Manager User:
1. Go to WordPress Admin → Users → Add New
2. Fill in user details
3. In the "Role" dropdown, select "Friends Gestionale - Gestore Pagamenti"
4. Save the user

#### Payment Manager Will Have Access To:
- View all payments
- Add new payments
- Edit existing payments
- Delete payments
- Upload files for attachments

#### Payment Manager Will NOT Have Access To:
- Other WordPress content (posts, pages, media)
- Other Friends Gestionale sections (members, fundraising, events)
- WordPress settings or configuration
- User management
- Plugin/theme management

### For Payment Managers:

#### Adding a Payment with Event:
1. Go to Pagamenti → Aggiungi Pagamento
2. Select member from "Socio" dropdown
3. Enter amount and date
4. Select payment method
5. Select "Evento" as payment type
6. A new field appears: "Seleziona Evento"
7. Choose an event from the dropdown OR select "Altro Evento"
8. If "Altro Evento" is selected, enter custom event title in the new field
9. Add notes if needed
10. Publish

#### Adding a Payment with Quota Renewal:
1. Go to Pagamenti → Aggiungi Pagamento
2. Select member from "Socio" dropdown
3. Enter amount and date
4. Select payment method
5. Select "Quota Associativa" as payment type
6. A new field appears: "Categoria Socio"
7. Select the appropriate member category
8. Add notes if needed
9. Publish

## Technical Notes

### Field Visibility Logic:
- Fields are initially hidden using `style="display: none;"`
- JavaScript shows/hides fields dynamically based on selections
- On page load, JavaScript checks saved values and shows appropriate fields
- This ensures clean UX without page reloads

### Data Storage:
- All new fields are stored as post meta in the WordPress database
- Meta keys:
  - `_fg_evento_id`: Event ID or "altro_evento"
  - `_fg_evento_custom`: Custom event title (text)
  - `_fg_categoria_socio_id`: Category term ID (integer)

### Security:
- All user inputs are sanitized using WordPress functions
- Nonces are verified before saving
- Role capabilities enforce access control
- Direct URL access is blocked for unauthorized post types

## Testing Recommendations

1. **Test Payment Form:**
   - Create a payment with "Evento" type → Verify event dropdown appears
   - Select an existing event → Verify it saves correctly
   - Select "Altro Evento" → Verify custom input field appears
   - Enter custom event title → Verify it saves correctly
   - Create a payment with "Quota Associativa" type → Verify category dropdown appears
   - Select a category → Verify it saves correctly

2. **Test Payment Manager Role:**
   - Create a test user with "Friends Gestionale - Gestore Pagamenti" role
   - Log in as that user
   - Verify redirect to Pagamenti page on login
   - Verify only Pagamenti menu is visible
   - Verify can add/edit/delete payments
   - Try to access other areas via URL → Should show permission error

3. **Test Existing Functionality:**
   - Verify other payment types (donazione, altro) still work
   - Verify existing payments display correctly
   - Verify regular admin users still have full access
   - Verify payment listing shows all columns correctly

## Compatibility Notes

- Compatible with WordPress 5.0+
- Uses standard WordPress APIs (get_posts, get_terms, add_role, etc.)
- No external dependencies added
- JavaScript uses jQuery (already loaded by WordPress)
- All strings are translation-ready using `_e()` and `__()`
