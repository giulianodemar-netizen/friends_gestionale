# Expiration Date Management Update - Testing Guide

## Overview

This update implements a new expiration date management system for the Friends Gestionale plugin. The key changes are:

1. **ALL payment types now update expiration dates** (not just quota payments)
2. **Expiration date is set to payment_date + 1 year** (not based on current expiry)
3. **No year restrictions** - payments from any year update the expiry
4. **Recalculation button** added for migrating existing data

## What Changed

### Before
- Only quota payments updated member expiry dates
- Only donations/fundraisers updated donor expiry dates  
- Expiry was calculated differently for members vs donors
- Only current year payments would update expiry
- Members: added 1 year to existing expiry
- Donors: added 1 year to payment date

### After
- **ALL payments** (quota, donazione, raccolta, evento, altro) update expiry for both members and donors
- Expiry is **always** set to payment_date + 1 year
- **No restrictions** on payment year
- **Consistent logic** for both members (anche_socio) and donors (solo_donatore)

## Testing Instructions

### 1. Test New Payment Creation

#### Test Case 1: Member with Quota Payment
1. Go to **Pagamenti** → **Aggiungi Nuovo**
2. Select a member (anche_socio) as donor
3. Set payment type to "Quota Socio"
4. Set payment date to any date (e.g., 2024-06-15)
5. Save the payment
6. Go to **Donatori** and find the member
7. **Verify**: Expiry date should be 2025-06-15 (payment_date + 1 year)

#### Test Case 2: Member with Donation Payment
1. Create a new payment for a member
2. Set payment type to "Donazione"
3. Set payment date to 2024-03-10
4. Save the payment
5. **Verify**: Member's expiry date should be 2025-03-10

#### Test Case 3: Donor with Any Payment Type
1. Create payments for a donor (solo_donatore)
2. Try different payment types: donazione, raccolta, evento
3. **Verify**: Each payment updates the expiry to payment_date + 1 year

#### Test Case 4: Old Payment Dates
1. Create a payment with payment date from 2023 (e.g., 2023-12-01)
2. **Verify**: Expiry is set to 2024-12-01 (no current year restriction)

### 2. Test Recalculation Button

#### Setup
1. Make sure you have some donors/members with existing payments
2. You should have the administrator role (manage_options capability)

#### Test Recalculation
1. Go to **Donatori** in the WordPress admin
2. Look for the "**Ricalcola Scadenze**" button near the top of the page
3. Click the button
4. **Verify**: Confirmation dialog appears
5. Click OK to proceed
6. **Verify**: Success message appears showing:
   - Number of dates updated
   - Number of expiries cleared (for donors with no payments)
   - Number skipped

#### Expected Results
- Donors/members WITH payments: expiry set to latest_payment_date + 1 year
- Donors/members WITHOUT payments: expiry cleared (empty)
- Status updated: "attivo" if expiry >= today, "scaduto" if expiry < today

### 3. Test Edge Cases

#### Test Case 1: Multiple Payments
1. Create a donor with 3 payments:
   - Payment 1: 2023-01-15
   - Payment 2: 2023-06-20
   - Payment 3: 2024-03-10 (latest)
2. Click "Ricalcola Scadenze"
3. **Verify**: Expiry is 2025-03-10 (based on latest payment)

#### Test Case 2: Donor Without Payments
1. Create a new donor
2. Do not add any payments
3. Click "Ricalcola Scadenze"
4. **Verify**: Donor has no expiry date (empty field)

#### Test Case 3: Leap Year
1. Create a payment with date 2024-02-29 (leap year)
2. **Verify**: Expiry is 2025-03-01 (since 2025 is not a leap year)

### 4. Test UI Changes

#### Payment Form Warning
1. Go to create a new payment
2. **Verify**: Warning message is visible stating:
   > "L'inserimento di questo pagamento aggiornerà automaticamente la data di scadenza del socio/donatore a un anno dalla data del pagamento specificata."
3. **Verify**: Warning is visible for ALL payment types (not just quota)

#### Expiry Date Display
1. Edit a donor/member
2. **Verify**: "Data di Scadenza" field is visible
3. **Verify**: Field shows the correct expiry date
4. **Verify**: Field can be manually edited if needed

## Technical Details

### Database Changes
- No schema changes required
- Uses existing `_fg_data_scadenza` meta field
- Works for both `fg_socio` post type (members and donors)

### Performance
- Recalculation uses optimized query to avoid N+1 problem
- Single database query fetches all payment data
- Suitable for databases with thousands of donors/payments

### Security
- Recalculation requires `manage_options` capability (admin only)
- AJAX nonce verification
- XSS protection with proper text escaping
- Input sanitization

### Files Modified
- `friends_gestionale/includes/class-meta-boxes.php` - Core expiry logic
- `friends_gestionale/includes/class-post-types.php` - Recalculation button
- `friends_gestionale/assets/js/admin-script.js` - UI updates

## Troubleshooting

### Button Not Visible
- Make sure you're on the Donatori list page
- Check that you have administrator role
- Clear browser cache

### Recalculation Not Working
- Check JavaScript console for errors
- Verify AJAX endpoint is accessible
- Check WordPress debug log

### Expiry Not Updating on Payment
- Verify payment date is valid
- Check that donor/member is properly linked
- Check WordPress debug log for errors

## Rollback

If you need to revert:
1. Checkout previous commit
2. Run manual SQL if needed to restore old expiry dates
3. Consider re-running recalculation with old logic

## Support

For issues or questions:
- Check WordPress debug log
- Review test results
- Contact plugin maintainer
