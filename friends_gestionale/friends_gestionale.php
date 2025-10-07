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
