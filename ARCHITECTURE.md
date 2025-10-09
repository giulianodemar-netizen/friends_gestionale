# Implementation Architecture Diagram

## Payment Form Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    PAYMENT ADD/EDIT PAGE                        │
│                                                                 │
│  WordPress Title/Description: HIDDEN ✓                         │
│                                                                 │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │          DETTAGLI PAGAMENTO (Meta Box)                    │ │
│  │                                                           │ │
│  │  1. Socio: [Dropdown - All Members]                      │ │
│  │  2. Importo: [Number Input]                              │ │
│  │  3. Data Pagamento: [Date Picker]                        │ │
│  │  4. Metodo Pagamento: [Dropdown]                         │ │
│  │     - Contanti / Bonifico / Carta / PayPal / Altro       │ │
│  │                                                           │ │
│  │  5. Tipo Pagamento: [Dropdown] ◄── Triggers JS          │ │
│  │     ┌─────────────────────────────────────────────┐      │ │
│  │     │ • Quota Associativa                         │      │ │
│  │     │ • Donazione                                 │      │ │
│  │     │ • Evento                                    │      │ │
│  │     │ • Altro                                     │      │ │
│  │     └─────────────────────────────────────────────┘      │ │
│  │          │                                                │ │
│  │          ▼                                                │ │
│  │  ┌──────────────────────────────────────────────────┐    │ │
│  │  │   CONDITIONAL FIELDS (Show/Hide via JS)          │    │ │
│  │  │                                                  │    │ │
│  │  │   IF "Quota Associativa":                       │    │ │
│  │  │   ┌────────────────────────────────────┐        │    │ │
│  │  │   │ Categoria Socio: [Dropdown]        │        │    │ │
│  │  │   │ - Regular Member                   │        │    │ │
│  │  │   │ - Student Member                   │        │    │ │
│  │  │   │ - Senior Member                    │        │    │ │
│  │  │   │ - etc...                           │        │    │ │
│  │  │   └────────────────────────────────────┘        │    │ │
│  │  │                                                  │    │ │
│  │  │   IF "Evento":                                  │    │ │
│  │  │   ┌────────────────────────────────────┐        │    │ │
│  │  │   │ Seleziona Evento: [Dropdown]       │        │    │ │
│  │  │   │ - Event 1                          │        │    │ │
│  │  │   │ - Event 2                          │        │    │ │
│  │  │   │ - ...                              │        │    │ │
│  │  │   │ - Altro Evento ◄── Triggers more   │        │    │ │
│  │  │   └────────────────────────────────────┘        │    │ │
│  │  │          │                                       │    │ │
│  │  │          ▼ (if "Altro Evento" selected)         │    │ │
│  │  │   ┌────────────────────────────────────┐        │    │ │
│  │  │   │ Titolo Evento Personalizzato:      │        │    │ │
│  │  │   │ [Text Input]                       │        │    │ │
│  │  │   └────────────────────────────────────┘        │    │ │
│  │  │                                                  │    │ │
│  │  │   IF "Donazione" OR "Altro":                    │    │ │
│  │  │   (No additional fields)                        │    │ │
│  │  └──────────────────────────────────────────────────┘    │ │
│  │                                                           │ │
│  │  6. Note: [Textarea]                                     │ │
│  │                                                           │ │
│  │  [Pubblica Button]                                       │ │
│  └───────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow

```
┌──────────────────┐
│   User Action    │
│  (Select Type)   │
└────────┬─────────┘
         │
         ▼
┌─────────────────────────────────┐
│   admin-script.js               │
│   togglePaymentFields()         │
│                                 │
│   Checks tipo_pagamento value  │
└────────┬────────────────────────┘
         │
         ├─── "quota" ────► Show: #fg_categoria_socio_field
         │
         ├─── "evento" ───► Show: #fg_evento_field
         │                     │
         │                     └─── Check evento_id
         │                           │
         │                           ├─── "altro_evento" ───► Show: #fg_evento_custom_field
         │                           └─── (other) ───────────► Hide: #fg_evento_custom_field
         │
         └─── "donazione" or "altro" ───► Hide all conditional fields
```

## Database Schema (Post Meta)

