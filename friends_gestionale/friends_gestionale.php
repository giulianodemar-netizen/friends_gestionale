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
        
        // Restrict menu access for payment manager role
        add_action('admin_menu', array($this, 'restrict_payment_manager_menu'), 999);
        
        // Redirect payment manager to payments page
        add_action('admin_init', array($this, 'redirect_payment_manager'));
        
        // AJAX handlers
        add_action('wp_ajax_fg_get_member_quota', array($this, 'ajax_get_member_quota'));
        add_action('wp_ajax_fg_get_event_participants', array($this, 'ajax_get_event_participants'));
        add_action('wp_ajax_fg_get_raccolta_donors', array($this, 'ajax_get_raccolta_donors'));
        add_action('wp_ajax_fg_get_socio_donations', array($this, 'ajax_get_socio_donations'));
        add_action('wp_ajax_fg_get_category_quota', array($this, 'ajax_get_category_quota'));
    }
    
    /**
     * Restrict admin menu for plugin manager role
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
            
            // Keep all Friends Gestionale post type menus visible:
            // - Soci (fg_socio)
            // - Pagamenti (fg_pagamento)
            // - Raccolte Fondi (fg_raccolta)
            // - Eventi (fg_evento)
        }
    }
    
    /**
     * Redirect plugin manager appropriately
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
    }
    
    /**
     * Ensure payment manager role exists (called on init)
     */
    public function ensure_payment_manager_role() {
        // Always recreate the role to ensure it has the latest name and capabilities
        $this->create_payment_manager_role();
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
            __('Friends Gestionale - Gestore Soci', 'friends-gestionale'),
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
                    $donors_data[] = array(
                        'id' => $socio_id,
                        'name' => $socio->post_title,
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
        
        wp_enqueue_script(
            'friends-gestionale-admin',
            FRIENDS_GESTIONALE_PLUGIN_URL . 'assets/js/admin-script.js',
            array('jquery', 'jquery-ui-datepicker'),
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
