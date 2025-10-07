<?php
/**
 * Custom Post Types Registration
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Post_Types {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
    }
    
    /**
     * Register custom post types
     */
    public static function register_post_types() {
        // Register Soci (Members) post type
        register_post_type('fg_socio', array(
            'labels' => array(
                'name' => __('Soci', 'friends-gestionale'),
                'singular_name' => __('Socio', 'friends-gestionale'),
                'add_new' => __('Aggiungi Socio', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Nuovo Socio', 'friends-gestionale'),
                'edit_item' => __('Modifica Socio', 'friends-gestionale'),
                'new_item' => __('Nuovo Socio', 'friends-gestionale'),
                'view_item' => __('Visualizza Socio', 'friends-gestionale'),
                'search_items' => __('Cerca Soci', 'friends-gestionale'),
                'not_found' => __('Nessun socio trovato', 'friends-gestionale'),
                'not_found_in_trash' => __('Nessun socio nel cestino', 'friends-gestionale'),
                'menu_name' => __('Soci', 'friends-gestionale')
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-groups',
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'soci'),
            'capability_type' => 'post',
            'show_in_rest' => true
        ));
        
        // Register Pagamenti (Payments) post type
        register_post_type('fg_pagamento', array(
            'labels' => array(
                'name' => __('Pagamenti', 'friends-gestionale'),
                'singular_name' => __('Pagamento', 'friends-gestionale'),
                'add_new' => __('Aggiungi Pagamento', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Nuovo Pagamento', 'friends-gestionale'),
                'edit_item' => __('Modifica Pagamento', 'friends-gestionale'),
                'new_item' => __('Nuovo Pagamento', 'friends-gestionale'),
                'view_item' => __('Visualizza Pagamento', 'friends-gestionale'),
                'search_items' => __('Cerca Pagamenti', 'friends-gestionale'),
                'not_found' => __('Nessun pagamento trovato', 'friends-gestionale'),
                'not_found_in_trash' => __('Nessun pagamento nel cestino', 'friends-gestionale'),
                'menu_name' => __('Pagamenti', 'friends-gestionale')
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 26,
            'menu_icon' => 'dashicons-money-alt',
            'supports' => array('title'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'pagamenti'),
            'capability_type' => 'post',
            'show_in_rest' => true
        ));
        
        // Register Raccolte Fondi (Fundraising) post type
        register_post_type('fg_raccolta', array(
            'labels' => array(
                'name' => __('Raccolte Fondi', 'friends-gestionale'),
                'singular_name' => __('Raccolta Fondi', 'friends-gestionale'),
                'add_new' => __('Aggiungi Raccolta', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Nuova Raccolta', 'friends-gestionale'),
                'edit_item' => __('Modifica Raccolta', 'friends-gestionale'),
                'new_item' => __('Nuova Raccolta', 'friends-gestionale'),
                'view_item' => __('Visualizza Raccolta', 'friends-gestionale'),
                'search_items' => __('Cerca Raccolte', 'friends-gestionale'),
                'not_found' => __('Nessuna raccolta trovata', 'friends-gestionale'),
                'not_found_in_trash' => __('Nessuna raccolta nel cestino', 'friends-gestionale'),
                'menu_name' => __('Raccolte Fondi', 'friends-gestionale')
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 27,
            'menu_icon' => 'dashicons-heart',
            'supports' => array('title', 'editor', 'thumbnail'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'raccolte-fondi'),
            'capability_type' => 'post',
            'show_in_rest' => true
        ));
    }
    
    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        // Register Categoria Socio taxonomy
        register_taxonomy('fg_categoria_socio', 'fg_socio', array(
            'labels' => array(
                'name' => __('Categorie Soci', 'friends-gestionale'),
                'singular_name' => __('Categoria Socio', 'friends-gestionale'),
                'search_items' => __('Cerca Categorie', 'friends-gestionale'),
                'all_items' => __('Tutte le Categorie', 'friends-gestionale'),
                'edit_item' => __('Modifica Categoria', 'friends-gestionale'),
                'update_item' => __('Aggiorna Categoria', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Categoria', 'friends-gestionale'),
                'new_item_name' => __('Nuova Categoria', 'friends-gestionale'),
                'menu_name' => __('Categorie', 'friends-gestionale')
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'categoria-socio'),
            'show_in_rest' => true
        ));
        
        // Register Stato Pagamento taxonomy
        register_taxonomy('fg_stato_pagamento', 'fg_pagamento', array(
            'labels' => array(
                'name' => __('Stati Pagamento', 'friends-gestionale'),
                'singular_name' => __('Stato Pagamento', 'friends-gestionale'),
                'search_items' => __('Cerca Stati', 'friends-gestionale'),
                'all_items' => __('Tutti gli Stati', 'friends-gestionale'),
                'edit_item' => __('Modifica Stato', 'friends-gestionale'),
                'update_item' => __('Aggiorna Stato', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Stato', 'friends-gestionale'),
                'new_item_name' => __('Nuovo Stato', 'friends-gestionale'),
                'menu_name' => __('Stati', 'friends-gestionale')
            ),
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'stato-pagamento'),
            'show_in_rest' => true
        ));
    }
}

// Initialize
new Friends_Gestionale_Post_Types();
