# Testing Guide - Payment Form Enhancements

## Prerequisites
Before testing, ensure:
1. WordPress environment is running
2. Friends Gestionale plugin is installed and activated
3. At least one member (Socio) exists in the system
4. At least one event (Evento) exists in the system
5. At least one member category exists (Soci → Categorie)

## Test Case 1: WordPress Title/Description Hidden in Payment Pages

### Steps:
1. Log in as an administrator
2. Navigate to Pagamenti → Aggiungi Pagamento
3. Observe the page layout

### Expected Results:
✓ No WordPress title field visible at the top of the page
✓ No WordPress content editor (WYSIWYG) visible
✓ Only the "Dettagli Pagamento" meta box is visible
✓ All custom fields are displayed within the meta box

### Actual Results:
(To be filled during testing)

---

## Test Case 2: Event Selection for Payment Type "Evento"

### Steps:
1. Log in as an administrator
2. Navigate to Pagamenti → Aggiungi Pagamento
3. Select any member from "Socio" dropdown
4. Select "Evento" from "Tipo di Pagamento" dropdown
5. Observe changes on the page

### Expected Results:
✓ A new field "Seleziona Evento" appears below payment type
✓ The event dropdown contains:
  - "Seleziona Evento" (default option)
  - All existing events from the sistema
  - "Altro Evento" as the last option
✓ The custom event title field is NOT visible yet

### Test Substep 2a: Select Existing Event
6. Select an existing event from the dropdown
7. Enter other payment details (amount, date, etc.)
8. Click "Pubblica"
9. Edit the payment again

### Expected Results:
✓ The selected event is saved and displayed in the dropdown
✓ No custom event title field is visible

### Test Substep 2b: Select "Altro Evento"
10. In the payment form, select "Altro Evento" from the event dropdown
11. Observe changes

### Expected Results:
✓ A new text field "Titolo Evento Personalizzato" appears below the event dropdown
✓ The field is empty and ready for input

12. Enter a custom event name (e.g., "Cena di Beneficenza 2024")
13. Complete the payment details
14. Click "Pubblica"
15. Edit the payment again

### Expected Results:
✓ The event dropdown shows "Altro Evento" selected
✓ The custom event title field shows "Cena di Beneficenza 2024"
✓ All data is preserved correctly

---

## Test Case 3: Category Selection for Payment Type "Quota Associativa"

### Steps:
1. Log in as an administrator
2. Navigate to Pagamenti → Aggiungi Pagamento
3. Select any member from "Socio" dropdown
4. Select "Quota Associativa" from "Tipo di Pagamento" dropdown
5. Observe changes on the page

### Expected Results:
✓ A new field "Categoria Socio" appears below payment type
✓ The category dropdown contains:
  - "Seleziona Categoria" (default option)
  - All existing member categories
✓ Event-related fields are NOT visible

6. Select a member category from the dropdown
7. Complete the payment details
8. Click "Pubblica"
9. Edit the payment again

### Expected Results:
✓ The selected category is saved and displayed in the dropdown
✓ All data is preserved correctly

---

## Test Case 4: Field Visibility Toggle

### Test 4a: Switch Between Payment Types
1. Create a new payment
2. Select "Evento" → Verify event field appears
3. Select "Quota Associativa" → Verify event field hides and category field appears
4. Select "Donazione" → Verify all conditional fields hide
5. Select "Altro" → Verify all conditional fields hide

### Expected Results:
✓ Only relevant fields are shown for each payment type
✓ Switching between types works smoothly without page reload
✓ No JavaScript errors in browser console

### Test 4b: Edit Existing Payment with Event
1. Edit a payment that has "Evento" selected with an event
2. Observe the page load

### Expected Results:
✓ Payment type "Evento" is pre-selected
✓ Event dropdown is visible on page load
✓ Correct event is pre-selected
✓ If "Altro Evento" was selected, custom field is visible with saved value

---

## Test Case 5: Payment Manager Role - Creation

### Steps:
1. Log in as an administrator
2. Navigate to Users → Add New
3. Fill in required user details:
   - Username: testpaymentmanager
   - Email: testpm@example.com
   - Password: (generate or enter)
4. In the "Role" dropdown, observe options

### Expected Results:
✓ "Friends Gestionale - Gestore Pagamenti" appears in the role dropdown
✓ Role is selectable

5. Select "Friends Gestionale - Gestore Pagamenti"
6. Click "Add New User"

### Expected Results:
✓ User is created successfully
✓ User shows in the users list with the correct role

---

## Test Case 6: Payment Manager Role - Login and Redirect

### Steps:
1. Log out from administrator account
2. Log in as the payment manager user created in Test Case 5
3. Observe where you land after login

### Expected Results:
✓ Automatically redirected to Pagamenti page (not Dashboard)
✓ Pagamenti listing is displayed

---

## Test Case 7: Payment Manager Role - Menu Restrictions

### Steps:
1. While logged in as payment manager, observe the left admin menu

### Expected Results:
✓ Only these menu items are visible:
  - Pagamenti
