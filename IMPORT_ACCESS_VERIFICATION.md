# Import Feature Access Verification

## Current Implementation Status

### Access Control for fg_payment_manager Role

The import feature is **already configured** to be accessible to the `fg_payment_manager` role (Gestore Donatori). Here's the verification:

#### 1. Menu Visibility ✅

The import page is added as a submenu under **Donatori** (fg_socio):
```php
// In class-import.php, line 32
add_submenu_page(
    'edit.php?post_type=fg_socio',  // Parent: Donatori menu
    __('Importa da file', 'friends-gestionale'),
    __('Importa da file', 'friends-gestionale'),
    'edit_posts',  // Required capability
    'fg-import',
    array($this, 'render_import_page')
);
```

The Donatori menu IS visible to payment managers (it's not removed in `restrict_payment_manager_menu()`).

#### 2. Capability Requirements ✅

**Required capability**: `edit_posts`

**Role capabilities** (from friends_gestionale.php, line 271):
```php
add_role(
    'fg_payment_manager',
    __('Friends Gestionale - Gestore Donatori', 'friends-gestionale'),
    array(
        'read' => true,
        'edit_posts' => true,              // ✅ HAS THIS
        'upload_files' => true,            // ✅ NEEDED FOR FILE UPLOAD
        'edit_published_posts' => true,
        'edit_others_posts' => true,
        'publish_posts' => true,
        // ... other capabilities
    )
);
```

#### 3. AJAX Handlers ✅

All AJAX handlers check for `edit_posts` capability:
```php
// Line 364, 549, 716, 908, 939 in class-import.php
if (!current_user_can('edit_posts')) {
    wp_send_json_error(array('message' => __('Permessi insufficienti', 'friends-gestionale')));
}
```

The `fg_payment_manager` role HAS this capability.

#### 4. File Upload Capability ✅

The import requires file upload capability, which is included:
```php
'upload_files' => true,  // Line 283 in friends_gestionale.php
```

#### 5. Menu Restriction Check ✅

The import menu is NOT explicitly removed for payment managers:
```php
// In restrict_payment_manager_menu(), line 180
remove_submenu_page('friends-gestionale', 'fg-export');  // Export IS removed
// Import is NOT removed - it's accessible!
```

## Expected Behavior

When a user with the `fg_payment_manager` role logs in:

1. ✅ They should see the **Donatori** menu in the admin sidebar
2. ✅ Under Donatori, they should see **"Importa da file"** submenu item
3. ✅ Clicking it should load the import page
4. ✅ They should be able to upload CSV/XLSX files
5. ✅ They should be able to complete the full import workflow

## Potential Issues & Solutions

### Issue 1: Role Not Updated After Plugin Installation

**Symptom**: Menu item visible but functionality not working

**Cause**: WordPress roles are cached and don't auto-update when plugin is updated

**Solution**: Deactivate and reactivate the plugin to trigger role recreation:
1. Go to WordPress Admin → Plugins
2. Deactivate "Friends of Naples Gestionale"
3. Reactivate "Friends of Naples Gestionale"
4. The role will be recreated with all capabilities

### Issue 2: User Created Before Plugin Update

**Symptom**: User can't see the import menu

**Cause**: User role was assigned before import feature was added

**Solution**: Same as Issue 1 - deactivate/reactivate plugin

### Issue 3: Capability Caching

**Symptom**: User has role but can't access feature

**Solution**: 
1. As admin, go to Users → Find the user
2. Change their role to something else (e.g., Subscriber)
3. Save
4. Change back to "Friends Gestionale - Gestore Donatori"
5. Save

## Testing Checklist

To verify the import is accessible to `fg_payment_manager`:

- [ ] Log in as a user with `fg_payment_manager` role
- [ ] Verify "Donatori" menu is visible in admin sidebar
- [ ] Click on Donatori menu
- [ ] Verify "Importa da file" submenu is visible
- [ ] Click on "Importa da file"
- [ ] Verify import page loads correctly
- [ ] Try uploading a CSV file
- [ ] Verify file upload works
- [ ] Complete import workflow
- [ ] Verify import executes successfully

## Conclusion

The import feature is **already fully accessible** to the `fg_payment_manager` role based on the current implementation. No code changes are needed.

If the feature is not accessible, the issue is likely:
1. Role needs to be refreshed (deactivate/reactivate plugin)
2. Browser cache needs clearing
3. WordPress object cache needs flushing

The implementation follows WordPress best practices and uses the same capability structure as other features in the plugin.
