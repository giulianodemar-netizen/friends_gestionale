<?php
/**
 * Plugin Name: Friends of Naples Gestionale
 * Plugin URI: https://github.com/giulianodemar-netizen/friends_gestionale
 * Description: Sistema gestionale completo per associazioni con gestione soci, pagamenti, raccolte fondi, dashboard statistiche, reminder e esportazione dati.
 * Version: 1.0.0
 * Author: Friends of Naples
 * Author URI: https://github.com/giulianodemar-netizen
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: friends-gestionale
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FRIENDS_GESTIONALE_VERSION', '1.0.0');
define('FRIENDS_GESTIONALE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FRIENDS_GESTIONALE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FRIENDS_GESTIONALE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Friends_Gestionale {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Custom Post Types
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-post-types.php';
        
        // Meta boxes and custom fields
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-meta-boxes.php';
        
        // Shortcodes
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-shortcodes.php';
        
        // Admin dashboard
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-admin-dashboard.php';
        
        // Reminders
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-reminders.php';
        
        // Export functionality
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-export.php';
        
        // Import functionality
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-import.php';
        
        // Email notifications
        require_once FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/class-email.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // Load text domain for translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Ensure payment manager role exists
        add_action('init', array($this, 'ensure_payment_manager_role'));
        
        // Ensure viewer role exists
        add_action('init', array($this, 'ensure_viewer_role'));
        
        // Initialize Export class
        add_action('init', array($this, 'init_export_class'));
        
        // Initialize Import class
        add_action('init', array($this, 'init_import_class'));
        
        // Restrict menu access for payment manager role
        add_action('admin_menu', array($this, 'restrict_payment_manager_menu'), 999);
        
        // Redirect payment manager to payments page
        add_action('admin_init', array($this, 'redirect_payment_manager'));
        
        // Login redirect for payment manager
        add_filter('login_redirect', array($this, 'redirect_after_login'), 10, 3);
        
        // AJAX handlers
        add_action('wp_ajax_fg_get_member_quota', array($this, 'ajax_get_member_quota'));
        add_action('wp_ajax_fg_get_event_participants', array($this, 'ajax_get_event_participants'));
        add_action('wp_ajax_fg_get_raccolta_donors', array($this, 'ajax_get_raccolta_donors'));
        add_action('wp_ajax_fg_get_socio_donations', array($this, 'ajax_get_socio_donations'));
        add_action('wp_ajax_fg_get_evento_donations', array($this, 'ajax_get_evento_donations'));
        add_action('wp_ajax_fg_get_category_quota', array($this, 'ajax_get_category_quota'));
    }
    
    /**
     * Initialize Export class
     */
    public function init_export_class() {
        new Friends_Gestionale_Export();
    }
    
    /**
     * Initialize Import class
     */
    public function init_import_class() {
        new Friends_Gestionale_Import();
    }
    
    /**
     * Redirect to plugin dashboard after login for payment managers
     */
    public function redirect_after_login($redirect_to, $request, $user) {
        // Check if user object is valid and has roles
        if (isset($user->roles) && is_array($user->roles)) {
            // If user has payment manager role, redirect to plugin dashboard
            if (in_array('fg_payment_manager', $user->roles)) {
                return admin_url('admin.php?page=friends-gestionale');
            }
        }
        // For all other users, use default redirect
        return $redirect_to;
    }
    
    /**
     * Restrict admin menu for plugin manager and viewer roles
     */
    public function restrict_payment_manager_menu() {
        $user = wp_get_current_user();
        
        // Don't restrict administrators - they should have full access
        if (in_array('administrator', $user->roles)) {
            return;
        }
        
        if (in_array('fg_payment_manager', $user->roles)) {
            // Remove all default WordPress menus - keep only Friends Gestionale plugin menus
            remove_menu_page('index.php');                  // Dashboard
            remove_menu_page('edit.php');                   // Posts
            remove_menu_page('upload.php');                 // Media
            remove_menu_page('edit.php?post_type=page');    // Pages
            remove_menu_page('edit-comments.php');          // Comments
            remove_menu_page('themes.php');                 // Appearance
            remove_menu_page('plugins.php');                // Plugins
            remove_menu_page('users.php');                  // Users
            remove_menu_page('tools.php');                  // Tools
            // Keep Settings visible for plugin settings access
            // remove_menu_page('options-general.php');     // Settings - now kept visible
            
            // Remove Visual Composer menus (try multiple possible slugs)
            remove_menu_page('vc-general');                 // Visual Composer
            remove_menu_page('vc-welcome');                 // VC Welcome
            remove_menu_page('vcv-settings');               // VC Settings
            remove_menu_page('vcv-about');                  // VC About
            remove_menu_page('vcv-headers-footers');        // VC Headers/Footers
            remove_menu_page('vcv-headers-footers-layouts'); // VC Layouts
            
            // Remove submenu items for plugin users - they'll access via dashboard buttons
            remove_submenu_page('friends-gestionale', 'fg-export');
            
            // Keep all Friends Gestionale post type menus visible:
            // - Donatori (fg_socio)
            // - Pagamenti (fg_pagamento)
            // - Raccolte Fondi (fg_raccolta)
            // - Eventi (fg_evento)
        }
        
        // Viewer role restrictions - similar to payment manager but also remove import
        if (in_array('fg_donatori_viewer', $user->roles)) {
            // Remove all default WordPress menus
            remove_menu_page('index.php');                  // Dashboard
            remove_menu_page('edit.php');                   // Posts
            remove_menu_page('upload.php');                 // Media
            remove_menu_page('edit.php?post_type=page');    // Pages
            remove_menu_page('edit-comments.php');          // Comments
            remove_menu_page('themes.php');                 // Appearance
            remove_menu_page('plugins.php');                // Plugins
            remove_menu_page('users.php');                  // Users
            remove_menu_page('tools.php');                  // Tools
            remove_menu_page('options-general.php');        // Settings - viewers don't need this
            
            // Remove Visual Composer menus
            remove_menu_page('vc-general');
            remove_menu_page('vc-welcome');
            remove_menu_page('vcv-settings');
            remove_menu_page('vcv-about');
            remove_menu_page('vcv-headers-footers');
            remove_menu_page('vcv-headers-footers-layouts');
            
            // Remove import and export submenus - viewers can't import/export
            remove_submenu_page('friends-gestionale', 'fg-import');
            remove_submenu_page('friends-gestionale', 'fg-export');
            remove_submenu_page('friends-gestionale', 'fg-settings'); // No settings access
            
            // Keep visible (read-only):
            // - Friends Gestionale Dashboard
            // - Statistiche
            // - Donatori (fg_socio)
            // - Pagamenti (fg_pagamento)
            // - Raccolte Fondi (fg_raccolta)
            // - Eventi (fg_evento)
        }
    }
    
    /**
     * Redirect plugin manager and viewer appropriately
     */
    public function redirect_payment_manager() {
        $user = wp_get_current_user();
        
        // Don't restrict administrators - they should have full access
        if (in_array('administrator', $user->roles)) {
            return;
        }
        
        if (in_array('fg_payment_manager', $user->roles)) {
            global $pagenow;
            
            // Prevent access to post types outside the plugin
            if ($pagenow == 'post.php' && isset($_GET['post'])) {
                $post_type = get_post_type($_GET['post']);
                $allowed_types = array('fg_socio', 'fg_pagamento', 'fg_raccolta', 'fg_evento');
                if ($post_type && !in_array($post_type, $allowed_types)) {
                    wp_die(__('Non hai i permessi per accedere a questa pagina.', 'friends-gestionale'));
                }
            }
            
            // Prevent access to other post types via post_type parameter
            if (isset($_GET['post_type'])) {
                $allowed_types = array('fg_socio', 'fg_pagamento', 'fg_raccolta', 'fg_evento');
                if (!in_array($_GET['post_type'], $allowed_types)) {
                    wp_die(__('Non hai i permessi per accedere a questa pagina.', 'friends-gestionale'));
                }
            }
        }
        
        // Viewer role - block all edit/create/delete actions
        if (in_array('fg_donatori_viewer', $user->roles)) {
            global $pagenow;
            
            // Block post.php unless it's just viewing
            if ($pagenow == 'post.php' && isset($_GET['post'])) {
                // Allow viewing, but block editing
                if (!isset($_GET['action']) || $_GET['action'] !== 'view') {
                    // Redirect to view mode instead of edit
                    $post_id = intval($_GET['post']);
                    $post_type = get_post_type($post_id);
                    $allowed_types = array('fg_socio', 'fg_pagamento', 'fg_raccolta', 'fg_evento');
                    
                    if (in_array($post_type, $allowed_types)) {
                        wp_die(__('Non hai i permessi per modificare questo contenuto. Puoi solo visualizzarlo.', 'friends-gestionale'));
                    } else {
                        wp_die(__('Non hai i permessi per accedere a questa pagina.', 'friends-gestionale'));
                    }
                }
            }
            
            // Block post-new.php entirely - no creation allowed
            if ($pagenow == 'post-new.php') {
                wp_die(__('Non hai i permessi per creare nuovi contenuti.', 'friends-gestionale'));
            }
            
            // Block edit.php with action parameter (bulk actions)
            if ($pagenow == 'edit.php' && isset($_GET['action']) && $_GET['action'] !== '-1') {
                wp_die(__('Non hai i permessi per modificare contenuti.', 'friends-gestionale'));
            }
            
            // Block import page
            if (isset($_GET['page']) && $_GET['page'] === 'fg-import') {
                wp_die(__('Non hai i permessi per importare dati.', 'friends-gestionale'));
            }
            
            // Block export page
            if (isset($_GET['page']) && $_GET['page'] === 'fg-export') {
                wp_die(__('Non hai i permessi per esportare dati.', 'friends-gestionale'));
            }
            
            // Block settings page
            if (isset($_GET['page']) && $_GET['page'] === 'fg-settings') {
                wp_die(__('Non hai i permessi per accedere alle impostazioni.', 'friends-gestionale'));
            }
        }
    }
    
    /**
     * Ensure payment manager role exists (called on init)
     */
    public function ensure_payment_manager_role() {
        // Always recreate the role to ensure it has the latest name and capabilities
        $this->create_payment_manager_role();
    }
    
    /**
     * Ensure viewer role exists (called on init)
     */
    public function ensure_viewer_role() {
        // Always recreate the role to ensure it has the latest capabilities
        $this->create_viewer_role();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Flush rewrite rules to ensure custom post types work
        Friends_Gestionale_Post_Types::register_post_types();
        flush_rewrite_rules();
        
        // Set up default options
        add_option('friends_gestionale_quota_annuale', 50);
        add_option('friends_gestionale_reminder_days', 30);
        add_option('friends_gestionale_email_notifications', true);
        
        // Create custom user role for plugin management
        $this->create_payment_manager_role();
        
        // Create viewer role
        $this->create_viewer_role();
    }
    
    /**
     * Create custom user role for plugin management
     */
    private function create_payment_manager_role() {
        // Remove role if it exists to ensure clean setup
        remove_role('fg_payment_manager');
        
        // Add the role with capabilities for all plugin areas
        add_role(
            'fg_payment_manager',
            __('Friends Gestionale - Gestore Donatori', 'friends-gestionale'),
            array(
                'read' => true,
                
                // Basic post capabilities
                'edit_posts' => true,
                'edit_published_posts' => true,
                'edit_others_posts' => true,
                'publish_posts' => true,
                'delete_posts' => true,
                'delete_published_posts' => true,
                'delete_others_posts' => true,
                'read_private_posts' => true,
                'edit_private_posts' => true,
                'delete_private_posts' => true,
                
                // Upload files (for attachments)
                'upload_files' => true,
                
                // Taxonomy management capabilities
                'manage_categories' => true,
                'edit_categories' => true,
                'delete_categories' => true,
                'assign_categories' => true,
            )
        );
        
        // Get the roles
        $plugin_role = get_role('fg_payment_manager');
        $admin_role = get_role('administrator');
        
        // Add capabilities for fg_pagamento (Pagamenti) to both roles
        $capabilities = array(
            'edit_fg_pagamento',
            'read_fg_pagamento',
            'delete_fg_pagamento',
            'edit_fg_pagamentos',
            'edit_others_fg_pagamentos',
            'publish_fg_pagamentos',
            'read_private_fg_pagamentos',
            'delete_fg_pagamentos',
            'delete_private_fg_pagamentos',
            'delete_published_fg_pagamentos',
            'delete_others_fg_pagamentos',
            'edit_private_fg_pagamentos',
            'edit_published_fg_pagamentos',
        );
        
        foreach ($capabilities as $cap) {
            if ($plugin_role) {
                $plugin_role->add_cap($cap);
            }
            if ($admin_role) {
                $admin_role->add_cap($cap);
            }
        }
    }
    
    /**
     * Create viewer role for read-only access
     */
    private function create_viewer_role() {
        // Remove role if it exists to ensure clean setup
        remove_role('fg_donatori_viewer');
        
        // Add the role with read-only capabilities
        // Need 'edit_posts' to see menus, but no actual edit/delete/publish caps
        add_role(
            'fg_donatori_viewer',
            __('Donatori Visualizzatore', 'friends-gestionale'),
            array(
                'read' => true,
                'edit_posts' => true, // Needed to see the admin menus
                'read_private_posts' => true, // To view private posts
            )
        );
        
        // Get the roles
        $viewer_role = get_role('fg_donatori_viewer');
        $admin_role = get_role('administrator');
        
        // Add read and list capabilities for all plugin post types
        $read_capabilities = array(
            // Donatori/Soci (fg_socio)
            'read_fg_socio',
            'edit_fg_socios', // List capability (plural form needed to see the menu)
            'edit_others_fg_socios', // To view posts by others
            
            // Pagamenti (fg_pagamento)
            'read_fg_pagamento',
            'edit_fg_pagamentos', // List capability
            'edit_others_fg_pagamentos',
            
            // Raccolte Fondi (fg_raccolta) - uses standard 'post' capability_type
            'read_private_posts',
            'edit_others_posts',
            
            // Eventi (fg_evento) - uses standard 'post' capability_type
            // Already covered by edit_posts and edit_others_posts
        );
        
        foreach ($read_capabilities as $cap) {
            if ($viewer_role) {
                $viewer_role->add_cap($cap);
            }
        }
    }
    
    /**
     * AJAX handler to get member quota from category
     */
    public function ajax_get_member_quota() {
        $socio_id = isset($_POST['socio_id']) ? absint($_POST['socio_id']) : 0;
        $categoria_id = isset($_POST['categoria_id']) ? absint($_POST['categoria_id']) : 0;
        
        if (!$socio_id) {
            wp_send_json_error(array('message' => 'Invalid socio ID'));
            return;
        }
        
        $quota = 0;
        
        // If category is specified, get quota from category
        if ($categoria_id) {
            $quota = get_term_meta($categoria_id, 'fg_quota_associativa', true);
        } else {
            // Otherwise, get from member's assigned category
            $categories = wp_get_post_terms($socio_id, 'fg_categoria_socio');
            if (!empty($categories) && !is_wp_error($categories)) {
                $categoria_id = $categories[0]->term_id;
                $quota = get_term_meta($categoria_id, 'fg_quota_associativa', true);
            }
        }
        
        // Fallback to member's individual quota if no category quota
        if (empty($quota)) {
            $quota = get_post_meta($socio_id, '_fg_quota_annuale', true);
        }
        
        wp_send_json_success(array(
            'quota' => floatval($quota),
            'categoria_id' => $categoria_id
        ));
    }
    
    /**
     * AJAX handler to get event participants
     */
    public function ajax_get_event_participants() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fg_get_participants')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid event ID'));
            return;
        }
        
        // Get participants
        $partecipanti = get_post_meta($post_id, '_fg_partecipanti', true);
        
        if (!is_array($partecipanti) || empty($partecipanti)) {
            wp_send_json_success(array('participants' => array()));
            return;
        }
        
        $participants_data = array();
        foreach ($partecipanti as $socio_id) {
            $socio = get_post($socio_id);
            if ($socio) {
                $participants_data[] = array(
                    'id' => $socio_id,
                    'name' => $socio->post_title,
                    'edit_link' => get_edit_post_link($socio_id)
                );
            }
        }
        
        wp_send_json_success(array('participants' => $participants_data));
    }
    
    /**
     * AJAX handler to get raccolta fondi donors
     */
    public function ajax_get_raccolta_donors() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fg_get_donors')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid raccolta ID'));
            return;
        }
        
        // Get all payments for this raccolta
        $payments = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_fg_raccolta_id',
                    'value' => $post_id,
                    'compare' => '='
                )
            )
        ));
        
        if (empty($payments)) {
            $fondi_extra = get_post_meta($post_id, '_fg_fondi_extra', true);
            wp_send_json_success(array('donors' => array(), 'fondi_extra' => floatval($fondi_extra)));
            return;
        }
        
        $donors_data = array();
        foreach ($payments as $payment) {
            $socio_id = get_post_meta($payment->ID, '_fg_socio_id', true);
            $importo = get_post_meta($payment->ID, '_fg_importo', true);
            $data_pagamento = get_post_meta($payment->ID, '_fg_data_pagamento', true);
            
            if ($socio_id) {
                $socio = get_post($socio_id);
                if ($socio) {
                    $tipo_donatore = get_post_meta($socio_id, '_fg_tipo_donatore', true);
                    if (empty($tipo_donatore)) {
                        $tipo_donatore = 'anche_socio'; // Default
                    }
                    $tipo_label = ($tipo_donatore === 'anche_socio') ? 'Socio' : 'Donatore';
                    
                    $donors_data[] = array(
                        'id' => $socio_id,
                        'name' => $socio->post_title,
                        'tipo' => $tipo_label,
                        'amount' => number_format(floatval($importo), 2, ',', '.'),
                        'date' => $data_pagamento ? date_i18n(get_option('date_format'), strtotime($data_pagamento)) : '-',
                        'edit_link' => get_edit_post_link($socio_id)
                    );
                }
            }
        }
        
        // Get fondi extra
        $fondi_extra = get_post_meta($post_id, '_fg_fondi_extra', true);
        
        wp_send_json_success(array('donors' => $donors_data, 'fondi_extra' => floatval($fondi_extra)));
    }
    
    /**
     * AJAX handler to get member donations
     */
    public function ajax_get_socio_donations() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fg_get_socio_donations')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => 'Invalid member ID'));
            return;
        }
        
        // Get all payments for this member
        $payments = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_fg_socio_id',
                    'value' => $post_id,
                    'compare' => '='
                )
            )
        ));
        
        if (empty($payments)) {
            wp_send_json_success(array('donations' => array()));
            return;
        }
        
        $donations_data = array();
        foreach ($payments as $payment) {
            $importo = get_post_meta($payment->ID, '_fg_importo', true);
            $data_pagamento = get_post_meta($payment->ID, '_fg_data_pagamento', true);
            $tipo_pagamento = get_post_meta($payment->ID, '_fg_tipo_pagamento', true);
            
            // Translate payment type
            $tipo_labels = array(
                'quota' => 'Quota Associativa',
                'donazione' => 'Donazione singola',
                'raccolta' => 'Raccolta Fondi',
                'evento' => 'Evento',
                'altro' => 'Altro'
            );
            $tipo_label = isset($tipo_labels[$tipo_pagamento]) ? $tipo_labels[$tipo_pagamento] : 'Pagamento';
            
            $donations_data[] = array(
                'tipo' => $tipo_label,
                'amount' => number_format(floatval($importo), 2, ',', '.'),
                'date' => $data_pagamento ? date_i18n(get_option('date_format'), strtotime($data_pagamento)) : null
            );
        }
        
        wp_send_json_success(array('donations' => $donations_data));
    }
    
    /**
     * AJAX handler to get event donations
     */
    public function ajax_get_evento_donations() {
        $evento_id = isset($_POST['evento_id']) ? absint($_POST['evento_id']) : 0;
        
        if (!$evento_id) {
            wp_send_json_error(array('message' => 'Invalid event ID'));
            return;
        }
        
        // Get all payments for this event
        $payments = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_key' => '_fg_data_pagamento',
            'meta_query' => array(
                array(
                    'key' => '_fg_evento_id',
                    'value' => $evento_id,
                    'compare' => '='
                )
            )
        ));
        
        if (empty($payments)) {
            wp_send_json_success(array('html' => '<p style="text-align: center; color: #666; padding: 20px;">Nessuna donazione per questo evento.</p>'));
            return;
        }
        
        // Calculate total
        $total = 0;
        foreach ($payments as $payment) {
            $importo = get_post_meta($payment->ID, '_fg_importo', true);
            $total += floatval($importo);
        }
        
        // Build HTML
        $html = '<div style="margin-bottom: 20px; text-align: center; padding: 15px; background: #f0f6fc; border: 2px solid #0073aa; border-radius: 4px;">';
        $html .= '<p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">Totale Raccolto:</p>';
        $html .= '<p style="margin: 0; font-size: 28px; font-weight: bold; color: #0073aa;">€' . number_format($total, 2, ',', '.') . '</p>';
        $html .= '<p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">' . count($payments) . ' ' . _n('donazione', 'donazioni', count($payments), 'friends-gestionale') . '</p>';
        $html .= '</div>';
        
        $html .= '<div style="max-height: 400px; overflow-y: auto;">';
        $html .= '<table class="widefat" style="border: 1px solid #ddd;">';
        $html .= '<thead><tr>';
        $html .= '<th style="padding: 10px; background: #f9f9f9;">' . __('Donatore', 'friends-gestionale') . '</th>';
        $html .= '<th style="padding: 10px; background: #f9f9f9;">' . __('Tipo', 'friends-gestionale') . '</th>';
        $html .= '<th style="padding: 10px; background: #f9f9f9;">' . __('Data', 'friends-gestionale') . '</th>';
        $html .= '<th style="padding: 10px; background: #f9f9f9; text-align: right;">' . __('Importo', 'friends-gestionale') . '</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($payments as $payment) {
            $importo = get_post_meta($payment->ID, '_fg_importo', true);
            $data_pagamento = get_post_meta($payment->ID, '_fg_data_pagamento', true);
            $socio_id = get_post_meta($payment->ID, '_fg_socio_id', true);
            
            $socio_nome = __('Anonimo', 'friends-gestionale');
            $tipo_badge = '';
            if ($socio_id) {
                $socio = get_post($socio_id);
                if ($socio) {
                    $socio_nome = '<a href="' . get_edit_post_link($socio_id) . '" target="_blank">' . esc_html($socio->post_title) . '</a>';
                    
                    $tipo_donatore = get_post_meta($socio_id, '_fg_tipo_donatore', true);
                    if (empty($tipo_donatore)) {
                        $tipo_donatore = 'anche_socio'; // Default
                    }
                    if ($tipo_donatore === 'anche_socio') {
                        $tipo_badge = '<span style="display: inline-block; padding: 3px 8px; background: #0073aa; color: white; border-radius: 3px; font-size: 11px; font-weight: bold;">Socio</span>';
                    } else {
                        $tipo_badge = '<span style="display: inline-block; padding: 3px 8px; background: #7e8993; color: white; border-radius: 3px; font-size: 11px; font-weight: bold;">Donatore</span>';
                    }
                }
            }
            
            $html .= '<tr>';
            $html .= '<td style="padding: 10px;">' . $socio_nome . '</td>';
            $html .= '<td style="padding: 10px;">' . $tipo_badge . '</td>';
            $html .= '<td style="padding: 10px;">' . ($data_pagamento ? date_i18n(get_option('date_format'), strtotime($data_pagamento)) : '-') . '</td>';
            $html .= '<td style="padding: 10px; text-align: right; font-weight: bold; color: #0073aa;">€' . number_format(floatval($importo), 2, ',', '.') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '</div>';
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * AJAX handler to get category quota
     */
    public function ajax_get_category_quota() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fg_get_category_quota')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }
        
        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;
        
        if (!$category_id) {
            wp_send_json_error(array('message' => 'Invalid category ID'));
            return;
        }
        
        // Get quota from category
        $quota = get_term_meta($category_id, 'fg_quota_associativa', true);
        
        if ($quota) {
            wp_send_json_success(array('quota' => floatval($quota)));
        } else {
            wp_send_json_success(array('quota' => 0));
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Load on all admin pages for consistency
        wp_enqueue_style(
            'friends-gestionale-admin',
            FRIENDS_GESTIONALE_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            FRIENDS_GESTIONALE_VERSION
        );
        
        // Enqueue Select2 for searchable dropdowns
        wp_enqueue_style(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            array(),
            '4.1.0'
        );
        
        wp_enqueue_script(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            array('jquery'),
            '4.1.0',
            true
        );
        
        wp_enqueue_script(
            'friends-gestionale-admin',
            FRIENDS_GESTIONALE_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery', 'jquery-ui-datepicker', 'select2'),
            FRIENDS_GESTIONALE_VERSION,
            true
        );
        
        // Chart.js for statistics
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
            array(),
            '3.9.1',
            true
        );
        
        // Import script for import page
        if (isset($_GET['page']) && $_GET['page'] === 'fg-import') {
            wp_enqueue_media(); // For file upload
            wp_enqueue_script(
                'friends-gestionale-import',
                FRIENDS_GESTIONALE_PLUGIN_URL . 'assets/js/import-script.js',
                array('jquery'),
                FRIENDS_GESTIONALE_VERSION,
                true
            );
            
            wp_localize_script('friends-gestionale-import', 'fg_import_vars', array(
                'nonce' => wp_create_nonce('fg-import-nonce'),
                'fields' => Friends_Gestionale_Import::get_mappable_fields(),
                'tooltips' => Friends_Gestionale_Import::get_field_tooltips()
            ));
        }
        
        // Localize script
        wp_localize_script('friends-gestionale-admin', 'friendsGestionale', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('friends_gestionale_nonce')
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'friends-gestionale-frontend',
            FRIENDS_GESTIONALE_PLUGIN_URL . 'assets/css/frontend-style.css',
            array(),
            FRIENDS_GESTIONALE_VERSION
        );
        
        wp_enqueue_script(
            'friends-gestionale-frontend',
            FRIENDS_GESTIONALE_PLUGIN_URL . 'assets/js/frontend-script.js',
            array('jquery'),
            FRIENDS_GESTIONALE_VERSION,
            true
        );
        
        wp_localize_script('friends-gestionale-frontend', 'friendsGestionale', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('friends_gestionale_nonce')
        ));
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'friends-gestionale',
            false,
            dirname(FRIENDS_GESTIONALE_PLUGIN_BASENAME) . '/languages'
        );
    }
}

// Initialize the plugin
function friends_gestionale_init() {
    return Friends_Gestionale::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'friends_gestionale_init');