✓ These menu items are NOT visible:
  - Dashboard
  - Posts
  - Media
  - Pages
  - Comments
  - Appearance
  - Plugins
  - Users
  - Tools
  - Settings
  - Soci
  - Raccolte Fondi
  - Eventi

---

## Test Case 8: Payment Manager Role - Capabilities

### Test 8a: Can Add Payment
1. As payment manager, click Pagamenti → Aggiungi Pagamento
2. Fill in all payment details
3. Click "Pubblica"

### Expected Results:
✓ Can access the add payment page
✓ Can fill in all fields
✓ Payment is created successfully
✓ Redirected to payment list or edit page

### Test 8b: Can Edit Payment
1. As payment manager, go to Pagamenti
2. Click "Edit" on any payment
3. Modify some details
4. Click "Update"

### Expected Results:
✓ Can access the edit payment page
✓ Can modify all fields
✓ Changes are saved successfully

### Test 8c: Can Delete Payment
1. As payment manager, go to Pagamenti
2. Hover over a payment
3. Click "Trash"

### Expected Results:
✓ Payment is moved to trash
✓ Success message appears

### Test 8d: Can View Payment List
1. As payment manager, go to Pagamenti

### Expected Results:
✓ Can see all payments in the list
✓ All columns display correctly (Member, Amount, Date, Method, Type)

---

## Test Case 9: Payment Manager Role - Access Restrictions

### Test 9a: Cannot Access Members via URL
1. As payment manager, manually navigate to:
   `wp-admin/edit.php?post_type=fg_socio`

### Expected Results:
✓ Access is denied
✓ Error message: "Non hai i permessi per accedere a questa pagina."

### Test 9b: Cannot Edit Member
1. As payment manager, get the edit URL for a member:
   `wp-admin/post.php?post=123&action=edit` (where 123 is a member ID)
2. Navigate to this URL manually

### Expected Results:
✓ Access is denied
✓ Error message: "Non hai i permessi per accedere a questa pagina."

### Test 9c: Cannot Access Other Post Types
Repeat 9a and 9b for:
- Events: `edit.php?post_type=fg_evento`
- Fundraising: `edit.php?post_type=fg_raccolta`
- Regular Posts: `edit.php`
- Pages: `edit.php?post_type=page`

### Expected Results:
✓ Access is denied for all
✓ Appropriate error messages shown

---

## Test Case 10: Administrator Retains Full Access

### Steps:
1. Log out from payment manager account
2. Log in as administrator
3. Navigate to all sections:
   - Soci
   - Pagamenti
   - Raccolte Fondi
   - Eventi
   - Regular WordPress menus

### Expected Results:
✓ Administrator can access all areas
✓ All menus are visible
✓ No restrictions applied
✓ Can edit payments, members, events, etc.

---

## Test Case 11: Payment Form - All Payment Types Still Work

### Test 11a: Donazione
1. Create a payment with type "Donazione"
2. Verify no extra fields appear
3. Save and verify it works

### Test 11b: Altro
1. Create a payment with type "Altro"
2. Verify no extra fields appear
3. Save and verify it works

### Expected Results for Both:
✓ Form works as before
✓ No conditional fields appear
✓ Payments save correctly

---

## Test Case 12: Backward Compatibility

### Test 12a: Existing Payments Display Correctly
1. View existing payments (created before the update)
2. Edit an existing payment

### Expected Results:
✓ All existing payments display in the list
✓ Can edit existing payments without issues
✓ No errors when opening old payments
✓ New fields are empty for old payments (expected)

---

## Regression Testing Checklist

After all tests, verify these still work:
- [ ] Auto-populate amount from member's quota (when selecting member)
- [ ] Member categories taxonomy still works
- [ ] Event post type still works
- [ ] Payment statistics on dashboard
- [ ] Export functionality (if exists)
- [ ] Email notifications (if configured)

---

## Browser Compatibility

Test the payment form in these browsers:
- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari
- [ ] Edge

Verify:
- Fields show/hide correctly
- Dropdowns work
- JavaScript executes without errors
- Form submission works

---

## Performance Testing

- [ ] Page load time for payment add/edit is acceptable
- [ ] Dropdown population is fast (even with many events/categories)
- [ ] No JavaScript lag when switching payment types
- [ ] No memory leaks in browser console

---

## Security Testing

- [ ] All inputs are sanitized (check in database after saving)
- [ ] Nonces are verified on form submission
- [ ] Payment manager cannot escalate privileges
- [ ] SQL injection attempts fail safely
- [ ] XSS attempts in text fields are escaped

---

## Notes Section

Use this section to document any issues found during testing:

### Issues Found:
1. 
2. 
3. 

### Recommendations:
1. 
2. 
3. 

### Browser-Specific Issues:
- Chrome:
- Firefox:
- Safari:
- Edge:

---

## Test Summary

Date: _______________
Tester: _______________

Total Test Cases: 12
Passed: _______________
Failed: _______________
Not Tested: _______________

Overall Status: [ ] Pass [ ] Fail [ ] Partial Pass

Comments:
