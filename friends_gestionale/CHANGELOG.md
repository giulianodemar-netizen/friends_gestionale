# Changelog

All notable changes to Friends of Naples Gestionale will be documented in this file.

## [1.1.0] - 2024

### Added
- **Import: Skip Existing Records Option**
  - New checkbox "Ignora record esistenti (per email)" in import UI
  - When enabled, records with existing email addresses are skipped (not updated)
  - Preserves existing data when re-importing files
  - Added tests for skip functionality
  
- **Import: Improved Tooltip for "Tipo Socio/Donatore"**
  - Enhanced tooltip with complete description
  - Now shows: "Se contiene 'socio' o 'donatore' (case-insensitive), il record verrà classificato rispettivamente come Socio o Donatore. Esempio: 'socio sostenitore' => Socio; 'donatore occasionale' => Donatore."
  - Better guidance for users during import process
  
- **Statistics: Date Range Filter**
  - New date filter section at top of statistics page
  - Start date and end date input fields (optional, can use either or both)
  - "Applica Filtro" button to refresh statistics
  - All charts and tables update based on selected date range
  - Date validation (start_date <= end_date, ISO format YYYY-MM-DD)
  - Visual indicator showing active filters
  - "Rimuovi Filtro" button to clear and return to default view
  - Filters apply to:
    - Payment trends chart
    - Donations by type
    - Payment methods distribution
    - Top donors ranking
    - Monthly new members chart
  
- **New User Role: Donatori Visualizzatore**
  - Read-only access role for viewing donors/members
  - Can view all data (soci, pagamenti, raccolte, eventi)
  - Cannot create, edit, delete, or import data
  - Perfect for auditors, board members, or read-only staff
  - Automatically created on plugin activation
  
### Changed
- Import validation logic improved to support skip_existing flag
- Role initialization now includes viewer role alongside payment manager role
- Statistics queries now support optional date filtering

### Technical
- Added test suite for skip existing functionality (`test-skip-existing-import.php`)
- Added test suite for viewer role permissions (`test-viewer-role.php`)
- Added test suite for statistics date filtering (`test-statistics-date-filter.php`)
- Updated AJAX handlers to support `skip_existing` parameter
- Modified `validate_and_preview_row()` method signature
- Updated `render_statistics()` method to accept and process date filters
- All statistics queries updated with conditional date filtering

## [1.0.0] - 2024

### Added - Initial Release
- **Custom Post Types**
  - Soci (Members) with complete profile management
  - Pagamenti (Payments) with tracking and history
  - Raccolte Fondi (Fundraising) with progress tracking
  
- **Meta Boxes & Custom Fields**
  - Member information fields (personal data, contacts, dates)
  - Payment details (amount, method, type)
  - Fundraising campaign details (goal, collected, dates)
  - Document upload and management system
  
- **Administrative Dashboard**
  - Real-time statistics (total members, active, expired, revenue)
  - Recent payments display
  - Active fundraising campaigns overview
  - Quick action buttons
  - Admin notices for expiring memberships
  
- **Statistics Page**
  - Interactive payment trend chart (12 months)
  - Member distribution pie chart
  - Chart.js integration for visualizations
  
- **Shortcodes System**
  - `[fg_elenco_soci]` - Members list with filters
  - `[fg_dettaglio_socio]` - Single member details
  - `[fg_elenco_raccolte]` - Fundraising campaigns list
  - `[fg_dettaglio_raccolta]` - Single campaign details
  - `[fg_progress_bar]` - Progress bar widget
  - `[fg_dashboard]` - Statistics dashboard
  - `[fg_filtro_soci]` - Members filter form
  
- **Reminder System**
  - Daily automated check for expiring memberships
  - Configurable reminder days before expiration
  - Automatic status update for expired members
  - Admin dashboard notifications
  
- **Email Notifications**
  - Welcome email for new members
  - Expiration reminder emails
  - Payment confirmation emails
  - Customizable email templates
  - Dynamic placeholders support
  
- **Export Functionality**
  - Members export to CSV with state filters
  - Payments export to CSV with date filters
  - Fundraising campaigns export to CSV
  - UTF-8 encoding with BOM for Excel compatibility
  
- **Styling & Assets**
  - Responsive admin dashboard design
  - Modern frontend styles
  - Card-based layouts
  - Progress bars with animations
  - Mobile-optimized interface
  - Custom color schemes
  
- **JavaScript Enhancements**
  - Document upload via WordPress Media Library
  - Auto-calculation of expiry dates
  - Real-time search and filtering
  - Progress bar animations on scroll
  - AJAX operations support
  
- **Security Features**
  - Nonce verification for all forms
  - Capability checks for admin operations
  - Input sanitization
  - Output escaping
  - CSRF protection
  
- **Documentation**
  - Comprehensive README.md
  - Quick Start Guide
  - Installation instructions
  - Usage examples
  - Shortcode documentation
  - Troubleshooting guide

### Technical Details
- WordPress 5.0+ compatibility
- PHP 7.2+ support
- MySQL 5.6+ support
- REST API ready
- Translation ready (i18n)
- Multisite compatible
- Follows WordPress Coding Standards
- Object-oriented architecture
- Modular code structure

### Files Structure
```
friends_gestionale/
├── friends_gestionale.php          # Main plugin file
├── includes/                       # PHP classes
│   ├── class-post-types.php       # Custom post types
│   ├── class-meta-boxes.php       # Custom fields
│   ├── class-shortcodes.php       # Shortcodes
│   ├── class-admin-dashboard.php  # Admin interface
│   ├── class-reminders.php        # Reminder system
│   ├── class-export.php           # CSV export
│   └── class-email.php            # Email notifications
├── assets/                        # Frontend resources
│   ├── css/                       # Stylesheets
│   ├── js/                        # JavaScript files
│   └── images/                    # Icons and images
├── README.md                      # Full documentation
├── QUICK_START.md                 # Quick start guide
└── CHANGELOG.md                   # This file
```

### Statistics
- Total Lines of Code: ~3,775
- PHP Files: 8
- CSS Files: 2
- JavaScript Files: 2
- Custom Post Types: 3
- Shortcodes: 7
- Meta Boxes: 4
- Admin Pages: 4

## Future Releases

### Planned for 2.0.0
- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Mobile companion app
- [ ] Advanced PDF reports
- [ ] Event calendar module
- [ ] Membership cards with QR codes
- [ ] Attendance tracking system
- [ ] Multi-language support (WPML)
- [ ] Extended REST API
- [ ] Batch operations
- [ ] Advanced search filters

### Under Consideration
- WhatsApp integration for notifications
- SMS notifications
- Member portal with login
- Online payment forms
- Recurring payment automation
- Custom reports builder
- Integration with accounting software
- Volunteer hours tracking
- Newsletter integration

---

**Note:** This is the initial release (1.0.0). For support and feature requests, please visit the GitHub repository.

**Contributors:** Friends of Naples Team
**License:** GPL v2 or later
