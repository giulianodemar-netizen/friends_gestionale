# Friends of Naples Gestionale - Plugin Structure

## Complete File Tree

```
friends_gestionale/
│
├── friends_gestionale.php              # Main Plugin File (200 lines)
│   ├── Plugin Header (name, version, description)
│   ├── Constants Definition
│   ├── Main Class: Friends_Gestionale
│   ├── Dependencies Loading
│   ├── Hooks Registration
│   ├── Activation/Deactivation Handlers
│   └── Asset Enqueuing (CSS/JS)
│
├── includes/                           # Core PHP Classes
│   │
│   ├── class-post-types.php           # (160 lines)
│   │   ├── Register fg_socio (Members)
│   │   ├── Register fg_pagamento (Payments)
│   │   ├── Register fg_raccolta (Fundraising)
│   │   └── Register Taxonomies
│   │
│   ├── class-meta-boxes.php           # (580 lines)
│   │   ├── Socio Meta Box (personal info)
│   │   ├── Documents Meta Box (uploads)
│   │   ├── Pagamento Meta Box (payment details)
│   │   ├── Raccolta Meta Box (campaign info)
│   │   ├── Save Meta Functions
│   │   └── Document Upload Handler (AJAX)
│   │
│   ├── class-shortcodes.php           # (630 lines)
│   │   ├── [fg_elenco_soci]
│   │   ├── [fg_dettaglio_socio]
│   │   ├── [fg_elenco_raccolte]
│   │   ├── [fg_dettaglio_raccolta]
│   │   ├── [fg_progress_bar]
│   │   ├── [fg_dashboard]
│   │   └── [fg_filtro_soci]
│   │
│   ├── class-admin-dashboard.php      # (570 lines)
│   │   ├── Add Admin Menu Pages
│   │   ├── Dashboard Page (statistics)
│   │   ├── Statistics Page (charts)
│   │   ├── Settings Page (configuration)
│   │   └── Register Settings
│   │
│   ├── class-reminders.php            # (340 lines)
│   │   ├── Schedule Daily Cron
│   │   ├── Check Expiring Memberships
│   │   ├── Update Expired Status
│   │   ├── Send Reminder Emails
│   │   ├── Admin Notices
│   │   └── Manual Reminder Handler
│   │
│   ├── class-export.php               # (410 lines)
│   │   ├── Add Export Page
│   │   ├── Export Members to CSV
│   │   ├── Export Payments to CSV
│   │   ├── Export Campaigns to CSV
│   │   └── Handle Export Requests
│   │
│   └── class-email.php                # (200 lines)
│       ├── Welcome Email
│       ├── Payment Confirmation
│       ├── Email Templates
│       └── Email Sending Functions
│
├── assets/                            # Frontend Resources
│   │
│   ├── css/
│   │   ├── admin-style.css            # (240 lines)
│   │   │   ├── Dashboard Stats Cards
│   │   │   ├── Grid Layouts
│   │   │   ├── Progress Bars
│   │   │   ├── Meta Box Styles
│   │   │   ├── Chart Containers
│   │   │   └── Responsive Rules
│   │   │
│   │   └── frontend-style.css         # (350 lines)
│   │       ├── Tables
│   │       ├── Status Badges
│   │       ├── Raccolta Cards
│   │       ├── Progress Bars
│   │       ├── Dashboard Widgets
│   │       ├── Filter Forms
│   │       └── Responsive Rules
│   │
│   ├── js/
│   │   ├── admin-script.js            # (220 lines)
│   │   │   ├── Document Upload
│   │   │   ├── Datepicker Init
│   │   │   ├── Auto-Calculate Dates
│   │   │   ├── AJAX Helpers
│   │   │   └── UI Enhancements
│   │   │
│   │   └── frontend-script.js         # (280 lines)
│   │       ├── Smooth Scrolling
│   │       ├── Progress Animation
│   │       ├── Filter Forms
│   │       ├── Table Sorting
│   │       ├── Lazy Loading
│   │       └── Social Sharing
│   │
│   └── images/
│       └── icon.svg                   # Plugin Icon
│
├── README.md                          # Complete Documentation (10KB)
│   ├── Features Overview
│   ├── Installation Instructions
│   ├── Configuration Guide
│   ├── Usage Examples
│   ├── Shortcode Documentation
│   ├── Troubleshooting
│   └── Support Information
│
├── QUICK_START.md                     # Installation Guide (4.4KB)
│   ├── Step-by-Step Installation
│   ├── Initial Configuration
│   ├── First Steps Tutorial
│   ├── Common Issues
│   └── Checklist
│
├── CHANGELOG.md                       # Version History (4.9KB)
│   ├── Version 1.0.0 Details
│   ├── Features List
│   ├── Technical Details
│   ├── File Structure
│   └── Future Roadmap
│
├── SUMMARY.txt                        # Technical Summary (13KB)
│   ├── Plugin Overview
│   ├── Directory Structure
│   ├── Features Breakdown
│   ├── Settings Reference
│   ├── Hooks & Filters
│   ├── Security Features
│   ├── Performance Tips
│   └── Troubleshooting Guide
│
└── .gitignore                         # Git Ignore Rules
    ├── WordPress Files
    ├── Build Artifacts
    ├── OS Files
    └── IDE Files
```

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     WordPress Admin                          │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Add/Edit   │  │  Dashboard   │  │   Export     │     │
│  │   Socio      │  │  Statistics  │  │   Data       │     │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘     │
│         │                 │                  │              │
└─────────┼─────────────────┼──────────────────┼──────────────┘
          │                 │                  │
          ▼                 ▼                  ▼