```
wp_postmeta
├── _fg_socio_id (int)              ← Existing
├── _fg_importo (float)             ← Existing
├── _fg_data_pagamento (date)       ← Existing
├── _fg_metodo_pagamento (string)   ← Existing
├── _fg_tipo_pagamento (string)     ← Existing
├── _fg_note (text)                 ← Existing
│
├── _fg_evento_id (string)          ← NEW (Event ID or "altro_evento")
├── _fg_evento_custom (string)      ← NEW (Custom event title)
└── _fg_categoria_socio_id (int)    ← NEW (Category term ID)
```

## User Role Hierarchy

```
WordPress Roles
├── Administrator
│   ├── Full access to all areas
│   ├── Can manage plugins
│   ├── Can manage users
│   └── Can manage all post types
│
├── Editor (existing)
│   └── Can edit all content
│
├── Author (existing)
│   └── Can publish own posts
│
├── Friends Gestionale - Gestore Pagamenti ◄── NEW ROLE
│   ├── ✓ Access: edit.php?post_type=fg_pagamento
│   ├── ✓ Can: View/Add/Edit/Delete payments
│   ├── ✗ Cannot: Access Dashboard
│   ├── ✗ Cannot: Access Posts/Pages/Media
│   ├── ✗ Cannot: Access Soci/Eventi/Raccolte
│   ├── ✗ Cannot: Access WordPress Settings
│   └── ✗ Cannot: Manage Users/Plugins
│
└── Subscriber (existing)
    └── Read only access
```

## Menu Visibility Matrix

```
╔═══════════════════════╦════════════════╦══════════════════╗
║   Menu Item           ║ Administrator  ║ Payment Manager  ║
╠═══════════════════════╬════════════════╬══════════════════╣
║ Dashboard             ║       ✓        ║        ✗         ║
║ Posts                 ║       ✓        ║        ✗         ║
║ Media                 ║       ✓        ║        ✗         ║
║ Pages                 ║       ✓        ║        ✗         ║
║ Comments              ║       ✓        ║        ✗         ║
║ Soci                  ║       ✓        ║        ✗         ║
║ Pagamenti             ║       ✓        ║        ✓         ║
║ Raccolte Fondi        ║       ✓        ║        ✗         ║
║ Eventi                ║       ✓        ║        ✗         ║
║ Appearance            ║       ✓        ║        ✗         ║
║ Plugins               ║       ✓        ║        ✗         ║
║ Users                 ║       ✓        ║        ✗         ║
║ Tools                 ║       ✓        ║        ✗         ║
║ Settings              ║       ✓        ║        ✗         ║
╚═══════════════════════╩════════════════╩══════════════════╝
```

## Capability Mapping

```
Custom Post Type: fg_pagamento
Capability Type: array('fg_pagamento', 'fg_pagamentos')

Individual Capabilities:
┌─────────────────────────────────────────────────────┐
│ edit_fg_pagamento                  ← Single post    │
│ read_fg_pagamento                  ← Single post    │
│ delete_fg_pagamento                ← Single post    │
│ edit_fg_pagamentos                 ← All posts      │
│ edit_others_fg_pagamentos          ← Others' posts  │
│ publish_fg_pagamentos              ← Publishing     │
│ read_private_fg_pagamentos         ← Private posts  │
│ delete_fg_pagamentos               ← All posts      │
│ delete_private_fg_pagamentos       ← Private posts  │
│ delete_published_fg_pagamentos     ← Published      │
│ delete_others_fg_pagamentos        ← Others' posts  │
│ edit_private_fg_pagamentos         ← Private posts  │
│ edit_published_fg_pagamentos       ← Published      │
└─────────────────────────────────────────────────────┘

Administrator: ✓ All capabilities
Payment Manager: ✓ All capabilities (for fg_pagamento ONLY)
```

## JavaScript Event Flow

```
Page Load
    │
    ▼
┌───────────────────────────────────────┐
│ $(document).ready()                   │
│   ├── Initialize togglePaymentFields()│
│   │   └── Check current tipo_pagamento│
│   │       └── Show/hide relevant fields│
│   │                                    │
│   ├── Bind: #fg_tipo_pagamento.change │
│   │   └── Call togglePaymentFields()  │
│   │                                    │
│   └── Bind: #fg_evento_id.change      │
│       └── Check if "altro_evento"     │
│           ├── Yes: Show custom field  │
│           └── No: Hide custom field   │
└───────────────────────────────────────┘

User Changes Payment Type
    │
    ▼
togglePaymentFields() called
    │
    ├── Hide all conditional fields
    │
    ▼
    Check tipo_pagamento value
    │
    ├── "quota" ───► Show categoria_socio_field
    │
    ├── "evento" ──► Show evento_field
    │                   │
    │                   └─► Check evento_id value
    │                       ├── "altro_evento" ─► Show custom field
    │                       └── other ──────────► Hide custom field
    │
    └── other ─────► (fields remain hidden)
```

