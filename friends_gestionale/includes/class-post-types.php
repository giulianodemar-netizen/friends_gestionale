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
        
        // Add filter dropdown for payment type
        add_action('restrict_manage_posts', array($this, 'add_pagamento_filters'));
        add_filter('parse_query', array($this, 'filter_pagamenti_by_type'));
        
        // Add admin footer script for participant popup
        add_action('admin_footer', array($this, 'add_partecipanti_popup_script'));
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
            case 'title':
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
    }
}

// Initialize
new Friends_Gestionale_Post_Types();