┌─────────────────────────────────────────────────────────────┐
│                  Plugin Core Classes                         │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  class-post-types.php                               │   │
│  │  • Register Custom Post Types                       │   │
│  │  • Register Taxonomies                              │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  class-meta-boxes.php                               │   │
│  │  • Render Meta Boxes                                │   │
│  │  • Save Custom Fields                               │   │
│  │  • Handle Document Uploads                          │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  class-admin-dashboard.php                          │   │
│  │  • Display Statistics                               │   │
│  │  • Render Charts                                    │   │
│  │  • Handle Settings                                  │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  class-reminders.php                                │   │
│  │  • Schedule Cron Jobs                               │   │
│  │  • Check Expirations                                │   │
│  │  • Send Notifications                               │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  class-export.php                                   │   │
│  │  • Generate CSV Files                               │   │
│  │  • Apply Filters                                    │   │
│  │  • Stream Downloads                                 │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  class-email.php                                    │   │
│  │  • Send Welcome Emails                              │   │
│  │  • Send Reminders                                   │   │
│  │  • Send Confirmations                               │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
└───────────────────────────┬──────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                  WordPress Database                          │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   wp_posts   │  │ wp_postmeta  │  │  wp_options  │     │
│  │  (CPT data)  │  │ (Meta data)  │  │  (Settings)  │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│                                                              │
└──────────────────────────┬───────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    Frontend Display                          │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  class-shortcodes.php                               │   │
│  │  • Query Data                                       │   │
│  │  • Render HTML                                      │   │
│  │  • Apply Styles                                     │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                              │
│  ┌────────────┐  ┌────────────┐  ┌─────────────┐          │
│  │  Members   │  │ Campaigns  │  │  Dashboard  │          │
│  │   List     │  │   Cards    │  │   Widget    │          │
│  └────────────┘  └────────────┘  └─────────────┘          │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## Execution Flow

### 1. Plugin Initialization
```
WordPress Loads
    ↓
plugins_loaded hook
    ↓
Friends_Gestionale::get_instance()
    ↓
Load Dependencies (all class files)
    ↓
Register Hooks & Actions
    ↓
Plugin Ready
```

### 2. Admin Dashboard View
```
User Opens Dashboard
    ↓
admin_menu hook
    ↓
Add Menu Pages
    ↓
Render Dashboard
    ↓
Query Statistics
    ↓
Display Cards & Charts
```

### 3. Adding a Member
```
User Fills Form
    ↓
Submit Post
    ↓
save_post_fg_socio hook
    ↓
Save Meta Data
    ↓
Send Welcome Email
    ↓
Redirect to List
```

### 4. Daily Reminder Check
```
WordPress Cron Triggers
    ↓
fg_daily_reminder_check hook
    ↓
Query Expiring Members
    ↓
For Each Member:
    ↓
Send Email
    ↓
Log Activity
    ↓
Update Last Sent
```

### 5. Frontend Shortcode
```
Page/Post Rendered
    ↓
do_shortcode() processes [fg_elenco_soci]
    ↓
Query Members
    ↓
Apply Filters
    ↓
Render HTML Table
    ↓
Enqueue CSS/JS
    ↓
Display to Visitor
```

## Component Dependencies

```
friends_gestionale.php (Main)
├── Requires: WordPress 5.0+
├── Loads: All class files
└── Depends on: None

class-post-types.php
├── Requires: WordPress Core
└── Depends on: None (standalone)

class-meta-boxes.php
├── Requires: class-post-types.php
└── Depends on: WordPress Media Library

class-shortcodes.php
├── Requires: class-post-types.php
└── Depends on: WP_Query, get_post_meta()

class-admin-dashboard.php
├── Requires: class-post-types.php
├── Depends on: Chart.js (CDN)
└── Uses: WP_Query, wp_count_posts()

class-reminders.php
├── Requires: class-email.php
├── Depends on: WordPress Cron
└── Uses: WP_Query, wp_schedule_event()

class-export.php
├── Requires: class-post-types.php
└── Depends on: PHP CSV functions

class-email.php
├── Requires: WordPress Core
└── Depends on: wp_mail()
```

## Total Project Metrics

- **Total Files**: 18
- **Total Lines**: ~4,300 (including docs)
- **PHP Code**: ~2,890 lines
- **CSS**: ~590 lines
- **JavaScript**: ~500 lines
- **Documentation**: ~32KB
- **Package Size**: ~150KB

---

**Generated for Friends of Naples Gestionale v1.0.0**
