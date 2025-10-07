<?php
/**
 * Meta Boxes and Custom Fields
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Meta_Boxes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
        add_action('admin_init', array($this, 'setup_upload_handler'));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        // Soci meta boxes
        add_meta_box(
            'fg_socio_info',
            __('Informazioni Socio', 'friends-gestionale'),
            array($this, 'render_socio_info_meta_box'),
            'fg_socio',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fg_socio_documents',
            __('Documenti', 'friends-gestionale'),
            array($this, 'render_documents_meta_box'),
            'fg_socio',
            'side',
            'default'
        );
        
        // Pagamenti meta boxes
        add_meta_box(
            'fg_pagamento_info',
            __('Dettagli Pagamento', 'friends-gestionale'),
            array($this, 'render_pagamento_info_meta_box'),
            'fg_pagamento',
            'normal',
            'high'
        );
        
        // Raccolte Fondi meta boxes
        add_meta_box(
            'fg_raccolta_info',
            __('Dettagli Raccolta Fondi', 'friends-gestionale'),
            array($this, 'render_raccolta_info_meta_box'),
            'fg_raccolta',
            'normal',
            'high'
        );
    }
    
    /**
     * Render Socio info meta box
     */
    public function render_socio_info_meta_box($post) {
        wp_nonce_field('fg_socio_meta_box', 'fg_socio_meta_box_nonce');
        
        $codice_fiscale = get_post_meta($post->ID, '_fg_codice_fiscale', true);
        $email = get_post_meta($post->ID, '_fg_email', true);
        $telefono = get_post_meta($post->ID, '_fg_telefono', true);
        $indirizzo = get_post_meta($post->ID, '_fg_indirizzo', true);
        $data_iscrizione = get_post_meta($post->ID, '_fg_data_iscrizione', true);
        $data_scadenza = get_post_meta($post->ID, '_fg_data_scadenza', true);
        $quota_annuale = get_post_meta($post->ID, '_fg_quota_annuale', true);
        $stato = get_post_meta($post->ID, '_fg_stato', true);
        $note = get_post_meta($post->ID, '_fg_note', true);
        ?>
        <div class="fg-meta-box">
            <p>
                <label for="fg_codice_fiscale"><strong><?php _e('Codice Fiscale:', 'friends-gestionale'); ?></strong></label><br>
                <input type="text" id="fg_codice_fiscale" name="fg_codice_fiscale" value="<?php echo esc_attr($codice_fiscale); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_email"><strong><?php _e('Email:', 'friends-gestionale'); ?></strong></label><br>
                <input type="email" id="fg_email" name="fg_email" value="<?php echo esc_attr($email); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_telefono"><strong><?php _e('Telefono:', 'friends-gestionale'); ?></strong></label><br>
                <input type="text" id="fg_telefono" name="fg_telefono" value="<?php echo esc_attr($telefono); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_indirizzo"><strong><?php _e('Indirizzo:', 'friends-gestionale'); ?></strong></label><br>
                <textarea id="fg_indirizzo" name="fg_indirizzo" rows="3" class="widefat"><?php echo esc_textarea($indirizzo); ?></textarea>
            </p>
            
            <p>
                <label for="fg_data_iscrizione"><strong><?php _e('Data Iscrizione:', 'friends-gestionale'); ?></strong></label><br>
                <input type="date" id="fg_data_iscrizione" name="fg_data_iscrizione" value="<?php echo esc_attr($data_iscrizione); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_data_scadenza"><strong><?php _e('Data Scadenza:', 'friends-gestionale'); ?></strong></label><br>
                <input type="date" id="fg_data_scadenza" name="fg_data_scadenza" value="<?php echo esc_attr($data_scadenza); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_quota_annuale"><strong><?php _e('Quota Annuale (€):', 'friends-gestionale'); ?></strong></label><br>
                <input type="number" id="fg_quota_annuale" name="fg_quota_annuale" value="<?php echo esc_attr($quota_annuale); ?>" step="0.01" min="0" class="widefat" />
            </p>
            
            <p>
                <label for="fg_stato"><strong><?php _e('Stato:', 'friends-gestionale'); ?></strong></label><br>
                <select id="fg_stato" name="fg_stato" class="widefat">
                    <option value="attivo" <?php selected($stato, 'attivo'); ?>><?php _e('Attivo', 'friends-gestionale'); ?></option>
                    <option value="sospeso" <?php selected($stato, 'sospeso'); ?>><?php _e('Sospeso', 'friends-gestionale'); ?></option>
                    <option value="scaduto" <?php selected($stato, 'scaduto'); ?>><?php _e('Scaduto', 'friends-gestionale'); ?></option>
                    <option value="inattivo" <?php selected($stato, 'inattivo'); ?>><?php _e('Inattivo', 'friends-gestionale'); ?></option>
                </select>
            </p>
            
            <p>
                <label for="fg_note"><strong><?php _e('Note:', 'friends-gestionale'); ?></strong></label><br>
                <textarea id="fg_note" name="fg_note" rows="4" class="widefat"><?php echo esc_textarea($note); ?></textarea>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render documents meta box
     */
    public function render_documents_meta_box($post) {
        $documents = get_post_meta($post->ID, '_fg_documents', true);
        if (!is_array($documents)) {
            $documents = array();
        }
        ?>
        <div class="fg-documents-box">
            <div id="fg-documents-list">
                <?php foreach ($documents as $index => $doc): ?>
                    <div class="fg-document-item">
                        <a href="<?php echo esc_url($doc['url']); ?>" target="_blank"><?php echo esc_html($doc['name']); ?></a>
                        <button type="button" class="button fg-remove-document" data-index="<?php echo $index; ?>"><?php _e('Rimuovi', 'friends-gestionale'); ?></button>
                        <input type="hidden" name="fg_documents[<?php echo $index; ?>][url]" value="<?php echo esc_attr($doc['url']); ?>" />
                        <input type="hidden" name="fg_documents[<?php echo $index; ?>][name]" value="<?php echo esc_attr($doc['name']); ?>" />
                    </div>
                <?php endforeach; ?>
            </div>
            
            <p>
                <button type="button" class="button" id="fg-upload-document"><?php _e('Carica Documento', 'friends-gestionale'); ?></button>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render Pagamento info meta box
     */
    public function render_pagamento_info_meta_box($post) {
        wp_nonce_field('fg_pagamento_meta_box', 'fg_pagamento_meta_box_nonce');
        
        $socio_id = get_post_meta($post->ID, '_fg_socio_id', true);
        $importo = get_post_meta($post->ID, '_fg_importo', true);
        $data_pagamento = get_post_meta($post->ID, '_fg_data_pagamento', true);
        $metodo_pagamento = get_post_meta($post->ID, '_fg_metodo_pagamento', true);
        $tipo_pagamento = get_post_meta($post->ID, '_fg_tipo_pagamento', true);
        $note = get_post_meta($post->ID, '_fg_note', true);
        
        // Get all members for dropdown
        $soci = get_posts(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <div class="fg-meta-box">
            <p>
                <label for="fg_socio_id"><strong><?php _e('Socio:', 'friends-gestionale'); ?></strong></label><br>
                <select id="fg_socio_id" name="fg_socio_id" class="widefat">
                    <option value=""><?php _e('Seleziona Socio', 'friends-gestionale'); ?></option>
                    <?php foreach ($soci as $socio): ?>
                        <option value="<?php echo $socio->ID; ?>" <?php selected($socio_id, $socio->ID); ?>>
                            <?php echo esc_html($socio->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <p>
                <label for="fg_importo"><strong><?php _e('Importo (€):', 'friends-gestionale'); ?></strong></label><br>
                <input type="number" id="fg_importo" name="fg_importo" value="<?php echo esc_attr($importo); ?>" step="0.01" min="0" class="widefat" />
            </p>
            
            <p>
                <label for="fg_data_pagamento"><strong><?php _e('Data Pagamento:', 'friends-gestionale'); ?></strong></label><br>
                <input type="date" id="fg_data_pagamento" name="fg_data_pagamento" value="<?php echo esc_attr($data_pagamento); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_metodo_pagamento"><strong><?php _e('Metodo di Pagamento:', 'friends-gestionale'); ?></strong></label><br>
                <select id="fg_metodo_pagamento" name="fg_metodo_pagamento" class="widefat">
                    <option value="contanti" <?php selected($metodo_pagamento, 'contanti'); ?>><?php _e('Contanti', 'friends-gestionale'); ?></option>
                    <option value="bonifico" <?php selected($metodo_pagamento, 'bonifico'); ?>><?php _e('Bonifico Bancario', 'friends-gestionale'); ?></option>
                    <option value="carta" <?php selected($metodo_pagamento, 'carta'); ?>><?php _e('Carta di Credito', 'friends-gestionale'); ?></option>
                    <option value="paypal" <?php selected($metodo_pagamento, 'paypal'); ?>><?php _e('PayPal', 'friends-gestionale'); ?></option>
                    <option value="altro" <?php selected($metodo_pagamento, 'altro'); ?>><?php _e('Altro', 'friends-gestionale'); ?></option>
                </select>
            </p>
            
            <p>
                <label for="fg_tipo_pagamento"><strong><?php _e('Tipo di Pagamento:', 'friends-gestionale'); ?></strong></label><br>
                <select id="fg_tipo_pagamento" name="fg_tipo_pagamento" class="widefat">
                    <option value="quota" <?php selected($tipo_pagamento, 'quota'); ?>><?php _e('Quota Associativa', 'friends-gestionale'); ?></option>
                    <option value="donazione" <?php selected($tipo_pagamento, 'donazione'); ?>><?php _e('Donazione', 'friends-gestionale'); ?></option>
                    <option value="evento" <?php selected($tipo_pagamento, 'evento'); ?>><?php _e('Evento', 'friends-gestionale'); ?></option>
                    <option value="altro" <?php selected($tipo_pagamento, 'altro'); ?>><?php _e('Altro', 'friends-gestionale'); ?></option>
                </select>
            </p>
            
            <p>
                <label for="fg_note"><strong><?php _e('Note:', 'friends-gestionale'); ?></strong></label><br>
                <textarea id="fg_note" name="fg_note" rows="4" class="widefat"><?php echo esc_textarea($note); ?></textarea>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render Raccolta info meta box
     */
    public function render_raccolta_info_meta_box($post) {
        wp_nonce_field('fg_raccolta_meta_box', 'fg_raccolta_meta_box_nonce');
        
        $obiettivo = get_post_meta($post->ID, '_fg_obiettivo', true);
        $raccolto = get_post_meta($post->ID, '_fg_raccolto', true);
        $data_inizio = get_post_meta($post->ID, '_fg_data_inizio', true);
        $data_fine = get_post_meta($post->ID, '_fg_data_fine', true);
        $stato = get_post_meta($post->ID, '_fg_stato', true);
        ?>
        <div class="fg-meta-box">
            <p>
                <label for="fg_obiettivo"><strong><?php _e('Obiettivo (€):', 'friends-gestionale'); ?></strong></label><br>
                <input type="number" id="fg_obiettivo" name="fg_obiettivo" value="<?php echo esc_attr($obiettivo); ?>" step="0.01" min="0" class="widefat" />
            </p>
            
            <p>
                <label for="fg_raccolto"><strong><?php _e('Raccolto (€):', 'friends-gestionale'); ?></strong></label><br>
                <input type="number" id="fg_raccolto" name="fg_raccolto" value="<?php echo esc_attr($raccolto); ?>" step="0.01" min="0" class="widefat" />
            </p>
            
            <p>
                <label for="fg_data_inizio"><strong><?php _e('Data Inizio:', 'friends-gestionale'); ?></strong></label><br>
                <input type="date" id="fg_data_inizio" name="fg_data_inizio" value="<?php echo esc_attr($data_inizio); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_data_fine"><strong><?php _e('Data Fine:', 'friends-gestionale'); ?></strong></label><br>
                <input type="date" id="fg_data_fine" name="fg_data_fine" value="<?php echo esc_attr($data_fine); ?>" class="widefat" />
            </p>
            
            <p>
                <label for="fg_stato"><strong><?php _e('Stato:', 'friends-gestionale'); ?></strong></label><br>
                <select id="fg_stato" name="fg_stato" class="widefat">
                    <option value="attiva" <?php selected($stato, 'attiva'); ?>><?php _e('Attiva', 'friends-gestionale'); ?></option>
                    <option value="completata" <?php selected($stato, 'completata'); ?>><?php _e('Completata', 'friends-gestionale'); ?></option>
                    <option value="sospesa" <?php selected($stato, 'sospesa'); ?>><?php _e('Sospesa', 'friends-gestionale'); ?></option>
                </select>
            </p>
            
            <?php if ($obiettivo > 0): ?>
                <p>
                    <strong><?php _e('Progresso:', 'friends-gestionale'); ?></strong><br>
                    <div class="fg-progress-bar">
                        <div class="fg-progress-fill" style="width: <?php echo min(100, ($raccolto / $obiettivo) * 100); ?>%"></div>
                    </div>
                    <small><?php echo number_format($raccolto, 2); ?>€ / <?php echo number_format($obiettivo, 2); ?>€ (<?php echo number_format(($raccolto / $obiettivo) * 100, 1); ?>%)</small>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id, $post) {
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Save Socio meta
        if ($post->post_type === 'fg_socio') {
            if (!isset($_POST['fg_socio_meta_box_nonce']) || !wp_verify_nonce($_POST['fg_socio_meta_box_nonce'], 'fg_socio_meta_box')) {
                return;
            }
            
            if (isset($_POST['fg_codice_fiscale'])) {
                update_post_meta($post_id, '_fg_codice_fiscale', sanitize_text_field($_POST['fg_codice_fiscale']));
            }
            if (isset($_POST['fg_email'])) {
                update_post_meta($post_id, '_fg_email', sanitize_email($_POST['fg_email']));
            }
            if (isset($_POST['fg_telefono'])) {
                update_post_meta($post_id, '_fg_telefono', sanitize_text_field($_POST['fg_telefono']));
            }
            if (isset($_POST['fg_indirizzo'])) {
                update_post_meta($post_id, '_fg_indirizzo', sanitize_textarea_field($_POST['fg_indirizzo']));
            }
            if (isset($_POST['fg_data_iscrizione'])) {
                update_post_meta($post_id, '_fg_data_iscrizione', sanitize_text_field($_POST['fg_data_iscrizione']));
            }
            if (isset($_POST['fg_data_scadenza'])) {
                update_post_meta($post_id, '_fg_data_scadenza', sanitize_text_field($_POST['fg_data_scadenza']));
            }
            if (isset($_POST['fg_quota_annuale'])) {
                update_post_meta($post_id, '_fg_quota_annuale', floatval($_POST['fg_quota_annuale']));
            }
            if (isset($_POST['fg_stato'])) {
                update_post_meta($post_id, '_fg_stato', sanitize_text_field($_POST['fg_stato']));
            }
            if (isset($_POST['fg_note'])) {
                update_post_meta($post_id, '_fg_note', sanitize_textarea_field($_POST['fg_note']));
            }
            if (isset($_POST['fg_documents']) && is_array($_POST['fg_documents'])) {
                update_post_meta($post_id, '_fg_documents', array_map(function($doc) {
                    return array(
                        'url' => esc_url_raw($doc['url']),
                        'name' => sanitize_text_field($doc['name'])
                    );
                }, $_POST['fg_documents']));
            }
        }
        
        // Save Pagamento meta
        if ($post->post_type === 'fg_pagamento') {
            if (!isset($_POST['fg_pagamento_meta_box_nonce']) || !wp_verify_nonce($_POST['fg_pagamento_meta_box_nonce'], 'fg_pagamento_meta_box')) {
                return;
            }
            
            if (isset($_POST['fg_socio_id'])) {
                update_post_meta($post_id, '_fg_socio_id', absint($_POST['fg_socio_id']));
            }
            if (isset($_POST['fg_importo'])) {
                update_post_meta($post_id, '_fg_importo', floatval($_POST['fg_importo']));
            }
            if (isset($_POST['fg_data_pagamento'])) {
                update_post_meta($post_id, '_fg_data_pagamento', sanitize_text_field($_POST['fg_data_pagamento']));
            }
            if (isset($_POST['fg_metodo_pagamento'])) {
                update_post_meta($post_id, '_fg_metodo_pagamento', sanitize_text_field($_POST['fg_metodo_pagamento']));
            }
            if (isset($_POST['fg_tipo_pagamento'])) {
                update_post_meta($post_id, '_fg_tipo_pagamento', sanitize_text_field($_POST['fg_tipo_pagamento']));
            }
            if (isset($_POST['fg_note'])) {
                update_post_meta($post_id, '_fg_note', sanitize_textarea_field($_POST['fg_note']));
            }
        }
        
        // Save Raccolta meta
        if ($post->post_type === 'fg_raccolta') {
            if (!isset($_POST['fg_raccolta_meta_box_nonce']) || !wp_verify_nonce($_POST['fg_raccolta_meta_box_nonce'], 'fg_raccolta_meta_box')) {
                return;
            }
            
            if (isset($_POST['fg_obiettivo'])) {
                update_post_meta($post_id, '_fg_obiettivo', floatval($_POST['fg_obiettivo']));
            }
            if (isset($_POST['fg_raccolto'])) {
                update_post_meta($post_id, '_fg_raccolto', floatval($_POST['fg_raccolto']));
            }
            if (isset($_POST['fg_data_inizio'])) {
                update_post_meta($post_id, '_fg_data_inizio', sanitize_text_field($_POST['fg_data_inizio']));
            }
            if (isset($_POST['fg_data_fine'])) {
                update_post_meta($post_id, '_fg_data_fine', sanitize_text_field($_POST['fg_data_fine']));
            }
            if (isset($_POST['fg_stato'])) {
                update_post_meta($post_id, '_fg_stato', sanitize_text_field($_POST['fg_stato']));
            }
        }
    }
    
    /**
     * Setup upload handler
     */
    public function setup_upload_handler() {
        add_action('wp_ajax_fg_upload_document', array($this, 'handle_document_upload'));
    }
    
    /**
     * Handle document upload
     */
    public function handle_document_upload() {
        check_ajax_referer('friends_gestionale_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array(
                'url' => $movefile['url'],
                'name' => basename($movefile['file'])
            ));
        } else {
            wp_send_json_error($movefile['error']);
        }
    }
}

// Initialize
new Friends_Gestionale_Meta_Boxes();
