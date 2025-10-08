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
            'supports' => array('thumbnail'),
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
            'title' => __('Nome Completo', 'friends-gestionale'),
            'fg_email' => __('Email', 'friends-gestionale'),
            'fg_telefono' => __('Telefono', 'friends-gestionale'),
            'fg_codice_fiscale' => __('Codice Fiscale', 'friends-gestionale'),
            'fg_stato' => __('Stato', 'friends-gestionale'),
            'fg_data_iscrizione' => __('Data Iscrizione', 'friends-gestionale'),
            'fg_data_scadenza' => __('Data Scadenza', 'friends-gestionale'),
            'fg_quota_annuale' => __('Quota Annuale', 'friends-gestionale'),
            'taxonomy-fg_categoria_socio' => __('Categoria', 'friends-gestionale'),
            'date' => $columns['date']
        );
    }
    
    /**
     * Render custom columns for Soci
     */
    public function render_socio_columns($column, $post_id) {
        switch ($column) {
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
     * Set custom columns for Pagamenti
     */
    public function set_pagamento_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => __('Riferimento', 'friends-gestionale'),
            'fg_socio' => __('Socio', 'friends-gestionale'),
            'fg_importo' => __('Importo', 'friends-gestionale'),
            'fg_data_pagamento' => __('Data Pagamento', 'friends-gestionale'),
            'fg_metodo_pagamento' => __('Metodo', 'friends-gestionale'),
            'fg_tipo_pagamento' => __('Tipo', 'friends-gestionale'),
            'date' => $columns['date']
        );
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
                echo $raccolto ? '€' . number_format($raccolto, 2, ',', '.') : '-';
                break;
            case 'fg_progresso':
                $obiettivo = floatval(get_post_meta($post_id, '_fg_obiettivo', true));
                $raccolto = floatval(get_post_meta($post_id, '_fg_raccolto', true));
                if ($obiettivo > 0) {
                    $percentuale = ($raccolto / $obiettivo) * 100;
                    echo number_format($percentuale, 1) . '%';
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
                    echo count($partecipanti);
                } else {
                    echo '0';
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
}

// Initialize
new Friends_Gestionale_Post_Types();
