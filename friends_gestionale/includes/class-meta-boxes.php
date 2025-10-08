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
        
        // Hide default editor for custom post types
        add_action('admin_head', array($this, 'hide_default_editor'));
    }
    
    /**
     * Hide default WordPress editor for custom post types
     */
    public function hide_default_editor() {
        global $post_type;
        if (in_array($post_type, array('fg_socio', 'fg_pagamento', 'fg_evento'))) {
            remove_post_type_support($post_type, 'editor');
            remove_post_type_support($post_type, 'title');
        }
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
        
        // Eventi meta boxes
        add_meta_box(
            'fg_evento_info',
            __('Dettagli Evento', 'friends-gestionale'),
            array($this, 'render_evento_info_meta_box'),
            'fg_evento',
            'normal',
            'high'
        );
        
        add_meta_box(
            'fg_evento_partecipanti',
            __('Partecipanti', 'friends-gestionale'),
            array($this, 'render_evento_partecipanti_meta_box'),
            'fg_evento',
            'normal',
            'default'
        );
    }
    
    /**
     * Render Socio info meta box
     */
    public function render_socio_info_meta_box($post) {
        wp_nonce_field('fg_socio_meta_box', 'fg_socio_meta_box_nonce');
        
        $nome = get_post_meta($post->ID, '_fg_nome', true);
        $cognome = get_post_meta($post->ID, '_fg_cognome', true);
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
        <div class="fg-meta-box fg-improved-form">
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Dati Anagrafici', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_nome"><strong><?php _e('Nome:', 'friends-gestionale'); ?></strong> <span class="required">*</span></label>
                        <input type="text" id="fg_nome" name="fg_nome" value="<?php echo esc_attr($nome); ?>" class="widefat" required />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_cognome"><strong><?php _e('Cognome:', 'friends-gestionale'); ?></strong> <span class="required">*</span></label>
                        <input type="text" id="fg_cognome" name="fg_cognome" value="<?php echo esc_attr($cognome); ?>" class="widefat" required />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_codice_fiscale"><strong><?php _e('Codice Fiscale:', 'friends-gestionale'); ?></strong></label>
                        <input type="text" id="fg_codice_fiscale" name="fg_codice_fiscale" value="<?php echo esc_attr($codice_fiscale); ?>" class="widefat" maxlength="16" style="text-transform: uppercase;" />
                    </div>
                </div>
            </div>
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Contatti', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_email"><strong><?php _e('Email:', 'friends-gestionale'); ?></strong></label>
                        <input type="email" id="fg_email" name="fg_email" value="<?php echo esc_attr($email); ?>" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_telefono"><strong><?php _e('Telefono:', 'friends-gestionale'); ?></strong></label>
                        <input type="text" id="fg_telefono" name="fg_telefono" value="<?php echo esc_attr($telefono); ?>" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_indirizzo"><strong><?php _e('Indirizzo:', 'friends-gestionale'); ?></strong></label>
                        <textarea id="fg_indirizzo" name="fg_indirizzo" rows="3" class="widefat"><?php echo esc_textarea($indirizzo); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Iscrizione', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-third">
                        <label for="fg_data_iscrizione"><strong><?php _e('Data Iscrizione:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_iscrizione" name="fg_data_iscrizione" value="<?php echo esc_attr($data_iscrizione); ?>" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-third">
                        <label for="fg_data_scadenza"><strong><?php _e('Data Scadenza:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_scadenza" name="fg_data_scadenza" value="<?php echo esc_attr($data_scadenza); ?>" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-third">
                        <label for="fg_quota_annuale"><strong><?php _e('Quota Annuale (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_quota_annuale" name="fg_quota_annuale" value="<?php echo esc_attr($quota_annuale); ?>" step="0.01" min="0" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_stato"><strong><?php _e('Stato:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_stato" name="fg_stato" class="widefat">
                            <option value="attivo" <?php selected($stato, 'attivo'); ?>><?php _e('Attivo', 'friends-gestionale'); ?></option>
                            <option value="sospeso" <?php selected($stato, 'sospeso'); ?>><?php _e('Sospeso', 'friends-gestionale'); ?></option>
                            <option value="scaduto" <?php selected($stato, 'scaduto'); ?>><?php _e('Scaduto', 'friends-gestionale'); ?></option>
                            <option value="inattivo" <?php selected($stato, 'inattivo'); ?>><?php _e('Inattivo', 'friends-gestionale'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Note', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_note"><strong><?php _e('Note:', 'friends-gestionale'); ?></strong></label>
                        <textarea id="fg_note" name="fg_note" rows="4" class="widefat"><?php echo esc_textarea($note); ?></textarea>
                    </div>
                </div>
            </div>
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
     * Render Evento info meta box
     */
    public function render_evento_info_meta_box($post) {
        wp_nonce_field('fg_evento_meta_box', 'fg_evento_meta_box_nonce');
        
        $titolo_evento = get_post_meta($post->ID, '_fg_titolo_evento', true);
        $descrizione_evento = get_post_meta($post->ID, '_fg_descrizione_evento', true);
        $data_evento = get_post_meta($post->ID, '_fg_data_evento', true);
        $ora_evento = get_post_meta($post->ID, '_fg_ora_evento', true);
        $luogo = get_post_meta($post->ID, '_fg_luogo', true);
        $posti_disponibili = get_post_meta($post->ID, '_fg_posti_disponibili', true);
        $costo_partecipazione = get_post_meta($post->ID, '_fg_costo_partecipazione', true);
        $stato_evento = get_post_meta($post->ID, '_fg_stato_evento', true);
        ?>
        <div class="fg-meta-box fg-improved-form">
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Informazioni Evento', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_titolo_evento"><strong><?php _e('Titolo Evento:', 'friends-gestionale'); ?></strong> <span class="required">*</span></label>
                        <input type="text" id="fg_titolo_evento" name="fg_titolo_evento" value="<?php echo esc_attr($titolo_evento); ?>" class="widefat" required />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_descrizione_evento"><strong><?php _e('Descrizione:', 'friends-gestionale'); ?></strong></label>
                        <textarea id="fg_descrizione_evento" name="fg_descrizione_evento" rows="4" class="widefat"><?php echo esc_textarea($descrizione_evento); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Data e Luogo', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_data_evento"><strong><?php _e('Data Evento:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_evento" name="fg_data_evento" value="<?php echo esc_attr($data_evento); ?>" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_ora_evento"><strong><?php _e('Ora:', 'friends-gestionale'); ?></strong></label>
                        <input type="time" id="fg_ora_evento" name="fg_ora_evento" value="<?php echo esc_attr($ora_evento); ?>" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_luogo"><strong><?php _e('Luogo:', 'friends-gestionale'); ?></strong></label>
                        <input type="text" id="fg_luogo" name="fg_luogo" value="<?php echo esc_attr($luogo); ?>" class="widefat" />
                    </div>
                </div>
            </div>
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Dettagli Partecipazione', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_posti_disponibili"><strong><?php _e('Posti Disponibili:', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_posti_disponibili" name="fg_posti_disponibili" value="<?php echo esc_attr($posti_disponibili); ?>" min="0" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_costo_partecipazione"><strong><?php _e('Costo Partecipazione (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_costo_partecipazione" name="fg_costo_partecipazione" value="<?php echo esc_attr($costo_partecipazione); ?>" step="0.01" min="0" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_stato_evento"><strong><?php _e('Stato:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_stato_evento" name="fg_stato_evento" class="widefat">
                            <option value="programmato" <?php selected($stato_evento, 'programmato'); ?>><?php _e('Programmato', 'friends-gestionale'); ?></option>
                            <option value="in-corso" <?php selected($stato_evento, 'in-corso'); ?>><?php _e('In Corso', 'friends-gestionale'); ?></option>
                            <option value="completato" <?php selected($stato_evento, 'completato'); ?>><?php _e('Completato', 'friends-gestionale'); ?></option>
                            <option value="annullato" <?php selected($stato_evento, 'annullato'); ?>><?php _e('Annullato', 'friends-gestionale'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render Evento partecipanti meta box
     */
    public function render_evento_partecipanti_meta_box($post) {
        $partecipanti = get_post_meta($post->ID, '_fg_partecipanti', true);
        if (!is_array($partecipanti)) {
            $partecipanti = array();
        }
        
        // Get all members for dropdown
        $soci = get_posts(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        ?>
        <div class="fg-partecipanti-box">
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Aggiungi Partecipanti', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <select id="fg-add-partecipante" class="widefat">
                            <option value=""><?php _e('Seleziona un socio...', 'friends-gestionale'); ?></option>
                            <?php foreach ($soci as $socio): ?>
                                <option value="<?php echo $socio->ID; ?>"><?php echo esc_html($socio->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="button button-primary" id="fg-add-partecipante-btn" style="margin-top: 10px;">
                            <?php _e('Aggiungi Partecipante', 'friends-gestionale'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Lista Partecipanti', 'friends-gestionale'); ?> (<span id="fg-partecipanti-count"><?php echo count($partecipanti); ?></span>)</h3>
                <div id="fg-partecipanti-list" class="fg-partecipanti-list">
                    <?php if (!empty($partecipanti)): ?>
                        <?php foreach ($partecipanti as $socio_id): ?>
                            <?php
                            $socio = get_post($socio_id);
                            if ($socio):
                                $email = get_post_meta($socio_id, '_fg_email', true);
                            ?>
                                <div class="fg-partecipante-item" data-socio-id="<?php echo $socio_id; ?>">
                                    <span class="fg-partecipante-name"><?php echo esc_html($socio->post_title); ?></span>
                                    <?php if ($email): ?>
                                        <span class="fg-partecipante-email">(<?php echo esc_html($email); ?>)</span>
                                    <?php endif; ?>
                                    <button type="button" class="button fg-remove-partecipante" data-socio-id="<?php echo $socio_id; ?>">
                                        <?php _e('Rimuovi', 'friends-gestionale'); ?>
                                    </button>
                                    <input type="hidden" name="fg_partecipanti[]" value="<?php echo $socio_id; ?>" />
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="fg-no-partecipanti"><?php _e('Nessun partecipante aggiunto.', 'friends-gestionale'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($partecipanti)): ?>
                <div class="fg-form-section">
                    <button type="button" class="button button-large" id="fg-send-invites-btn">
                        <span class="dashicons dashicons-email" style="margin-top: 3px;"></span>
                        <?php _e('Invia Inviti Email a Tutti i Partecipanti', 'friends-gestionale'); ?>
                    </button>
                    <p class="description"><?php _e('Invia un\'email di invito all\'evento a tutti i partecipanti nella lista.', 'friends-gestionale'); ?></p>
                </div>
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
            
            // Update post title with nome + cognome
            if (isset($_POST['fg_nome']) && isset($_POST['fg_cognome'])) {
                $nome = sanitize_text_field($_POST['fg_nome']);
                $cognome = sanitize_text_field($_POST['fg_cognome']);
                $nome_completo = trim($nome . ' ' . $cognome);
                
                // Update post title
                remove_action('save_post', array($this, 'save_meta_boxes'), 10);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $nome_completo
                ));
                add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
                
                update_post_meta($post_id, '_fg_nome', $nome);
                update_post_meta($post_id, '_fg_cognome', $cognome);
            }
            
            if (isset($_POST['fg_codice_fiscale'])) {
                update_post_meta($post_id, '_fg_codice_fiscale', strtoupper(sanitize_text_field($_POST['fg_codice_fiscale'])));
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
        
        // Save Evento meta
        if ($post->post_type === 'fg_evento') {
            if (!isset($_POST['fg_evento_meta_box_nonce']) || !wp_verify_nonce($_POST['fg_evento_meta_box_nonce'], 'fg_evento_meta_box')) {
                return;
            }
            
            // Update post title with titolo_evento
            if (isset($_POST['fg_titolo_evento'])) {
                $titolo_evento = sanitize_text_field($_POST['fg_titolo_evento']);
                
                // Update post title
                remove_action('save_post', array($this, 'save_meta_boxes'), 10);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $titolo_evento
                ));
                add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
                
                update_post_meta($post_id, '_fg_titolo_evento', $titolo_evento);
            }
            
            if (isset($_POST['fg_descrizione_evento'])) {
                update_post_meta($post_id, '_fg_descrizione_evento', sanitize_textarea_field($_POST['fg_descrizione_evento']));
            }
            if (isset($_POST['fg_data_evento'])) {
                update_post_meta($post_id, '_fg_data_evento', sanitize_text_field($_POST['fg_data_evento']));
            }
            if (isset($_POST['fg_ora_evento'])) {
                update_post_meta($post_id, '_fg_ora_evento', sanitize_text_field($_POST['fg_ora_evento']));
            }
            if (isset($_POST['fg_luogo'])) {
                update_post_meta($post_id, '_fg_luogo', sanitize_text_field($_POST['fg_luogo']));
            }
            if (isset($_POST['fg_posti_disponibili'])) {
                update_post_meta($post_id, '_fg_posti_disponibili', intval($_POST['fg_posti_disponibili']));
            }
            if (isset($_POST['fg_costo_partecipazione'])) {
                update_post_meta($post_id, '_fg_costo_partecipazione', floatval($_POST['fg_costo_partecipazione']));
            }
            if (isset($_POST['fg_stato_evento'])) {
                update_post_meta($post_id, '_fg_stato_evento', sanitize_text_field($_POST['fg_stato_evento']));
            }
            if (isset($_POST['fg_partecipanti']) && is_array($_POST['fg_partecipanti'])) {
                $partecipanti = array_map('intval', $_POST['fg_partecipanti']);
                update_post_meta($post_id, '_fg_partecipanti', $partecipanti);
            } else {
                update_post_meta($post_id, '_fg_partecipanti', array());
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
