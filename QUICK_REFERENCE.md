# Quick Reference Guide - Payment Form Enhancements

## For Administrators

### Creating a Payment Manager User

**Steps:**
1. Users → Add New
2. Fill in username, email, password
3. Role: Select "Friends Gestionale - Gestore Pagamenti"
4. Click "Add New User"

**What This User Can Do:**
- ✅ View, add, edit, and delete payments
- ✅ Access payment history
- ❌ Cannot access members, events, or other WordPress areas

---

## For Payment Managers

### Adding a Standard Payment

1. Pagamenti → Aggiungi Pagamento
2. Select member (Socio)
3. Enter amount and date
4. Select payment method (Cash, Bank Transfer, Card, PayPal, Other)
5. Select payment type:
   - **Quota Associativa** (Membership Fee) → See next section
   - **Donazione** (Donation) → Standard form
   - **Evento** (Event) → See section below
   - **Altro** (Other) → Standard form
6. Add notes (optional)
7. Click "Pubblica" (Publish)

---

### Adding a Payment for an Event

**Scenario:** Member paid for event participation or made a donation for an event.

1. Follow steps 1-4 from standard payment
2. Select **"Evento"** as payment type
3. **New field appears:** "Seleziona Evento"
4. Choose from two options:

   **Option A - Existing Event:**
   - Select the event from dropdown
   - Done! Proceed to step 5

   **Option B - Other Event (not in system):**
   - Select **"Altro Evento"** from dropdown
   - **Another field appears:** "Titolo Evento Personalizzato"
   - Enter the event name manually (e.g., "Cena di Beneficenza 2024")
   - Proceed to step 5

5. Add notes (optional)
6. Click "Pubblica"

**Example Use Cases:**
- Member paid for "Gala Dinner 2024" (select from events)
- Member donated for "External Charity Run" (use Altro Evento)
- Member purchased tickets for association concert (select from events)

---

### Adding a Membership Renewal Payment

**Scenario:** Member renews their annual membership quota.

1. Follow steps 1-4 from standard payment
2. Select **"Quota Associativa"** as payment type
3. **New field appears:** "Categoria Socio"
4. Select the member category:
   - Regular Member
   - Student Member
   - Senior Member
   - Honorary Member
   - (Or any custom categories defined by your organization)
5. Add notes (optional, e.g., "Renewed for 2024")
6. Click "Pubblica"

**Why Select Category?**
Different membership categories may have different fees or benefits. This helps track which type of membership was renewed.

---

## Field Visibility Quick Reference

| Payment Type | Additional Fields Shown |
|--------------|------------------------|
| Quota Associativa | Categoria Socio (dropdown) |
| Donazione | None |
| Evento | Seleziona Evento (dropdown) |
|  | + Titolo Evento Personalizzato (if "Altro Evento" selected) |
| Altro | None |

---

## Keyboard Shortcuts & Tips

### Tips for Faster Data Entry:
1. **Tab key** - Move between fields quickly
2. **Select member first** - Amount may auto-populate from member's quota
3. **Date field** - Click to open calendar picker
4. **Event dropdown** - Type to search (if many events)

### Common Workflows:

**Processing Multiple Membership Renewals:**
1. Keep "Tipo di Pagamento" as "Quota Associativa"
2. Category stays selected between entries
3. Just change member and amount for each

**Recording Event Payments:**
1. Set "Tipo di Pagamento" to "Evento"
2. Select the event once
3. Process multiple member payments for same event
4. Event selection stays between entries

---

## Troubleshooting

### "I don't see the event dropdown"
- Make sure "Evento" is selected as payment type
- Refresh the page if needed
- Check JavaScript is enabled in browser

### "I don't see any events in the dropdown"
- No events exist in system yet
- Ask administrator to create events (Eventi → Aggiungi Evento)
- Or use "Altro Evento" option

### "I can't access the Dashboard"
- This is normal for payment managers
- You are automatically redirected to Pagamenti
- This is by design for security

### "I need to access member details"
- Payment managers cannot access member management
- Contact your administrator
- Member names are visible in payment forms

---

## Data You Can Track

With these enhancements, you can now generate reports on:
- Payments by event (which events generate most revenue)
- Payments by membership category (which categories renew most)
- Custom event tracking (non-system events)
- Payment type distribution

---

## Best Practices

### When to Use "Altro Evento":
✅ One-time external events
✅ Events not managed in the system
✅ Historical events before system implementation
✅ Events from partner organizations

❌ Don't use for recurring system events
❌ Don't use if event already exists in Eventi

### When to Record Membership Category:
✅ Always select category for "Quota Associativa" payments
✅ Helps track which membership types are active
✅ Required for accurate financial reporting

### Notes Field Tips:
- Reference invoice numbers
- Add bank transaction IDs
- Note payment split details
- Record exceptional circumstances
- Add due date information

---

## Common Scenarios

### Scenario 1: Member Pays for Gala Dinner
```
Socio: Mario Rossi
Importo: €50.00
Tipo: Evento
Evento: Gala Dinner 2024
Note: Ticket #001
```

### Scenario 2: Student Membership Renewal
```
Socio: Giulia Bianchi
Importo: €25.00
Tipo: Quota Associativa
Categoria Socio: Student Member
Note: Academic year 2024/2025
```

### Scenario 3: Donation for External Fundraiser
```
Socio: Luca Verdi
Importo: €100.00
Tipo: Evento
Evento: Altro Evento
Titolo: "Marathon for Charity 2024"
Note: Donated through our stand
```

### Scenario 4: Regular Donation
```
Socio: Anna Neri
Importo: €30.00
Tipo: Donazione
Note: Monthly contribution - March
```

---

## Need Help?

- Contact your system administrator
- Refer to full TESTING_GUIDE.md for detailed information
- Check CHANGES.md for technical details
- Report issues to development team

---

## Version Information

- Feature: Payment Form Enhancements
- Date: 2024
- Plugin: Friends of Naples Gestionale
- Version: 1.0.0+

---

## Summary

**What's New:**
1. 🎉 Event selection when recording event-related payments
2. 🎉 Custom event title input for non-system events
3. 🎉 Member category selection for membership renewals
4. 🎉 New "Payment Manager" user role with restricted access
5. ✅ WordPress title/editor automatically hidden in payment forms

**Benefits:**
- Better tracking of event-related revenue
- Clearer membership renewal records
- Secure delegation of payment data entry
- Cleaner, more focused interface
- More detailed financial reporting
