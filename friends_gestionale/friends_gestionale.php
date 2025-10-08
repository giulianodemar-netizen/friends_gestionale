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
            remove_menu_page('options-general.php');        // Settings
            
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
        // Check if role exists, if not create it
        if (!get_role('fg_payment_manager')) {
            $this->create_payment_manager_role();
        }
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
            )
        );
        
        // Get the role
        $role = get_role('fg_payment_manager');
        
        if ($role) {
            // Add capabilities for fg_pagamento (Pagamenti)
            $role->add_cap('edit_fg_pagamento');
            $role->add_cap('read_fg_pagamento');
            $role->add_cap('delete_fg_pagamento');
            $role->add_cap('edit_fg_pagamentos');
            $role->add_cap('edit_others_fg_pagamentos');
            $role->add_cap('publish_fg_pagamentos');
            $role->add_cap('read_private_fg_pagamentos');
            $role->add_cap('delete_fg_pagamentos');
            $role->add_cap('delete_private_fg_pagamentos');
            $role->add_cap('delete_published_fg_pagamentos');
            $role->add_cap('delete_others_fg_pagamentos');
            $role->add_cap('edit_private_fg_pagamentos');
            $role->add_cap('edit_published_fg_pagamentos');
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