## Plugin Activation Flow

```
Plugin Activated
    │
    ▼
Friends_Gestionale::activate()
    │
    ├─► Register post types
    │   └─► fg_pagamento with custom capabilities
    │
    ├─► Flush rewrite rules
    │
    ├─► Set default options
    │
    └─► create_payment_manager_role()
        │
        ├─► Remove existing role (if any)
        │
        ├─► Add new role 'fg_payment_manager'
        │   └─► With base capabilities:
        │       ├── read
        │       ├── edit_posts
        │       ├── edit_published_posts
        │       ├── publish_posts
        │       ├── delete_posts
        │       ├── delete_published_posts
        │       └── upload_files
        │
        └─► Add custom post type capabilities:
            ├── edit_fg_pagamento
            ├── read_fg_pagamento
            ├── delete_fg_pagamento
            ├── edit_fg_pagamentos
            ├── edit_others_fg_pagamentos
            ├── publish_fg_pagamentos
            ├── read_private_fg_pagamentos
            ├── delete_fg_pagamentos
            ├── delete_private_fg_pagamentos
            ├── delete_published_fg_pagamentos
            ├── delete_others_fg_pagamentos
            ├── edit_private_fg_pagamentos
            └── edit_published_fg_pagamentos
```

## Security Access Flow

```
Payment Manager Logs In
    │
    ▼
WordPress Login Handler
    │
    ▼
admin_init Hook Fired
    │
    ▼
redirect_payment_manager() Called
    │
    ├─► Check if user is payment manager
    │   │
    │   Yes ─► Check current page
    │           │
    │           ├─── On Dashboard (index.php)
    │           │    └─► Redirect to edit.php?post_type=fg_pagamento
    │           │
    │           ├─── Wrong post_type in URL
    │           │    └─► wp_die("Non hai i permessi...")
    │           │
    │           └─── Editing wrong post type
    │                └─► wp_die("Non hai i permessi...")
    │
    └─► (Not payment manager) ─► Continue normally

admin_menu Hook Fired (priority 999)
    │
    ▼
restrict_payment_manager_menu() Called
    │
    ├─► Check if user is payment manager
    │   │
    │   Yes ─► Remove menu items:
    │           ├── Dashboard
    │           ├── Posts
    │           ├── Media
    │           ├── Pages
    │           ├── Comments
    │           ├── Soci
    │           ├── Raccolte Fondi
    │           ├── Eventi
    │           ├── Appearance
    │           ├── Plugins
    │           ├── Users
    │           ├── Tools
    │           └── Settings
    │
    └─► (Not payment manager) ─► Show all menus

Result: Only "Pagamenti" menu visible
```

## File Structure

```
friends_gestionale/
├── friends_gestionale.php
│   ├── activate() ← Creates payment manager role
│   ├── restrict_payment_manager_menu() ← Removes menus
│   └── redirect_payment_manager() ← Security checks
│
├── includes/
│   ├── class-post-types.php
│   │   └── register_post_types()
│   │       └── fg_pagamento ← Custom capabilities
│   │
│   └── class-meta-boxes.php
│       ├── render_pagamento_info_meta_box()
│       │   ├── Loads events
│       │   ├── Loads categories
│       │   └── Renders conditional fields
│       │
│       └── save_meta_boxes()
│           └── Saves new meta fields
│
└── assets/
    └── js/
        └── admin-script.js
            ├── togglePaymentFields() ← Show/hide logic
            ├── #fg_tipo_pagamento.change ← Event listener
            └── #fg_evento_id.change ← Event listener
```

## Summary Checklist

✅ Task 1: Hide title/editor in payment pages (already done)
✅ Task 2: Event selection with "Altro Evento" option
✅ Task 3: Category selection for membership renewals
✅ Task 4: Payment manager user role
✅ Security: Access restrictions implemented
✅ UX: Dynamic field visibility
✅ Data: New meta fields stored correctly
✅ Backward compatible: Old payments unaffected
✅ Documentation: Complete guides provided
