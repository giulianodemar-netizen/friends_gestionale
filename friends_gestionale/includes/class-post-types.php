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
        
        // Custom admin columns
        add_filter('manage_fg_socio_posts_columns', array($this, 'set_socio_columns'));
        add_action('manage_fg_socio_posts_custom_column', array($this, 'render_socio_columns'), 10, 2);
        add_filter('manage_edit-fg_socio_sortable_columns', array($this, 'set_socio_sortable_columns'));
        add_filter('request', array($this, 'socio_column_orderby'));
        add_filter('manage_fg_pagamento_posts_columns', array($this, 'set_pagamento_columns'));
        add_action('manage_fg_pagamento_posts_custom_column', array($this, 'render_pagamento_columns'), 10, 2);
        add_filter('manage_fg_raccolta_posts_columns', array($this, 'set_raccolta_columns'));
        add_action('manage_fg_raccolta_posts_custom_column', array($this, 'render_raccolta_columns'), 10, 2);
        add_filter('manage_fg_evento_posts_columns', array($this, 'set_evento_columns'));
        add_action('manage_fg_evento_posts_custom_column', array($this, 'render_evento_columns'), 10, 2);
        
        // Taxonomy custom fields for categoria_socio
        add_action('fg_categoria_socio_add_form_fields', array($this, 'add_categoria_quota_field'));
        add_action('fg_categoria_socio_edit_form_fields', array($this, 'edit_categoria_quota_field'), 10, 2);
        add_action('created_fg_categoria_socio', array($this, 'save_categoria_quota_field'));
        add_action('edited_fg_categoria_socio', array($this, 'save_categoria_quota_field'));
        
        // Add filter dropdown for payment type
        add_action('restrict_manage_posts', array($this, 'add_pagamento_filters'));
        add_filter('parse_query', array($this, 'filter_pagamenti_by_type'));
        
        // Add filter dropdown for donor status
        add_action('restrict_manage_posts', array($this, 'add_donor_stato_filter'));
        add_filter('parse_query', array($this, 'filter_donors_by_stato'));
        
        // Add admin footer script for participant popup
        add_action('admin_footer', array($this, 'add_partecipanti_popup_script'));
    }
    
    /**
     * Register custom post types
     */
    public static function register_post_types() {
        // Register Donatori (Donors) post type
        register_post_type('fg_socio', array(
            'labels' => array(
                'name' => __('Donatori', 'friends-gestionale'),
                'singular_name' => __('Donatore', 'friends-gestionale'),
                'add_new' => __('Aggiungi Donatore', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Nuovo Donatore', 'friends-gestionale'),
                'edit_item' => __('Modifica Donatore', 'friends-gestionale'),
                'new_item' => __('Nuovo Donatore', 'friends-gestionale'),
                'view_item' => __('Visualizza Donatore', 'friends-gestionale'),
                'search_items' => __('Cerca Donatori', 'friends-gestionale'),
                'not_found' => __('Nessun donatore trovato', 'friends-gestionale'),
                'not_found_in_trash' => __('Nessun donatore nel cestino', 'friends-gestionale'),
                'menu_name' => __('Donatori', 'friends-gestionale')
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-groups',
            'supports' => array('thumbnail'),
            'has_archive' => false,
            'rewrite' => array('slug' => 'donatori'),
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
            'capability_type' => array('fg_pagamento', 'fg_pagamentos'),
            'map_meta_cap' => true,
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
            'supports' => array('thumbnail'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'raccolte-fondi'),
            'capability_type' => 'post',
            'show_in_rest' => true
        ));
        
        // Register Eventi (Events) post type
        register_post_type('fg_evento', array(
            'labels' => array(
                'name' => __('Eventi', 'friends-gestionale'),
                'singular_name' => __('Evento', 'friends-gestionale'),
                'add_new' => __('Aggiungi Evento', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Nuovo Evento', 'friends-gestionale'),
                'edit_item' => __('Modifica Evento', 'friends-gestionale'),
                'new_item' => __('Nuovo Evento', 'friends-gestionale'),
                'view_item' => __('Visualizza Evento', 'friends-gestionale'),
                'search_items' => __('Cerca Eventi', 'friends-gestionale'),
                'not_found' => __('Nessun evento trovato', 'friends-gestionale'),
                'not_found_in_trash' => __('Nessun evento nel cestino', 'friends-gestionale'),
                'menu_name' => __('Eventi', 'friends-gestionale')
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 28,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => array('thumbnail'),
            'has_archive' => true,
            'rewrite' => array('slug' => 'eventi'),
            'capability_type' => 'post',
            'show_in_rest' => true
        ));
    }
    
    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        // Register Tipologia Socio taxonomy (for donors who are also members)
        register_taxonomy('fg_categoria_socio', 'fg_socio', array(
            'labels' => array(
                'name' => __('Tipologie Socio', 'friends-gestionale'),
                'singular_name' => __('Tipologia Socio', 'friends-gestionale'),
                'search_items' => __('Cerca Tipologie', 'friends-gestionale'),
                'all_items' => __('Tutte le Tipologie', 'friends-gestionale'),
                'edit_item' => __('Modifica Tipologia', 'friends-gestionale'),
                'update_item' => __('Aggiorna Tipologia', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Tipologia', 'friends-gestionale'),
                'new_item_name' => __('Nuova Tipologia', 'friends-gestionale'),
                'menu_name' => __('Tipologie Socio', 'friends-gestionale')
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'tipologia-socio'),
            'show_in_rest' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'edit_categories',
                'delete_terms' => 'delete_categories',
                'assign_terms' => 'assign_categories',
            ),
        ));
        
        // Register Categoria Donatore taxonomy (for simple donors)
        register_taxonomy('fg_categoria_donatore', 'fg_socio', array(
            'labels' => array(
                'name' => __('Categorie Donatore', 'friends-gestionale'),
                'singular_name' => __('Categoria Donatore', 'friends-gestionale'),
                'search_items' => __('Cerca Categorie', 'friends-gestionale'),
                'all_items' => __('Tutte le Categorie', 'friends-gestionale'),
                'edit_item' => __('Modifica Categoria', 'friends-gestionale'),
                'update_item' => __('Aggiorna Categoria', 'friends-gestionale'),
                'add_new_item' => __('Aggiungi Categoria', 'friends-gestionale'),
                'new_item_name' => __('Nuova Categoria', 'friends-gestionale'),
                'menu_name' => __('Categorie Donatore', 'friends-gestionale')
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'categoria-donatore'),
            'show_in_rest' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'edit_categories',
                'delete_terms' => 'delete_categories',
                'assign_terms' => 'assign_categories',
            ),
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
            'show_in_rest' => true,
            'capabilities' => array(
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'edit_categories',
                'delete_terms' => 'delete_categories',
                'assign_terms' => 'assign_categories',
            ),
        ));
    }
    
    /**
     * Set custom columns for Soci
     */
    public function set_socio_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'fg_nome_foto' => __('Nome Completo', 'friends-gestionale'),
            'fg_tipo_persona' => __('Privato/Società', 'friends-gestionale'),
            'fg_email' => __('Email', 'friends-gestionale'),
            'fg_telefono' => __('Telefono', 'friends-gestionale'),
            'fg_codice_fiscale' => __('Codice Fiscale', 'friends-gestionale'),
            'fg_tipo_donatore' => __('Tipo', 'friends-gestionale'),
            'fg_stato' => __('Stato', 'friends-gestionale'),
            'fg_data_iscrizione' => __('Data Iscrizione', 'friends-gestionale'),
            'fg_data_scadenza' => __('Data Scadenza', 'friends-gestionale'),
            'fg_quota_annuale' => __('Quota Annuale', 'friends-gestionale'),
            'fg_totale_donato' => __('Totale Donato', 'friends-gestionale'),
            'fg_tipologia_socio' => __('Tipologia Socio', 'friends-gestionale'),
            'fg_categoria_donatore' => __('Categoria', 'friends-gestionale'),
            'date' => $columns['date']
        );
    }
    
    /**
     * Render custom columns for Soci
     */
    public function render_socio_columns($column, $post_id) {
        switch ($column) {
            case 'fg_nome_foto':
                // Display name with photo below it
                $title = get_the_title($post_id);
                echo '<strong><a href="' . get_edit_post_link($post_id) . '">' . esc_html($title) . '</a></strong>';
                
                // Add photo if it exists
                $foto_id = get_post_thumbnail_id($post_id);
                if ($foto_id) {
                    $foto_url = wp_get_attachment_image_src($foto_id, 'thumbnail');
                    if ($foto_url) {
                        echo '<br><img src="' . esc_url($foto_url[0]) . '" style="max-width: 60px; height: auto; margin-top: 5px; border-radius: 3px; border: 1px solid #ddd;" />';
                    }
                }
                break;
            case 'fg_tipo_donatore':
                $tipo = get_post_meta($post_id, '_fg_tipo_donatore', true);
                if ($tipo === 'anche_socio') {
                    echo '<span class="fg-badge fg-stato-attivo">Socio</span>';
                } else {
                    echo '<span class="fg-badge">Donatore</span>';
                }
                break;
            case 'fg_tipo_persona':
                $tipo_persona = get_post_meta($post_id, '_fg_tipo_persona', true);
                if (empty($tipo_persona)) {
                    $tipo_persona = 'privato'; // Default
                }
                if ($tipo_persona === 'societa') {
                    echo '<span class="fg-badge" style="background-color: #8b5cf6; color: white;">Società</span>';
                } else {
                    echo '<span class="fg-badge" style="background-color: #10b981; color: white;">Privato</span>';
                }
                break;
            case 'fg_tipologia_socio':
                $tipo_donatore = get_post_meta($post_id, '_fg_tipo_donatore', true);
                if ($tipo_donatore === 'anche_socio') {
                    $categories = wp_get_post_terms($post_id, 'fg_categoria_socio');
                    if (!empty($categories) && !is_wp_error($categories)) {
                        echo esc_html($categories[0]->name);
                    } else {
                        echo '-';
                    }
                } else {
                    echo '-';
                }
                break;
            case 'fg_categoria_donatore':
                $tipo_donatore = get_post_meta($post_id, '_fg_tipo_donatore', true);
                if ($tipo_donatore !== 'anche_socio') {
                    $categories = wp_get_post_terms($post_id, 'fg_categoria_donatore');
                    if (!empty($categories) && !is_wp_error($categories)) {
                        echo esc_html($categories[0]->name);
                    } else {
                        echo '-';
                    }
                } else {
                    echo '-';
                }
                break;
            case 'fg_totale_donato':
                // Calculate total donations for this member
                $payments = get_posts(array(
                    'post_type' => 'fg_pagamento',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_fg_socio_id',
                            'value' => $post_id,
                            'compare' => '='
                        )
                    )
                ));
                
                $total = 0;
                foreach ($payments as $payment) {
                    $importo = get_post_meta($payment->ID, '_fg_importo', true);
                    $total += floatval($importo);
                }
                
                if ($total > 0) {
                    echo '<span class="fg-donazioni-socio-count" data-post-id="' . esc_attr($post_id) . '" style="cursor: pointer; color: #0073aa; text-decoration: underline; font-weight: bold;">€' . number_format($total, 2, ',', '.') . '</span>';
                } else {
                    echo '-';
                }
                break;
            case 'fg_email':
                $email = get_post_meta($post_id, '_fg_email', true);
                echo $email ? esc_html($email) : '-';
                break;
            case 'fg_telefono':
                $telefono = get_post_meta($post_id, '_fg_telefono', true);
                echo $telefono ? esc_html($telefono) : '-';
                break;
            case 'fg_codice_fiscale':
                $cf = get_post_meta($post_id, '_fg_codice_fiscale', true);
                echo $cf ? esc_html($cf) : '-';
                break;
            case 'fg_stato':
                $stato = get_post_meta($post_id, '_fg_stato', true);
                if ($stato) {
                    $class = 'fg-stato-' . esc_attr($stato);
                    echo '<span class="fg-badge ' . $class . '">' . esc_html(ucfirst($stato)) . '</span>';
                } else {
                    echo '-';
                }
                break;
            case 'fg_data_iscrizione':
                $data = get_post_meta($post_id, '_fg_data_iscrizione', true);
                echo $data ? esc_html(date_i18n(get_option('date_format'), strtotime($data))) : '-';
                break;
            case 'fg_data_scadenza':
                $data = get_post_meta($post_id, '_fg_data_scadenza', true);
                echo $data ? esc_html(date_i18n(get_option('date_format'), strtotime($data))) : '-';
                break;
            case 'fg_quota_annuale':
                $quota = get_post_meta($post_id, '_fg_quota_annuale', true);
                echo $quota ? '€' . number_format($quota, 2, ',', '.') : '-';
                break;
        }
    }
    
    /**
     * Set sortable columns for Soci
     */
    public function set_socio_sortable_columns($columns) {
        $columns['fg_nome_foto'] = 'title';
        $columns['fg_tipo_persona'] = 'fg_tipo_persona';
        $columns['fg_email'] = 'fg_email';
        $columns['fg_telefono'] = 'fg_telefono';
        $columns['fg_codice_fiscale'] = 'fg_codice_fiscale';
        $columns['fg_tipo_donatore'] = 'fg_tipo_donatore';
        $columns['fg_stato'] = 'fg_stato';
        $columns['fg_data_iscrizione'] = 'fg_data_iscrizione';
        $columns['fg_data_scadenza'] = 'fg_data_scadenza';
        $columns['fg_quota_annuale'] = 'fg_quota_annuale';
        $columns['fg_totale_donato'] = 'fg_totale_donato';
        
        return $columns;
    }
    
    /**
     * Handle orderby for custom columns
     */
    public function socio_column_orderby($vars) {
        if (!is_admin() || !isset($vars['post_type']) || $vars['post_type'] !== 'fg_socio') {
            return $vars;
        }
        
        if (isset($vars['orderby'])) {
            // Map custom column orderby values to meta keys
            $meta_key_map = array(
                'fg_tipo_persona' => '_fg_tipo_persona',
                'fg_email' => '_fg_email',
                'fg_telefono' => '_fg_telefono',
                'fg_codice_fiscale' => '_fg_codice_fiscale',
                'fg_tipo_donatore' => '_fg_tipo_donatore',
                'fg_stato' => '_fg_stato',
                'fg_data_iscrizione' => '_fg_data_iscrizione',
                'fg_data_scadenza' => '_fg_data_scadenza',
                'fg_quota_annuale' => '_fg_quota_annuale',
                'fg_totale_donato' => '_fg_totale_donato'
            );
            
            if (isset($meta_key_map[$vars['orderby']])) {
                $orderby_key = $vars['orderby'];
                $vars['meta_key'] = $meta_key_map[$orderby_key];
                
                // For numeric fields, use meta_value_num
                if (in_array($orderby_key, array('fg_quota_annuale', 'fg_totale_donato'))) {
                    $vars['orderby'] = 'meta_value_num';
                } else {
                    $vars['orderby'] = 'meta_value';
                }
            }
        }
        
        return $vars;
    }
    
    /**
     * Set custom columns for Pagamenti
     */
    public function set_pagamento_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => __('Riferimento', 'friends-gestionale'),
            'fg_socio' => __('Donatore', 'friends-gestionale'),
            'fg_tipo_donatore' => __('Tipo Donatore', 'friends-gestionale'),
            'fg_importo' => __('Importo', 'friends-gestionale'),
            'fg_data_pagamento' => __('Data Pagamento', 'friends-gestionale'),
            'fg_metodo_pagamento' => __('Metodo', 'friends-gestionale'),
            'fg_tipo_pagamento' => __('Tipo', 'friends-gestionale'),
            'date' => $columns['date']
        );
    }
    
    /**
     * Add filter dropdown for payment type
     */
    public function add_pagamento_filters() {
        global $typenow;
        
        if ($typenow == 'fg_pagamento') {
            $current_tipo = isset($_GET['tipo_pagamento']) ? $_GET['tipo_pagamento'] : '';
            
            $tipi_pagamento = array(
                'quota' => __('Quota Associativa', 'friends-gestionale'),
                'donazione' => __('Donazione singola', 'friends-gestionale'),
                'raccolta' => __('Raccolta Fondi', 'friends-gestionale'),
                'evento' => __('Evento', 'friends-gestionale'),
                'altro' => __('Altro', 'friends-gestionale')
            );
            
            echo '<select name="tipo_pagamento">';
            echo '<option value="">' . __('Tutti i tipi di pagamento', 'friends-gestionale') . '</option>';
            foreach ($tipi_pagamento as $value => $label) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($value),
                    selected($current_tipo, $value, false),
                    esc_html($label)
                );
            }
            echo '</select>';
        }
    }
    
    /**
     * Filter payments by type
     */
    public function filter_pagamenti_by_type($query) {
        global $pagenow, $typenow;
        
        if ($pagenow == 'edit.php' && $typenow == 'fg_pagamento' && isset($_GET['tipo_pagamento']) && $_GET['tipo_pagamento'] != '') {
            $meta_query = array(
                array(
                    'key' => '_fg_tipo_pagamento',
                    'value' => sanitize_text_field($_GET['tipo_pagamento']),
                    'compare' => '='
                )
            );
            $query->set('meta_query', $meta_query);
        }
    }
    
    /**
     * Add filter dropdown for donor status
     */
    public function add_donor_stato_filter() {
        global $typenow;
        
        if ($typenow == 'fg_socio') {
            $current_stato = isset($_GET['fg_stato_filter']) ? $_GET['fg_stato_filter'] : '';
            
            $stati = array(
                'attivo' => __('Attivo', 'friends-gestionale'),
                'sospeso' => __('Sospeso', 'friends-gestionale'),
                'scaduto' => __('Scaduto', 'friends-gestionale'),
                'inattivo' => __('Inattivo', 'friends-gestionale')
            );
            
            echo '<select name="fg_stato_filter">';
            echo '<option value="">' . __('Tutti gli stati', 'friends-gestionale') . '</option>';
            foreach ($stati as $value => $label) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($value),
                    selected($current_stato, $value, false),
                    esc_html($label)
                );
            }
            echo '</select>';
        }
    }
    
    /**
     * Filter donors by status
     */
    public function filter_donors_by_stato($query) {
        global $pagenow, $typenow;
        
        if ($pagenow == 'edit.php' && $typenow == 'fg_socio' && isset($_GET['fg_stato_filter']) && $_GET['fg_stato_filter'] != '') {
            $filter_stato = sanitize_text_field($_GET['fg_stato_filter']);
            
            if ($filter_stato === 'scaduto') {
                // For scaduto filter, check expiry date instead of stato field
                $today = date('Y-m-d');
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => '_fg_tipo_donatore',
                            'value' => 'anche_socio',
                            'compare' => '='
                        ),
                        array(
                            'key' => '_fg_tipo_donatore',
                            'compare' => 'NOT EXISTS'
                        )
                    ),
                    array(
                        'key' => '_fg_data_scadenza',
                        'value' => '',
                        'compare' => '!='
                    ),
                    array(
                        'key' => '_fg_data_scadenza',
                        'value' => $today,
                        'compare' => '<',
                        'type' => 'DATE'
                    )
                );
            } else {
                // For other statuses, filter by stato field
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key' => '_fg_stato',
                        'value' => $filter_stato,
                        'compare' => '='
                    ),
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => '_fg_tipo_donatore',
                            'value' => 'anche_socio',
                            'compare' => '='
                        ),
                        array(
                            'key' => '_fg_tipo_donatore',
                            'compare' => 'NOT EXISTS'
                        )
                    )
                );
            }
            
            $query->set('meta_query', $meta_query);
        }
    }
    
    /**
     * Render custom columns for Pagamenti
     */
    public function render_pagamento_columns($column, $post_id) {
        switch ($column) {
            case 'fg_socio':
                $socio_id = get_post_meta($post_id, '_fg_socio_id', true);
                if ($socio_id) {
                    $socio = get_post($socio_id);
                    if ($socio) {
                        echo '<a href="' . get_edit_post_link($socio_id) . '">' . esc_html($socio->post_title) . '</a>';
                    } else {
                        echo '-';
                    }
                } else {
                    echo '-';
                }
                break;
            case 'fg_tipo_donatore':
                $socio_id = get_post_meta($post_id, '_fg_socio_id', true);
                if ($socio_id) {
                    $tipo_donatore = get_post_meta($socio_id, '_fg_tipo_donatore', true);
                    if (empty($tipo_donatore)) {
                        $tipo_donatore = 'anche_socio'; // Default for backward compatibility
                    }
                    if ($tipo_donatore === 'anche_socio') {
                        echo '<span class="fg-badge fg-stato-attivo">Socio</span>';
                    } else {
                        echo '<span class="fg-badge">Donatore</span>';
                    }
                } else {
                    echo '-';
                }
                break;
            case 'fg_importo':
                $importo = get_post_meta($post_id, '_fg_importo', true);
                echo $importo ? '€' . number_format($importo, 2, ',', '.') : '-';
                break;
            case 'fg_data_pagamento':
                $data = get_post_meta($post_id, '_fg_data_pagamento', true);
                echo $data ? esc_html(date_i18n(get_option('date_format'), strtotime($data))) : '-';
                break;
            case 'fg_metodo_pagamento':
                $metodo = get_post_meta($post_id, '_fg_metodo_pagamento', true);
                echo $metodo ? esc_html(ucfirst($metodo)) : '-';
                break;
            case 'fg_tipo_pagamento':
                $tipo = get_post_meta($post_id, '_fg_tipo_pagamento', true);
                echo $tipo ? esc_html(ucfirst($tipo)) : '-';
                break;
        }
    }
    
    /**
     * Set custom columns for Raccolte
     */
    public function set_raccolta_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => __('Titolo', 'friends-gestionale'),
            'fg_obiettivo' => __('Obiettivo', 'friends-gestionale'),
            'fg_raccolto' => __('Raccolto', 'friends-gestionale'),
            'fg_progresso' => __('Progresso', 'friends-gestionale'),
            'fg_data_inizio' => __('Data Inizio', 'friends-gestionale'),
            'fg_data_fine' => __('Data Fine', 'friends-gestionale'),
            'fg_stato' => __('Stato', 'friends-gestionale'),
            'date' => $columns['date']
        );
    }
    
    /**
     * Render custom columns for Raccolte
     */
    public function render_raccolta_columns($column, $post_id) {
        switch ($column) {
            case 'fg_obiettivo':
                $obiettivo = get_post_meta($post_id, '_fg_obiettivo', true);
                echo $obiettivo ? '€' . number_format($obiettivo, 2, ',', '.') : '-';
                break;
            case 'fg_raccolto':
                $raccolto = get_post_meta($post_id, '_fg_raccolto', true);
                if ($raccolto && $raccolto > 0) {
                    echo '<span class="fg-donatori-count" data-post-id="' . esc_attr($post_id) . '" style="cursor: pointer; color: #0073aa; text-decoration: underline;">€' . number_format($raccolto, 2, ',', '.') . '</span>';
                } else {
                    echo '-';
                }
                break;
            case 'fg_progresso':
                $obiettivo = floatval(get_post_meta($post_id, '_fg_obiettivo', true));
                $raccolto = floatval(get_post_meta($post_id, '_fg_raccolto', true));
                if ($obiettivo > 0) {
                    $percentuale = ($raccolto / $obiettivo) * 100;
                    $percentuale_display = min(100, $percentuale);
                    echo '<div class="fg-progress-wrapper">';
                    echo '<div class="fg-progress-bar-small" style="width: 100px; height: 18px; background: #e0e0e0; border-radius: 3px; position: relative; display: inline-block; vertical-align: middle; margin-right: 8px;">';
                    echo '<div style="width: ' . esc_attr($percentuale_display) . '%; height: 100%; background: #0073aa; border-radius: 3px; transition: width 0.3s ease;"></div>';
                    echo '</div>';
                    echo '<span style="font-weight: 600;">' . number_format($percentuale, 1) . '%</span>';
                    echo '</div>';
                } else {
                    echo '-';
                }
                break;
            case 'fg_data_inizio':
                $data = get_post_meta($post_id, '_fg_data_inizio', true);
                echo $data ? esc_html(date_i18n(get_option('date_format'), strtotime($data))) : '-';
                break;
            case 'fg_data_fine':
                $data = get_post_meta($post_id, '_fg_data_fine', true);
                echo $data ? esc_html(date_i18n(get_option('date_format'), strtotime($data))) : '-';
                break;
            case 'fg_stato':
                $stato = get_post_meta($post_id, '_fg_stato', true);
                if ($stato) {
                    echo '<span class="fg-badge fg-stato-' . esc_attr($stato) . '">' . esc_html(ucfirst($stato)) . '</span>';
                } else {
                    echo '-';
                }
                break;
        }
    }
    
    /**
     * Set custom columns for Eventi
     */
    public function set_evento_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => __('Titolo Evento', 'friends-gestionale'),
            'fg_data_evento' => __('Data Evento', 'friends-gestionale'),
            'fg_luogo' => __('Luogo', 'friends-gestionale'),
            'fg_partecipanti' => __('Partecipanti', 'friends-gestionale'),
            'fg_totale_donazioni' => __('Totale Donazioni', 'friends-gestionale'),
            'fg_stato_evento' => __('Stato', 'friends-gestionale'),
            'date' => $columns['date']
        );
    }
    
    /**
     * Render custom columns for Eventi
     */
    public function render_evento_columns($column, $post_id) {
        switch ($column) {
            case 'fg_data_evento':
                $data = get_post_meta($post_id, '_fg_data_evento', true);
                echo $data ? esc_html(date_i18n(get_option('date_format'), strtotime($data))) : '-';
                break;
            case 'fg_luogo':
                $luogo = get_post_meta($post_id, '_fg_luogo', true);
                echo $luogo ? esc_html($luogo) : '-';
                break;
            case 'fg_partecipanti':
                $partecipanti = get_post_meta($post_id, '_fg_partecipanti', true);
                if (is_array($partecipanti) && !empty($partecipanti)) {
                    $count = count($partecipanti);
                    echo '<span class="fg-partecipanti-count" data-post-id="' . esc_attr($post_id) . '" style="cursor: pointer; color: #0073aa; text-decoration: underline;">' . $count . '</span>';
                } else {
                    echo '0';
                }
                break;
            case 'fg_totale_donazioni':
                // Get all payments for this event
                $payments = get_posts(array(
                    'post_type' => 'fg_pagamento',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_fg_evento_id',
                            'value' => $post_id,
                            'compare' => '='
                        )
                    )
                ));
                
                $total = 0;
                foreach ($payments as $payment) {
                    $importo = get_post_meta($payment->ID, '_fg_importo', true);
                    $total += floatval($importo);
                }
                
                if ($total > 0) {
                    echo '<span class="fg-donatori-evento-count" data-evento-id="' . esc_attr($post_id) . '" style="cursor: pointer; color: #0073aa; text-decoration: underline;">€' . number_format($total, 2, ',', '.') . '</span>';
                } else {
                    echo '€0,00';
                }
                break;
            case 'fg_stato_evento':
                $stato = get_post_meta($post_id, '_fg_stato_evento', true);
                if ($stato) {
                    echo '<span class="fg-badge fg-stato-' . esc_attr($stato) . '">' . esc_html(ucfirst($stato)) . '</span>';
                } else {
                    echo '-';
                }
                break;
        }
    }
    
    /**
     * Add quota field to categoria_socio taxonomy (add form)
     */
    public function add_categoria_quota_field() {
        ?>
        <div class="form-field">
            <label for="fg_quota_associativa"><?php _e('Quota Associativa (€)', 'friends-gestionale'); ?></label>
            <input type="number" name="fg_quota_associativa" id="fg_quota_associativa" step="0.01" min="0" value="" />
            <p class="description"><?php _e('Inserisci l\'importo della quota annuale per questa categoria di socio.', 'friends-gestionale'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Add quota field to categoria_socio taxonomy (edit form)
     */
    public function edit_categoria_quota_field($term, $taxonomy) {
        $quota = get_term_meta($term->term_id, 'fg_quota_associativa', true);
        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="fg_quota_associativa"><?php _e('Quota Associativa (€)', 'friends-gestionale'); ?></label>
            </th>
            <td>
                <input type="number" name="fg_quota_associativa" id="fg_quota_associativa" step="0.01" min="0" value="<?php echo esc_attr($quota); ?>" />
                <p class="description"><?php _e('Inserisci l\'importo della quota annuale per questa categoria di socio.', 'friends-gestionale'); ?></p>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save quota field for categoria_socio taxonomy
     */
    public function save_categoria_quota_field($term_id) {
        if (isset($_POST['fg_quota_associativa'])) {
            $quota = floatval($_POST['fg_quota_associativa']);
            update_term_meta($term_id, 'fg_quota_associativa', $quota);
        }
    }
    
    /**
     * Add participant popup script and styles
     */
    public function add_partecipanti_popup_script() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'fg_evento') {
            ?>
            <style>
                /* Participant popup modal */
                .fg-partecipanti-modal {
                    display: none;
                    position: fixed;
                    z-index: 100000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                }
                .fg-partecipanti-modal-content {
                    background: #fff;
                    margin: 5% auto;
                    padding: 0;
                    border: 2px solid #0073aa;
                    border-radius: 5px;
                    width: 80%;
                    max-width: 600px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                }
                .fg-partecipanti-modal-header {
                    background: #0073aa;
                    color: #fff;
                    padding: 15px 20px;
                    border-radius: 3px 3px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .fg-partecipanti-modal-header h2 {
                    margin: 0;
                    font-size: 18px;
                    color: #fff;
                }
                .fg-partecipanti-modal-close {
                    color: #fff;
                    font-size: 28px;
                    font-weight: bold;
                    cursor: pointer;
                    background: none;
                    border: none;
                    padding: 0;
                    line-height: 1;
                }
                .fg-partecipanti-modal-close:hover {
                    color: #ddd;
                }
                .fg-partecipanti-modal-body {
                    padding: 20px;
                    max-height: 60vh;
                    overflow-y: auto;
                }
                .fg-partecipante-item {
                    padding: 12px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    align-items: center;
                }
                .fg-partecipante-item:last-child {
                    border-bottom: none;
                }
                .fg-partecipante-number {
                    background: #0073aa;
                    color: #fff;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    margin-right: 15px;
                    flex-shrink: 0;
                }
                .fg-partecipante-name {
                    flex-grow: 1;
                    font-size: 14px;
                }
                .fg-partecipante-name a {
                    text-decoration: none;
                    color: #0073aa;
                }
                .fg-partecipante-name a:hover {
                    text-decoration: underline;
                }
            </style>
            <script>
            jQuery(document).ready(function($) {
                // Create modal element
                var modal = $('<div class="fg-partecipanti-modal"></div>');
                var modalContent = $('<div class="fg-partecipanti-modal-content"></div>');
                var modalHeader = $('<div class="fg-partecipanti-modal-header"><h2>Partecipanti all\'Evento</h2><button class="fg-partecipanti-modal-close">&times;</button></div>');
                var modalBody = $('<div class="fg-partecipanti-modal-body"></div>');
                
                modalContent.append(modalHeader).append(modalBody);
                modal.append(modalContent);
                $('body').append(modal);
                
                // Close modal handlers
                $('.fg-partecipanti-modal-close', modal).on('click', function() {
                    modal.hide();
                });
                $(modal).on('click', function(e) {
                    if (e.target === modal[0]) {
                        modal.hide();
                    }
                });
                
                // Click handler for participant count
                $(document).on('click', '.fg-partecipanti-count', function(e) {
                    e.preventDefault();
                    var postId = $(this).data('post-id');
                    
                    // Show loading
                    modalBody.html('<p style="text-align:center;padding:20px;">Caricamento...</p>');
                    modal.show();
                    
                    // AJAX request to get participants
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fg_get_event_participants',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('fg_get_participants'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.participants) {
                                var html = '';
                                $.each(response.data.participants, function(index, participant) {
                                    html += '<div class="fg-partecipante-item">';
                                    html += '<div class="fg-partecipante-number">' + (index + 1) + '</div>';
                                    html += '<div class="fg-partecipante-name">';
                                    if (participant.edit_link) {
                                        html += '<a href="' + participant.edit_link + '" target="_blank">' + participant.name + '</a>';
                                    } else {
                                        html += participant.name;
                                    }
                                    html += '</div>';
                                    html += '</div>';
                                });
                                modalBody.html(html);
                            } else {
                                modalBody.html('<p style="text-align:center;padding:20px;">Nessun partecipante trovato.</p>');
                            }
                        },
                        error: function() {
                            modalBody.html('<p style="text-align:center;padding:20px;color:#dc3545;">Errore nel caricamento dei partecipanti.</p>');
                        }
                    });
                });
            });
            </script>
            <?php
        }
        
        // Add donor popup for raccolta fondi
        if ($screen && $screen->post_type === 'fg_raccolta') {
            ?>
            <style>
                /* Donor popup modal - reuse participant modal styles */
                .fg-donatori-modal {
                    display: none;
                    position: fixed;
                    z-index: 100000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                }
                .fg-donatori-modal-content {
                    background: #fff;
                    margin: 5% auto;
                    padding: 0;
                    border: 2px solid #0073aa;
                    border-radius: 5px;
                    width: 80%;
                    max-width: 600px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                }
                .fg-donatori-modal-header {
                    background: #0073aa;
                    color: #fff;
                    padding: 15px 20px;
                    border-radius: 3px 3px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .fg-donatori-modal-header h2 {
                    margin: 0;
                    font-size: 18px;
                    color: #fff;
                }
                .fg-donatori-modal-close {
                    color: #fff;
                    font-size: 28px;
                    font-weight: bold;
                    cursor: pointer;
                    background: none;
                    border: none;
                    padding: 0;
                    line-height: 1;
                }
                .fg-donatori-modal-close:hover {
                    color: #ddd;
                }
                .fg-donatori-modal-body {
                    padding: 20px;
                    max-height: 60vh;
                    overflow-y: auto;
                }
                .fg-donatore-item {
                    padding: 12px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                .fg-donatore-item:last-child {
                    border-bottom: none;
                }
                .fg-donatore-info {
                    display: flex;
                    align-items: center;
                    flex-grow: 1;
                }
                .fg-donatore-number {
                    background: #0073aa;
                    color: #fff;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    margin-right: 15px;
                    flex-shrink: 0;
                }
                .fg-donatore-name {
                    font-size: 14px;
                }
                .fg-donatore-name a {
                    text-decoration: none;
                    color: #0073aa;
                }
                .fg-donatore-name a:hover {
                    text-decoration: underline;
                }
                .fg-donatore-amount {
                    font-weight: bold;
                    color: #0073aa;
                    font-size: 14px;
                    margin-left: 15px;
                }
            </style>
            <script>
            jQuery(document).ready(function($) {
                // Create modal element for donors
                var modal = $('<div class="fg-donatori-modal"></div>');
                var modalContent = $('<div class="fg-donatori-modal-content"></div>');
                var modalHeader = $('<div class="fg-donatori-modal-header"><h2>Donatori della Raccolta</h2><button class="fg-donatori-modal-close">&times;</button></div>');
                var modalBody = $('<div class="fg-donatori-modal-body"></div>');
                
                modalContent.append(modalHeader).append(modalBody);
                modal.append(modalContent);
                $('body').append(modal);
                
                // Close modal handlers
                $('.fg-donatori-modal-close', modal).on('click', function() {
                    modal.hide();
                });
                $(modal).on('click', function(e) {
                    if (e.target === modal[0]) {
                        modal.hide();
                    }
                });
                
                // Click handler for donor count/amount
                $(document).on('click', '.fg-donatori-count', function(e) {
                    e.preventDefault();
                    var postId = $(this).data('post-id');
                    
                    // Show loading
                    modalBody.html('<p style="text-align:center;padding:20px;">Caricamento...</p>');
                    modal.show();
                    
                    // AJAX request to get donors
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fg_get_raccolta_donors',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('fg_get_donors'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.donors) {
                                var html = '';
                                
                                // Show extra funds if exists
                                if (response.data.fondi_extra && response.data.fondi_extra > 0) {
                                    html += '<div style="background: #e7f3ff; border: 2px solid #0073aa; padding: 12px; margin-bottom: 15px; border-radius: 4px;">';
                                    html += '<strong style="color: #0073aa;">Fondi Raccolti Extra Piattaforma: €' + response.data.fondi_extra.toFixed(2).replace('.', ',') + '</strong>';
                                    html += '</div>';
                                }
                                
                                $.each(response.data.donors, function(index, donor) {
                                    html += '<div class="fg-donatore-item">';
                                    html += '<div class="fg-donatore-info">';
                                    html += '<div class="fg-donatore-number">' + (index + 1) + '</div>';
                                    html += '<div class="fg-donatore-name">';
                                    if (donor.edit_link) {
                                        html += '<a href="' + donor.edit_link + '" target="_blank">' + donor.name + '</a>';
                                    } else {
                                        html += donor.name;
                                    }
                                    if (donor.tipo) {
                                        var badgeColor = donor.tipo === 'Socio' ? '#0073aa' : '#7e8993';
                                        html += '<span style="display: inline-block; margin-left: 8px; padding: 2px 6px; background: ' + badgeColor + '; color: white; border-radius: 3px; font-size: 10px; font-weight: bold;">' + donor.tipo + '</span>';
                                    }
                                    if (donor.date) {
                                        html += '<div style="font-size: 12px; color: #666; margin-top: 3px;">' + donor.date + '</div>';
                                    }
                                    html += '</div>';
                                    html += '</div>';
                                    html += '<div class="fg-donatore-amount">€' + donor.amount + '</div>';
                                    html += '</div>';
                                });
                                modalBody.html(html);
                            } else {
                                modalBody.html('<p style="text-align:center;padding:20px;">Nessun donatore trovato.</p>');
                            }
                        },
                        error: function() {
                            modalBody.html('<p style="text-align:center;padding:20px;color:#dc3545;">Errore nel caricamento dei donatori.</p>');
                        }
                    });
                });
            });
            </script>
            <?php
        }
        
        // Add member donations popup for soci
        if ($screen && $screen->post_type === 'fg_socio') {
            ?>
            <style>
                /* Member donations popup modal - reuse donor modal styles */
                .fg-donazioni-socio-modal {
                    display: none;
                    position: fixed;
                    z-index: 100000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                }
                .fg-donazioni-socio-modal-content {
                    background: #fff;
                    margin: 5% auto;
                    padding: 0;
                    border: 2px solid #0073aa;
                    border-radius: 5px;
                    width: 80%;
                    max-width: 600px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                }
                .fg-donazioni-socio-modal-header {
                    background: #0073aa;
                    color: #fff;
                    padding: 15px 20px;
                    border-radius: 3px 3px 0 0;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .fg-donazioni-socio-modal-header h2 {
                    margin: 0;
                    font-size: 18px;
                    color: #fff;
                }
                .fg-donazioni-socio-modal-close {
                    color: #fff;
                    font-size: 28px;
                    font-weight: bold;
                    cursor: pointer;
                    background: none;
                    border: none;
                    padding: 0;
                    line-height: 1;
                }
                .fg-donazioni-socio-modal-close:hover {
                    color: #ddd;
                }
                .fg-donazioni-socio-modal-body {
                    padding: 20px;
                    max-height: 60vh;
                    overflow-y: auto;
                }
                .fg-donazione-socio-item {
                    padding: 12px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                .fg-donazione-socio-item:last-child {
                    border-bottom: none;
                }
                .fg-donazione-socio-info {
                    display: flex;
                    align-items: center;
                    flex-grow: 1;
                }
                .fg-donazione-socio-number {
                    background: #0073aa;
                    color: #fff;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    margin-right: 15px;
                    flex-shrink: 0;
                }
                .fg-donazione-socio-details {
                    font-size: 14px;
                }
                .fg-donazione-socio-date {
                    font-size: 12px;
                    color: #666;
                    margin-top: 3px;
                }
                .fg-donazione-socio-amount {
                    font-weight: bold;
                    color: #0073aa;
                    font-size: 14px;
                    margin-left: 15px;
                }
            </style>
            <script>
            jQuery(document).ready(function($) {
                // Create modal element for member donations
                var modal = $('<div class="fg-donazioni-socio-modal"></div>');
                var modalContent = $('<div class="fg-donazioni-socio-modal-content"></div>');
                var modalHeader = $('<div class="fg-donazioni-socio-modal-header"><h2>Donazioni del Donatore</h2><button class="fg-donazioni-socio-modal-close">&times;</button></div>');
                var modalBody = $('<div class="fg-donazioni-socio-modal-body"></div>');
                
                modalContent.append(modalHeader).append(modalBody);
                modal.append(modalContent);
                $('body').append(modal);
                
                // Close modal handlers
                $('.fg-donazioni-socio-modal-close', modal).on('click', function() {
                    modal.hide();
                });
                $(modal).on('click', function(e) {
                    if (e.target === modal[0]) {
                        modal.hide();
                    }
                });
                
                // Click handler for member donation count
                $(document).on('click', '.fg-donazioni-socio-count', function(e) {
                    e.preventDefault();
                    var postId = $(this).data('post-id');
                    
                    // Show loading
                    modalBody.html('<p style="text-align:center;padding:20px;">Caricamento...</p>');
                    modal.show();
                    
                    // AJAX request to get member donations
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fg_get_socio_donations',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('fg_get_socio_donations'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.donations) {
                                var html = '';
                                $.each(response.data.donations, function(index, donation) {
                                    html += '<div class="fg-donazione-socio-item">';
                                    html += '<div class="fg-donazione-socio-info">';
                                    html += '<div class="fg-donazione-socio-number">' + (index + 1) + '</div>';
                                    html += '<div class="fg-donazione-socio-details">';
                                    html += '<div>' + donation.tipo + '</div>';
                                    if (donation.date) {
                                        html += '<div class="fg-donazione-socio-date">' + donation.date + '</div>';
                                    }
                                    html += '</div>';
                                    html += '</div>';
                                    html += '<div class="fg-donazione-socio-amount">€' + donation.amount + '</div>';
                                    html += '</div>';
                                });
                                modalBody.html(html);
                            } else {
                                modalBody.html('<p style="text-align:center;padding:20px;">Nessuna donazione trovata.</p>');
                            }
                        },
                        error: function() {
                            modalBody.html('<p style="text-align:center;padding:20px;color:#dc3545;">Errore nel caricamento delle donazioni.</p>');
                        }
                    });
                });
                
                // Event donations popup
                var donationsModal = $('<div class="fg-partecipanti-modal"></div>');
                var donationsModalContent = $('<div class="fg-partecipanti-modal-content"></div>');
                var donationsModalHeader = $('<div class="fg-partecipanti-modal-header"><h2>Donazioni per l\'Evento</h2><button class="fg-partecipanti-modal-close">&times;</button></div>');
                var donationsModalBody = $('<div class="fg-partecipanti-modal-body"></div>');
                
                donationsModalContent.append(donationsModalHeader).append(donationsModalBody);
                donationsModal.append(donationsModalContent);
                $('body').append(donationsModal);
                
                // Close donations modal handlers
                $('.fg-partecipanti-modal-close', donationsModal).on('click', function() {
                    donationsModal.hide();
                });
                $(donationsModal).on('click', function(e) {
                    if (e.target === donationsModal[0]) {
                        donationsModal.hide();
                    }
                });
                
                // Click handler for event donations
                $(document).on('click', '.fg-donatori-evento-count', function(e) {
                    e.preventDefault();
                    var eventoId = $(this).data('evento-id');
                    
                    // Show loading
                    donationsModalBody.html('<p style="text-align:center;padding:20px;">Caricamento...</p>');
                    donationsModal.show();
                    
                    // AJAX request to get donations
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'fg_get_evento_donations',
                            evento_id: eventoId,
                            nonce: '<?php echo wp_create_nonce('fg_admin_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data.donations) {
                                var html = '';
                                $.each(response.data.donations, function(index, donation) {
                                    html += '<div class="fg-partecipante-item">';
                                    html += '<div class="fg-partecipante-number">' + (index + 1) + '</div>';
                                    html += '<div class="fg-partecipante-name">';
                                    if (donation.socio_link) {
                                        html += '<a href="' + donation.socio_link + '" target="_blank">' + donation.socio_nome + '</a>';
                                    } else {
                                        html += donation.socio_nome;
                                    }
                                    html += '<br><small style="color:#666;">' + donation.data + '</small>';
                                    html += '</div>';
                                    html += '<div style="font-weight:bold;margin-left:auto;">€' + donation.importo + '</div>';
                                    html += '</div>';
                                });
                                donationsModalBody.html(html);
                            } else {
                                donationsModalBody.html('<p style="text-align:center;padding:20px;">Nessuna donazione trovata.</p>');
                            }
                        },
                        error: function() {
                            donationsModalBody.html('<p style="text-align:center;padding:20px;color:#dc3545;">Errore nel caricamento delle donazioni.</p>');
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }
}

// Initialize
new Friends_Gestionale_Post_Types();
