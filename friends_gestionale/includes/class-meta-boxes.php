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
        add_action('before_delete_post', array($this, 'before_delete_payment'));
        
        // AJAX handlers for payment modal
        add_action('wp_ajax_fg_get_payment_form', array($this, 'ajax_get_payment_form'));
        add_action('wp_ajax_fg_save_payment', array($this, 'ajax_save_payment'));
        add_action('wp_ajax_fg_get_member_quota', array($this, 'ajax_get_member_quota'));
        
        // Hide default editor for custom post types
        add_action('admin_head', array($this, 'hide_default_editor'));
    }
    
    /**
     * Hide default WordPress editor for custom post types
     */
    public function hide_default_editor() {
        global $post_type;
        if (in_array($post_type, array('fg_socio', 'fg_pagamento', 'fg_evento', 'fg_raccolta'))) {
            remove_post_type_support($post_type, 'editor');
            remove_post_type_support($post_type, 'title');
        }
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        // Donatori meta boxes
        add_meta_box(
            'fg_socio_info',
            __('Informazioni Donatore', 'friends-gestionale'),
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
        // Convert datetime to date if needed (for backward compatibility with old data that has timestamps)
        if (!empty($data_iscrizione) && strlen($data_iscrizione) > 10) {
            $data_iscrizione = substr($data_iscrizione, 0, 10);
        }
        // Default to today's date for new records
        if (empty($data_iscrizione)) {
            $data_iscrizione = date('Y-m-d');
        }
        $data_scadenza = get_post_meta($post->ID, '_fg_data_scadenza', true);
        $quota_annuale = get_post_meta($post->ID, '_fg_quota_annuale', true);
        $stato = get_post_meta($post->ID, '_fg_stato', true);
        $note = get_post_meta($post->ID, '_fg_note', true);
        $tipo_donatore = get_post_meta($post->ID, '_fg_tipo_donatore', true);
        if (empty($tipo_donatore)) {
            $tipo_donatore = 'anche_socio'; // Default to member for backward compatibility
        }
        $tipo_persona = get_post_meta($post->ID, '_fg_tipo_persona', true);
        if (empty($tipo_persona)) {
            $tipo_persona = 'privato'; // Default to individual
        }
        $ragione_sociale = get_post_meta($post->ID, '_fg_ragione_sociale', true);
        ?>
        <div class="fg-meta-box fg-improved-form">
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Tipo Donatore', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_tipo_donatore"><strong><?php _e('Questa persona è:', 'friends-gestionale'); ?></strong> <span class="required">*</span></label>
                        <select id="fg_tipo_donatore" name="fg_tipo_donatore" class="widefat" required>
                            <option value="solo_donatore" <?php selected($tipo_donatore, 'solo_donatore'); ?>><?php _e('Donatore', 'friends-gestionale'); ?></option>
                            <option value="anche_socio" <?php selected($tipo_donatore, 'anche_socio'); ?>><?php _e('Socio', 'friends-gestionale'); ?></option>
                        </select>
                        <p class="description"><?php _e('Seleziona se questa persona è solo un donatore o anche un socio dell\'associazione.', 'friends-gestionale'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Dati Anagrafici', 'friends-gestionale'); ?></h3>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label><strong><?php _e('Tipo Persona:', 'friends-gestionale'); ?></strong></label>
                        <div style="margin-top: 8px;">
                            <label style="display: inline-block; margin-right: 20px;">
                                <input type="radio" name="fg_tipo_persona" value="privato" <?php checked($tipo_persona, 'privato'); ?> id="fg_tipo_persona_privato" />
                                <?php _e('Privato', 'friends-gestionale'); ?>
                            </label>
                            <label style="display: inline-block;">
                                <input type="radio" name="fg_tipo_persona" value="societa" <?php checked($tipo_persona, 'societa'); ?> id="fg_tipo_persona_societa" />
                                <?php _e('Società', 'friends-gestionale'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Ragione Sociale field - shown only for società -->
                <div class="fg-form-row fg-ragione-sociale-field" style="display: <?php echo $tipo_persona === 'societa' ? 'block' : 'none'; ?>;">
                    <div class="fg-form-field">
                        <label for="fg_ragione_sociale"><strong><?php _e('Ragione Sociale:', 'friends-gestionale'); ?></strong> <span class="required">*</span></label>
                        <input type="text" id="fg_ragione_sociale" name="fg_ragione_sociale" value="<?php echo esc_attr($ragione_sociale); ?>" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_nome" id="fg_nome_label"><strong><?php echo $tipo_persona === 'societa' ? __('Nome Referente:', 'friends-gestionale') : __('Nome:', 'friends-gestionale'); ?></strong> <span class="required fg-nome-required" style="display: <?php echo $tipo_persona === 'societa' ? 'none' : 'inline'; ?>;">*</span></label>
                        <input type="text" id="fg_nome" name="fg_nome" value="<?php echo esc_attr($nome); ?>" class="widefat" <?php echo $tipo_persona !== 'societa' ? 'required' : ''; ?> />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_cognome" id="fg_cognome_label"><strong><?php echo $tipo_persona === 'societa' ? __('Cognome Referente:', 'friends-gestionale') : __('Cognome:', 'friends-gestionale'); ?></strong> <span class="required fg-cognome-required" style="display: <?php echo $tipo_persona === 'societa' ? 'none' : 'inline'; ?>;">*</span></label>
                        <input type="text" id="fg_cognome" name="fg_cognome" value="<?php echo esc_attr($cognome); ?>" class="widefat" <?php echo $tipo_persona !== 'societa' ? 'required' : ''; ?> />
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
            
            <!-- Categoria Donatore section - shown only for solo_donatore -->
            <div class="fg-form-section fg-categoria-donatore-section" style="display: <?php echo $tipo_donatore === 'solo_donatore' ? 'block' : 'none'; ?>;">
                <h3 class="fg-section-title"><?php _e('Categoria Donatore', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_categoria_donatore_selector"><strong><?php _e('Categoria:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_categoria_donatore_selector" name="fg_categoria_donatore_selector" class="widefat">
                            <option value=""><?php _e('Seleziona categoria...', 'friends-gestionale'); ?></option>
                            <?php
                            $donor_categories = get_terms(array(
                                'taxonomy' => 'fg_categoria_donatore',
                                'hide_empty' => false
                            ));
                            $member_donor_categories = wp_get_post_terms($post->ID, 'fg_categoria_donatore', array('fields' => 'ids'));
                            $selected_donor_category = !empty($member_donor_categories) ? $member_donor_categories[0] : '';
                            
                            if (!empty($donor_categories) && !is_wp_error($donor_categories)) {
                                foreach ($donor_categories as $cat) {
                                    printf(
                                        '<option value="%d" %s>%s</option>',
                                        $cat->term_id,
                                        selected($selected_donor_category, $cat->term_id, false),
                                        esc_html($cat->name)
                                    );
                                }
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e('Seleziona la categoria del donatore.', 'friends-gestionale'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Data Iscrizione - always visible for all types -->
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Data Iscrizione', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_data_iscrizione"><strong><?php _e('Data Iscrizione:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_iscrizione" name="fg_data_iscrizione" value="<?php echo esc_attr($data_iscrizione); ?>" class="widefat" />
                        <p class="description"><?php _e('Data di registrazione.', 'friends-gestionale'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Iscrizione section - shown only for anche_socio -->
            <div class="fg-form-section fg-iscrizione-section" style="display: <?php echo $tipo_donatore === 'anche_socio' ? 'block' : 'none'; ?>;">
                <h3 class="fg-section-title"><?php _e('Iscrizione', 'friends-gestionale'); ?></h3>
                
                <!-- Category selector for auto-filling quota -->
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_categoria_socio_selector"><strong><?php _e('Tipologia Socio (per quota automatica):', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_categoria_socio_selector" name="fg_categoria_socio_selector" class="widefat">
                            <option value=""><?php _e('Seleziona categoria...', 'friends-gestionale'); ?></option>
                            <?php
                            $categories = get_terms(array(
                                'taxonomy' => 'fg_categoria_socio',
                                'hide_empty' => false
                            ));
                            $member_categories = wp_get_post_terms($post->ID, 'fg_categoria_socio', array('fields' => 'ids'));
                            $selected_category = !empty($member_categories) ? $member_categories[0] : '';
                            
                            if (!empty($categories) && !is_wp_error($categories)) {
                                foreach ($categories as $cat) {
                                    $quota = get_term_meta($cat->term_id, 'fg_quota_associativa', true);
                                    $label = $cat->name;
                                    if ($quota) {
                                        $label .= ' - €' . number_format($quota, 2, ',', '.');
                                    }
                                    printf(
                                        '<option value="%d" data-quota="%s" %s>%s</option>',
                                        $cat->term_id,
                                        esc_attr($quota),
                                        selected($selected_category, $cat->term_id, false),
                                        esc_html($label)
                                    );
                                }
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e('Selezionando una categoria, la quota annuale verrà compilata automaticamente.', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_data_scadenza"><strong><?php _e('Data Scadenza:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_scadenza" name="fg_data_scadenza" value="<?php echo esc_attr($data_scadenza); ?>" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_quota_annuale"><strong><?php _e('Quota Annuale (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_quota_annuale" name="fg_quota_annuale" value="<?php echo esc_attr($quota_annuale); ?>" step="0.01" min="0" class="widefat" readonly style="background-color: #f0f0f0;" />
                        <p class="description"><?php _e('La quota viene calcolata automaticamente dalla categoria del socio.', 'friends-gestionale'); ?></p>
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
            
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Foto Donatore', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <?php
                        $foto_id = get_post_thumbnail_id($post->ID);
                        if ($foto_id) {
                            $foto_url = wp_get_attachment_image_src($foto_id, 'medium');
                            ?>
                            <div class="fg-foto-preview" style="margin-bottom: 10px;">
                                <img src="<?php echo esc_url($foto_url[0]); ?>" style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px;" />
                            </div>
                            <?php
                        }
                        ?>
                        <button type="button" class="button" id="fg_upload_foto_button"><?php _e('Carica Foto', 'friends-gestionale'); ?></button>
                        <button type="button" class="button" id="fg_remove_foto_button" style="<?php echo !$foto_id ? 'display:none;' : ''; ?>"><?php _e('Rimuovi Foto', 'friends-gestionale'); ?></button>
                        <input type="hidden" id="fg_foto_id" name="fg_foto_id" value="<?php echo esc_attr($foto_id); ?>" />
                    </div>
                </div>
            </div>
            
            <?php
            // Display donations section if this is an existing member
            if ($post->ID > 0):
                // Get all payments for this member, ordered by payment date
                $payments = get_posts(array(
                    'post_type' => 'fg_pagamento',
                    'posts_per_page' => -1,
                    'orderby' => 'meta_value',
                    'meta_key' => '_fg_data_pagamento',
                    'meta_type' => 'DATE',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => '_fg_socio_id',
                            'value' => $post->ID,
                            'compare' => '='
                        )
                    )
                ));
                
                $total_donato = 0;
                foreach ($payments as $payment) {
                    $importo = get_post_meta($payment->ID, '_fg_importo', true);
                    $total_donato += floatval($importo);
                }
            ?>
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Riepilogo Donazioni', 'friends-gestionale'); ?></h3>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label><strong><?php _e('Totale Donato:', 'friends-gestionale'); ?></strong></label>
                        <p style="font-size: 18px; font-weight: bold; color: #0073aa; margin: 10px 0;">
                            €<?php echo number_format($total_donato, 2, ',', '.'); ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($payments)): ?>
                    <div class="fg-form-row">
                        <div class="fg-form-field">
                            <label><strong><?php _e('Elenco Donazioni:', 'friends-gestionale'); ?></strong></label>
                            <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 15px; max-height: 300px; overflow-y: auto;">
                                <?php 
                                $tipo_labels = array(
                                    'quota' => 'Quota Associativa',
                                    'donazione' => 'Donazione singola',
                                    'raccolta' => 'Raccolta Fondi',
                                    'evento' => 'Evento',
                                    'altro' => 'Altro'
                                );
                                
                                foreach ($payments as $payment):
                                    $importo = get_post_meta($payment->ID, '_fg_importo', true);
                                    $data_pagamento = get_post_meta($payment->ID, '_fg_data_pagamento', true);
                                    $tipo_pagamento = get_post_meta($payment->ID, '_fg_tipo_pagamento', true);
                                    $nota = get_post_meta($payment->ID, '_fg_note', true);
                                    
                                    // Determine label, display text, and badge color based on payment type
                                    $badge_label = '';
                                    $badge_color = '#0073aa'; // Default blue
                                    $display_text = '';
                                    
                                    if ($tipo_pagamento === 'evento') {
                                        // Event: Show badge "Evento" and event name
                                        $badge_label = 'Evento';
                                        $badge_color = '#9b51e0'; // Purple
                                        
                                        $evento_id = get_post_meta($payment->ID, '_fg_evento_id', true);
                                        $evento_custom = get_post_meta($payment->ID, '_fg_evento_custom', true);
                                        
                                        if ($evento_custom) {
                                            $display_text = $evento_custom;
                                        } elseif ($evento_id && $evento_id !== 'altro_evento') {
                                            $evento_titolo = get_post_meta($evento_id, '_fg_titolo_evento', true);
                                            if (!$evento_titolo) {
                                                $evento_titolo = get_the_title($evento_id);
                                            }
                                            $display_text = $evento_titolo ? $evento_titolo : 'Evento';
                                        } else {
                                            $display_text = 'Evento';
                                        }
                                    } elseif ($tipo_pagamento === 'donazione') {
                                        // Single donation: Show badge "Donazione Singola" and note
                                        $badge_label = 'Donazione Singola';
                                        $badge_color = '#00a86b'; // Green
                                        $display_text = $nota ? $nota : 'Donazione';
                                    } elseif ($tipo_pagamento === 'raccolta') {
                                        // Fundraising: Show badge "Raccolta Fondi" and fundraising name
                                        $badge_label = 'Raccolta Fondi';
                                        $badge_color = '#e74c3c'; // Red
                                        
                                        $raccolta_id = get_post_meta($payment->ID, '_fg_raccolta_id', true);
                                        if ($raccolta_id) {
                                            $raccolta = get_post($raccolta_id);
                                            $display_text = $raccolta ? get_the_title($raccolta) : 'Raccolta Fondi';
                                        } else {
                                            $display_text = 'Raccolta Fondi';
                                        }
                                    } elseif ($tipo_pagamento === 'altro') {
                                        // Other: Show badge "Altro" and note
                                        $badge_label = 'Altro';
                                        $badge_color = '#95a5a6'; // Gray
                                        $display_text = $nota ? $nota : 'Pagamento';
                                    } elseif ($tipo_pagamento === 'quota') {
                                        // Membership fee
                                        $badge_label = 'Quota Associativa';
                                        $badge_color = '#3498db'; // Blue
                                        $display_text = 'Quota Associativa';
                                    } else {
                                        // Default fallback
                                        $badge_label = 'Pagamento';
                                        $badge_color = '#0073aa';
                                        $display_text = 'Pagamento';
                                    }
                                ?>
                                    <div style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                        <div style="flex: 1;">
                                            <div style="margin-bottom: 5px;">
                                                <span style="display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; color: #fff; background-color: <?php echo $badge_color; ?>; margin-right: 5px;">
                                                    <?php echo esc_html($badge_label); ?>
                                                </span>
                                                <strong><?php echo esc_html($display_text); ?></strong>
                                            </div>
                                            <?php if ($data_pagamento): ?>
                                                <small style="color: #666; display: block; margin-top: 3px;">
                                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data_pagamento))); ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if (!empty($nota)): ?>
                                                <div style="margin-top: 5px; padding: 5px; background: #f0f0f0; border-radius: 3px;">
                                                    <small style="color: #555;">
                                                        <strong><?php _e('Nota:', 'friends-gestionale'); ?></strong> <?php echo esc_html($nota); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="text-align: right; margin-left: 15px;">
                                            <strong style="color: #0073aa; font-size: 14px; display: block; margin-bottom: 5px;">
                                                €<?php echo number_format(floatval($importo), 2, ',', '.'); ?>
                                            </strong>
                                            <a href="<?php echo get_edit_post_link($payment->ID); ?>" target="_blank" rel="noopener noreferrer" class="button button-small" style="font-size: 11px;">
                                                <?php _e('Dettaglio', 'friends-gestionale'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="fg-form-row">
                        <div class="fg-form-field">
                            <p style="color: #666; font-style: italic;"><?php _e('Nessuna donazione ancora per questo socio.', 'friends-gestionale'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Add Payment Button -->
                <div class="fg-form-row" style="margin-top: 15px;">
                    <div class="fg-form-field">
                        <button type="button" id="fg_add_payment_btn" class="button button-primary" data-donor-id="<?php echo $post->ID; ?>" data-donor-name="<?php echo esc_attr($post->post_title); ?>">
                            <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span>
                            <?php _e('Aggiungi Nuovo Pagamento', 'friends-gestionale'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
        $evento_id = get_post_meta($post->ID, '_fg_evento_id', true);
        $evento_custom = get_post_meta($post->ID, '_fg_evento_custom', true);
        $categoria_socio_id = get_post_meta($post->ID, '_fg_categoria_socio_id', true);
        $raccolta_id = get_post_meta($post->ID, '_fg_raccolta_id', true);
        $note = get_post_meta($post->ID, '_fg_note', true);
        
        // Get all members for dropdown
        $soci = get_posts(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Get all events for dropdown
        $eventi = get_posts(array(
            'post_type' => 'fg_evento',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Get all fundraising campaigns for dropdown
        $raccolte = get_posts(array(
            'post_type' => 'fg_raccolta',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Get all member categories
        $categorie = get_terms(array(
            'taxonomy' => 'fg_categoria_socio',
            'hide_empty' => false
        ));
        ?>
        <div class="fg-meta-box fg-improved-form">
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Informazioni Pagamento', 'friends-gestionale'); ?></h3>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_socio_id"><strong><?php _e('Donatore:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_socio_id" name="fg_socio_id" class="widefat fg-select2-donor">
                            <option value=""><?php _e('Seleziona Donatore', 'friends-gestionale'); ?></option>
                            <?php foreach ($soci as $socio): 
                                $tipo_donatore = get_post_meta($socio->ID, '_fg_tipo_donatore', true);
                                if (empty($tipo_donatore)) {
                                    $tipo_donatore = 'anche_socio'; // Default
                                }
                                $tipo_label = ($tipo_donatore === 'anche_socio') ? ' [Socio]' : ' [Donatore]';
                            ?>
                                <option value="<?php echo $socio->ID; ?>" <?php selected($socio_id, $socio->ID); ?>>
                                    <?php echo esc_html($socio->post_title . $tipo_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Cerca per nome. [Socio] indica un donatore che è anche socio, [Donatore] indica un donatore semplice.', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_importo"><strong><?php _e('Importo (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_importo" name="fg_importo" value="<?php echo esc_attr($importo); ?>" step="0.01" min="0" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_data_pagamento"><strong><?php _e('Data Pagamento:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_pagamento" name="fg_data_pagamento" value="<?php echo esc_attr($data_pagamento ? $data_pagamento : date('Y-m-d')); ?>" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_metodo_pagamento"><strong><?php _e('Metodo di Pagamento:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_metodo_pagamento" name="fg_metodo_pagamento" class="widefat" required>
                            <option value=""><?php _e('Seleziona metodo di pagamento', 'friends-gestionale'); ?></option>
                            <option value="contanti" <?php selected($metodo_pagamento, 'contanti'); ?>><?php _e('Contanti', 'friends-gestionale'); ?></option>
                            <option value="bonifico" <?php selected($metodo_pagamento, 'bonifico'); ?>><?php _e('Bonifico Bancario', 'friends-gestionale'); ?></option>
                            <option value="carta" <?php selected($metodo_pagamento, 'carta'); ?>><?php _e('Carta di Credito', 'friends-gestionale'); ?></option>
                            <option value="paypal" <?php selected($metodo_pagamento, 'paypal'); ?>><?php _e('PayPal', 'friends-gestionale'); ?></option>
                            <option value="altro" <?php selected($metodo_pagamento, 'altro'); ?>><?php _e('Altro', 'friends-gestionale'); ?></option>
                        </select>
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_tipo_pagamento"><strong><?php _e('Tipo di Pagamento:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_tipo_pagamento" name="fg_tipo_pagamento" class="widefat" required>
                            <option value=""><?php _e('Seleziona tipo di pagamento', 'friends-gestionale'); ?></option>
                            <option value="quota" <?php selected($tipo_pagamento, 'quota'); ?>><?php _e('Quota Associativa', 'friends-gestionale'); ?></option>
                            <option value="donazione" <?php selected($tipo_pagamento, 'donazione'); ?>><?php _e('Donazione singola', 'friends-gestionale'); ?></option>
                            <option value="raccolta" <?php selected($tipo_pagamento, 'raccolta'); ?>><?php _e('Raccolta Fondi', 'friends-gestionale'); ?></option>
                            <option value="evento" <?php selected($tipo_pagamento, 'evento'); ?>><?php _e('Evento', 'friends-gestionale'); ?></option>
                            <option value="altro" <?php selected($tipo_pagamento, 'altro'); ?>><?php _e('Altro', 'friends-gestionale'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="fg-form-row" id="fg_evento_field" style="display: none;">
                    <div class="fg-form-field">
                        <label for="fg_evento_id"><strong><?php _e('Seleziona Evento:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_evento_id" name="fg_evento_id" class="widefat">
                            <option value=""><?php _e('Seleziona Evento', 'friends-gestionale'); ?></option>
                            <?php foreach ($eventi as $evento): ?>
                                <option value="<?php echo $evento->ID; ?>" <?php selected($evento_id, $evento->ID); ?>>
                                    <?php echo esc_html($evento->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="altro_evento" <?php selected($evento_id, 'altro_evento'); ?>><?php _e('Altro Evento', 'friends-gestionale'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="fg-form-row" id="fg_evento_custom_field" style="display: none;">
                    <div class="fg-form-field">
                        <label for="fg_evento_custom"><strong><?php _e('Titolo Evento Personalizzato:', 'friends-gestionale'); ?></strong></label>
                        <input type="text" id="fg_evento_custom" name="fg_evento_custom" value="<?php echo esc_attr($evento_custom); ?>" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row" id="fg_raccolta_field" style="display: none;">
                    <div class="fg-form-field">
                        <label for="fg_raccolta_id"><strong><?php _e('Seleziona Raccolta Fondi:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_raccolta_id" name="fg_raccolta_id" class="widefat">
                            <option value=""><?php _e('Seleziona Raccolta Fondi', 'friends-gestionale'); ?></option>
                            <?php foreach ($raccolte as $raccolta): ?>
                                <option value="<?php echo $raccolta->ID; ?>" <?php selected($raccolta_id, $raccolta->ID); ?>>
                                    <?php echo esc_html($raccolta->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="fg-form-row" id="fg_categoria_socio_field" style="display: none;">
                    <div class="fg-form-field">
                        <label for="fg_categoria_socio_id">
                            <strong><?php _e('Categoria Socio:', 'friends-gestionale'); ?></strong>
                            <button type="button" id="fg_unlock_categoria_socio" class="button button-small" style="margin-left: 10px;">
                                <span class="dashicons dashicons-lock" style="margin-top: 3px;"></span>
                                <?php _e('Sblocca', 'friends-gestionale'); ?>
                            </button>
                        </label>
                        <select id="fg_categoria_socio_id" name="fg_categoria_socio_id" class="widefat" disabled>
                            <option value=""><?php _e('Seleziona Categoria', 'friends-gestionale'); ?></option>
                            <?php if (!empty($categorie) && !is_wp_error($categorie)): ?>
                                <?php foreach ($categorie as $categoria): ?>
                                    <option value="<?php echo $categoria->term_id; ?>" <?php selected($categoria_socio_id, $categoria->term_id); ?>>
                                        <?php echo esc_html($categoria->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <p class="description" id="fg_categoria_socio_description" style="color: #666;"><?php _e('Clicca su "Sblocca" per modificare la categoria. La modifica aggiornerà anche la tipologia socio del donatore.', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <!-- Warning message for quota payments -->
                <div class="fg-form-row" id="fg_quota_warning" style="display: none;">
                    <div class="notice notice-info inline" style="margin: 0; padding: 12px;">
                        <p style="margin: 0;">
                            <strong><?php _e('Attenzione:', 'friends-gestionale'); ?></strong>
                            <?php _e('L\'inserimento di questo pagamento comporterà l\'automatico aggiornamento della data di scadenza del socio di un anno rispetto alla scadenza attuale. È comunque possibile modificare manualmente la data di scadenza dalla sezione Donatori.', 'friends-gestionale'); ?>
                        </p>
                    </div>
                </div>
                
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
     * Render Raccolta info meta box
     */
    public function render_raccolta_info_meta_box($post) {
        wp_nonce_field('fg_raccolta_meta_box', 'fg_raccolta_meta_box_nonce');
        
        $titolo_raccolta = get_post_meta($post->ID, '_fg_titolo_raccolta', true);
        if (empty($titolo_raccolta)) {
            $titolo_raccolta = $post->post_title;
        }
        $obiettivo = get_post_meta($post->ID, '_fg_obiettivo', true);
        $raccolto = get_post_meta($post->ID, '_fg_raccolto', true);
        $fondi_extra = get_post_meta($post->ID, '_fg_fondi_extra', true);
        $data_inizio = get_post_meta($post->ID, '_fg_data_inizio', true);
        $data_fine = get_post_meta($post->ID, '_fg_data_fine', true);
        $stato = get_post_meta($post->ID, '_fg_stato', true);
        
        // Calculate total collected (auto + extra)
        $totale_raccolto = floatval($raccolto) + floatval($fondi_extra);
        ?>
        <div class="fg-meta-box fg-improved-form">
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Dettagli Raccolta Fondi', 'friends-gestionale'); ?></h3>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_titolo_raccolta"><strong><?php _e('Titolo Raccolta:', 'friends-gestionale'); ?></strong> <span class="required">*</span></label>
                        <input type="text" id="fg_titolo_raccolta" name="fg_titolo_raccolta" value="<?php echo esc_attr($titolo_raccolta); ?>" class="widefat" required />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_obiettivo"><strong><?php _e('Obiettivo (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_obiettivo" name="fg_obiettivo" value="<?php echo esc_attr($obiettivo); ?>" step="0.01" min="0" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_raccolto"><strong><?php _e('Raccolto Piattaforma (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_raccolto" name="fg_raccolto" value="<?php echo esc_attr($raccolto); ?>" step="0.01" min="0" class="widefat" readonly style="background-color: #f0f0f0;" />
                        <small style="color: #666;"><?php _e('Calcolato automaticamente dai pagamenti', 'friends-gestionale'); ?></small>
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_fondi_extra"><strong><?php _e('Fondi Raccolti Extra (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_fondi_extra" name="fg_fondi_extra" value="<?php echo esc_attr($fondi_extra); ?>" step="0.01" min="0" class="widefat" />
                        <small style="color: #666;"><?php _e('Fondi raccolti al di fuori della piattaforma', 'friends-gestionale'); ?></small>
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_totale_raccolto"><strong><?php _e('Totale Raccolto (€):', 'friends-gestionale'); ?></strong></label>
                        <input type="number" id="fg_totale_raccolto" name="fg_totale_raccolto" value="<?php echo esc_attr($totale_raccolto); ?>" step="0.01" min="0" class="widefat" readonly style="background-color: #f0f0f0;" />
                        <small style="color: #666;"><?php _e('Piattaforma + Extra', 'friends-gestionale'); ?></small>
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_data_inizio"><strong><?php _e('Data Inizio:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_inizio" name="fg_data_inizio" value="<?php echo esc_attr($data_inizio); ?>" class="widefat" />
                    </div>
                    <div class="fg-form-field fg-field-half">
                        <label for="fg_data_fine"><strong><?php _e('Data Fine:', 'friends-gestionale'); ?></strong></label>
                        <input type="date" id="fg_data_fine" name="fg_data_fine" value="<?php echo esc_attr($data_fine); ?>" class="widefat" />
                    </div>
                </div>
                
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label for="fg_stato"><strong><?php _e('Stato:', 'friends-gestionale'); ?></strong></label>
                        <select id="fg_stato" name="fg_stato" class="widefat">
                            <option value="attiva" <?php selected($stato, 'attiva'); ?>><?php _e('Attiva', 'friends-gestionale'); ?></option>
                            <option value="completata" <?php selected($stato, 'completata'); ?>><?php _e('Completata', 'friends-gestionale'); ?></option>
                            <option value="sospesa" <?php selected($stato, 'sospesa'); ?>><?php _e('Sospesa', 'friends-gestionale'); ?></option>
                        </select>
                    </div>
                </div>
                
                <?php if ($obiettivo > 0): ?>
                    <div class="fg-form-row">
                        <div class="fg-form-field">
                            <label><strong><?php _e('Progresso:', 'friends-gestionale'); ?></strong></label>
                            <div class="fg-progress-bar">
                                <div class="fg-progress-fill" style="width: <?php echo min(100, ($raccolto / $obiettivo) * 100); ?>%"></div>
                            </div>
                            <small><?php echo number_format($raccolto, 2); ?>€ / <?php echo number_format($obiettivo, 2); ?>€ (<?php echo number_format(($raccolto / $obiettivo) * 100, 1); ?>%)</small>
                            <?php if ($fondi_extra > 0): ?>
                                <div style="margin-top: 10px; padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa; border-radius: 3px;">
                                    <strong><?php _e('Fondi Raccolti Extra Piattaforma:', 'friends-gestionale'); ?></strong> €<?php echo number_format($fondi_extra, 2, ',', '.'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php
                // Display donor list if this is an existing raccolta
                if ($post->ID > 0):
                    // Get all payments for this raccolta
                    $payments = get_posts(array(
                        'post_type' => 'fg_pagamento',
                        'posts_per_page' => -1,
                        'meta_query' => array(
                            array(
                                'key' => '_fg_raccolta_id',
                                'value' => $post->ID,
                                'compare' => '='
                            )
                        )
                    ));
                    
                    if (!empty($payments)):
                ?>
                    <div class="fg-form-row">
                        <div class="fg-form-field">
                            <label><strong><?php _e('Donatori:', 'friends-gestionale'); ?></strong></label>
                            <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 15px; max-height: 300px; overflow-y: auto;">
                                <?php foreach ($payments as $payment):
                                    $socio_id = get_post_meta($payment->ID, '_fg_socio_id', true);
                                    $importo = get_post_meta($payment->ID, '_fg_importo', true);
                                    $data_pagamento = get_post_meta($payment->ID, '_fg_data_pagamento', true);
                                    
                                    if ($socio_id):
                                        $socio = get_post($socio_id);
                                        if ($socio):
                                ?>
                                    <div style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <a href="<?php echo get_edit_post_link($socio_id); ?>" target="_blank" style="color: #0073aa; text-decoration: none; font-weight: 500;">
                                                <?php echo esc_html($socio->post_title); ?>
                                            </a>
                                            <?php 
                                            $tipo_donatore = get_post_meta($socio_id, '_fg_tipo_donatore', true);
                                            if (empty($tipo_donatore)) {
                                                $tipo_donatore = 'anche_socio';
                                            }
                                            if ($tipo_donatore === 'anche_socio'):
                                            ?>
                                                <span class="fg-badge fg-stato-attivo" style="margin-left: 8px; font-size: 10px;">Socio</span>
                                            <?php else: ?>
                                                <span class="fg-badge" style="margin-left: 8px; font-size: 10px;">Donatore</span>
                                            <?php endif; ?>
                                            <?php if ($data_pagamento): ?>
                                                <small style="color: #666; display: block; margin-top: 3px;">
                                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data_pagamento))); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <strong style="color: #0073aa; font-size: 14px;">
                                            €<?php echo number_format(floatval($importo), 2, ',', '.'); ?>
                                        </strong>
                                    </div>
                                <?php
                                        endif;
                                    endif;
                                endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php
                    else:
                ?>
                    <div class="fg-form-row">
                        <div class="fg-form-field">
                            <label><strong><?php _e('Donatori:', 'friends-gestionale'); ?></strong></label>
                            <p style="color: #666; font-style: italic;"><?php _e('Nessun donatore ancora per questa raccolta fondi.', 'friends-gestionale'); ?></p>
                        </div>
                    </div>
                <?php
                    endif;
                endif;
                ?>
            </div>
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
            
            <?php if ($post->ID): ?>
            <div class="fg-form-section">
                <h3 class="fg-section-title"><?php _e('Fondi Raccolti', 'friends-gestionale'); ?></h3>
                <?php
                // Calculate total funds collected for this event
                $payments = get_posts(array(
                    'post_type' => 'fg_pagamento',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_fg_evento_id',
                            'value' => $post->ID,
                            'compare' => '='
                        )
                    )
                ));
                
                $total_raccolto = 0;
                foreach ($payments as $payment) {
                    $importo = get_post_meta($payment->ID, '_fg_importo', true);
                    $total_raccolto += floatval($importo);
                }
                ?>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <div style="background: #f0f6fc; border: 2px solid #0073aa; border-radius: 4px; padding: 20px; text-align: center;">
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                                <?php _e('Totale Raccolto per questo Evento:', 'friends-gestionale'); ?>
                            </p>
                            <p style="margin: 0; font-size: 32px; font-weight: bold; color: #0073aa;">
                                €<?php echo number_format($total_raccolto, 2, ',', '.'); ?>
                            </p>
                            <?php if (count($payments) > 0): ?>
                                <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                                    <?php printf(_n('%d donazione', '%d donazioni', count($payments), 'friends-gestionale'), count($payments)); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (count($payments) > 0): ?>
                <div class="fg-form-row">
                    <div class="fg-form-field">
                        <label><strong><?php _e('Elenco Donazioni per questo Evento:', 'friends-gestionale'); ?></strong></label>
                        <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 15px; max-height: 300px; overflow-y: auto;">
                            <?php foreach ($payments as $payment):
                                $importo = get_post_meta($payment->ID, '_fg_importo', true);
                                $data_pagamento = get_post_meta($payment->ID, '_fg_data_pagamento', true);
                                $socio_id = get_post_meta($payment->ID, '_fg_socio_id', true);
                                $socio_nome = $socio_id ? get_the_title($socio_id) : __('Anonimo', 'friends-gestionale');
                            ?>
                                <div style="padding: 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo esc_html($socio_nome); ?></strong>
                                        <?php if ($data_pagamento): ?>
                                            <small style="color: #666; display: block; margin-top: 3px;">
                                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($data_pagamento))); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <strong style="color: #0073aa; font-size: 14px;">
                                        €<?php echo number_format(floatval($importo), 2, ',', '.'); ?>
                                    </strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
                            <option value=""><?php _e('Seleziona un donatore...', 'friends-gestionale'); ?></option>
                            <?php foreach ($soci as $socio): 
                                $tipo_donatore = get_post_meta($socio->ID, '_fg_tipo_donatore', true);
                                if (empty($tipo_donatore)) {
                                    $tipo_donatore = 'anche_socio';
                                }
                                $tipo_label = ($tipo_donatore === 'anche_socio') ? ' [Socio]' : ' [Donatore]';
                            ?>
                                <option value="<?php echo $socio->ID; ?>" data-tipo="<?php echo $tipo_donatore; ?>"><?php echo esc_html($socio->post_title . $tipo_label); ?></option>
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
                                $tipo_donatore = get_post_meta($socio_id, '_fg_tipo_donatore', true);
                                if (empty($tipo_donatore)) {
                                    $tipo_donatore = 'anche_socio'; // Default
                                }
                            ?>
                                <div class="fg-partecipante-item" data-socio-id="<?php echo $socio_id; ?>">
                                    <span class="fg-partecipante-name">
                                        <?php echo esc_html($socio->post_title); ?>
                                        <?php if ($tipo_donatore === 'anche_socio'): ?>
                                            <span class="fg-badge fg-stato-attivo" style="margin-left: 5px; font-size: 10px;">Socio</span>
                                        <?php else: ?>
                                            <span class="fg-badge" style="margin-left: 5px; font-size: 10px;">Donatore</span>
                                        <?php endif; ?>
                                    </span>
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
            
            // Save donor type
            if (isset($_POST['fg_tipo_donatore'])) {
                update_post_meta($post_id, '_fg_tipo_donatore', sanitize_text_field($_POST['fg_tipo_donatore']));
            }
            
            // Save person type (privato/società)
            if (isset($_POST['fg_tipo_persona'])) {
                update_post_meta($post_id, '_fg_tipo_persona', sanitize_text_field($_POST['fg_tipo_persona']));
            }
            
            // Save ragione sociale
            if (isset($_POST['fg_ragione_sociale'])) {
                update_post_meta($post_id, '_fg_ragione_sociale', sanitize_text_field($_POST['fg_ragione_sociale']));
            }
            
            // Update post title based on person type
            $tipo_persona = isset($_POST['fg_tipo_persona']) ? sanitize_text_field($_POST['fg_tipo_persona']) : 'privato';
            
            if ($tipo_persona === 'societa' && isset($_POST['fg_ragione_sociale']) && !empty($_POST['fg_ragione_sociale'])) {
                // For società, use ragione sociale as title
                $post_title = sanitize_text_field($_POST['fg_ragione_sociale']);
            } else if (isset($_POST['fg_nome']) && isset($_POST['fg_cognome'])) {
                // For privato, use nome + cognome
                $nome = sanitize_text_field($_POST['fg_nome']);
                $cognome = sanitize_text_field($_POST['fg_cognome']);
                $post_title = trim($nome . ' ' . $cognome);
            } else {
                $post_title = null;
            }
            
            if ($post_title) {
                // Update post title
                remove_action('save_post', array($this, 'save_meta_boxes'), 10);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $post_title
                ));
                add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
            }
            
            if (isset($_POST['fg_nome'])) {
                update_post_meta($post_id, '_fg_nome', sanitize_text_field($_POST['fg_nome']));
            }
            if (isset($_POST['fg_cognome'])) {
                update_post_meta($post_id, '_fg_cognome', sanitize_text_field($_POST['fg_cognome']));
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
            
            // Handle taxonomies based on donor type
            $tipo_donatore = isset($_POST['fg_tipo_donatore']) ? sanitize_text_field($_POST['fg_tipo_donatore']) : 'anche_socio';
            
            if ($tipo_donatore === 'anche_socio') {
                // This is a member - handle member category
                if (isset($_POST['fg_categoria_socio_selector']) && !empty($_POST['fg_categoria_socio_selector'])) {
                    $category_id = absint($_POST['fg_categoria_socio_selector']);
                    wp_set_post_terms($post_id, array($category_id), 'fg_categoria_socio', false);
                }
                // Remove donor category if any
                wp_set_post_terms($post_id, array(), 'fg_categoria_donatore', false);
            } else {
                // This is a simple donor - handle donor category
                if (isset($_POST['fg_categoria_donatore_selector']) && !empty($_POST['fg_categoria_donatore_selector'])) {
                    $donor_category_id = absint($_POST['fg_categoria_donatore_selector']);
                    wp_set_post_terms($post_id, array($donor_category_id), 'fg_categoria_donatore', false);
                }
                // Remove member category if any
                wp_set_post_terms($post_id, array(), 'fg_categoria_socio', false);
            }
            
            if (isset($_POST['fg_data_iscrizione'])) {
                $data_iscrizione = sanitize_text_field($_POST['fg_data_iscrizione']);
                // Ensure we only store date part (YYYY-MM-DD), strip any time portion
                if (!empty($data_iscrizione) && strlen($data_iscrizione) > 10) {
                    $data_iscrizione = substr($data_iscrizione, 0, 10);
                }
                update_post_meta($post_id, '_fg_data_iscrizione', $data_iscrizione);
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
            // Save photo as featured image
            if (isset($_POST['fg_foto_id'])) {
                $foto_id = absint($_POST['fg_foto_id']);
                if ($foto_id > 0) {
                    set_post_thumbnail($post_id, $foto_id);
                } else {
                    delete_post_thumbnail($post_id);
                }
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
            if (isset($_POST['fg_evento_id'])) {
                update_post_meta($post_id, '_fg_evento_id', sanitize_text_field($_POST['fg_evento_id']));
            }
            if (isset($_POST['fg_evento_custom'])) {
                update_post_meta($post_id, '_fg_evento_custom', sanitize_text_field($_POST['fg_evento_custom']));
            }
            if (isset($_POST['fg_categoria_socio_id'])) {
                $new_categoria_id = absint($_POST['fg_categoria_socio_id']);
                update_post_meta($post_id, '_fg_categoria_socio_id', $new_categoria_id);
                
                // If categoria socio was modified and we have a valid member, update member's category
                $tipo_pagamento = isset($_POST['fg_tipo_pagamento']) ? sanitize_text_field($_POST['fg_tipo_pagamento']) : get_post_meta($post_id, '_fg_tipo_pagamento', true);
                $socio_id = isset($_POST['fg_socio_id']) ? absint($_POST['fg_socio_id']) : get_post_meta($post_id, '_fg_socio_id', true);
                
                if ($tipo_pagamento === 'quota' && $socio_id && $new_categoria_id) {
                    // Update member's category
                    wp_set_post_terms($socio_id, array($new_categoria_id), 'fg_categoria_socio', false);
                }
            }
            if (isset($_POST['fg_raccolta_id'])) {
                update_post_meta($post_id, '_fg_raccolta_id', absint($_POST['fg_raccolta_id']));
            }
            if (isset($_POST['fg_note'])) {
                update_post_meta($post_id, '_fg_note', sanitize_textarea_field($_POST['fg_note']));
            }
            
            // Update member expiry date if this is a quota payment
            $tipo_pagamento = isset($_POST['fg_tipo_pagamento']) ? sanitize_text_field($_POST['fg_tipo_pagamento']) : get_post_meta($post_id, '_fg_tipo_pagamento', true);
            $socio_id = isset($_POST['fg_socio_id']) ? absint($_POST['fg_socio_id']) : get_post_meta($post_id, '_fg_socio_id', true);
            
            if ($tipo_pagamento === 'quota' && $socio_id) {
                // Get payment date to check if it's from current year
                $data_pagamento = isset($_POST['fg_data_pagamento']) ? sanitize_text_field($_POST['fg_data_pagamento']) : get_post_meta($post_id, '_fg_data_pagamento', true);
                
                // Validate payment date before processing
                $payment_timestamp = strtotime($data_pagamento);
                if ($payment_timestamp !== false) {
                    $current_year = date('Y');
                    $payment_year = date('Y', $payment_timestamp);
                    
                    // Only update expiry date if payment is from current year
                    if ($payment_year == $current_year) {
                        // Get current expiry date
                        $current_expiry = get_post_meta($socio_id, '_fg_data_scadenza', true);
                        
                        if ($current_expiry) {
                            // Add one year to current expiry date
                            $expiry_date = new DateTime($current_expiry);
                            $expiry_date->modify('+1 year');
                            $new_expiry = $expiry_date->format('Y-m-d');
                        } else {
                            // If no expiry date exists, set to one year from today
                            $expiry_date = new DateTime();
                            $expiry_date->modify('+1 year');
                            $new_expiry = $expiry_date->format('Y-m-d');
                        }
                        
                        // Update the member's expiry date
                        update_post_meta($socio_id, '_fg_data_scadenza', $new_expiry);
                        
                        // Also update stato to 'attivo' if it's currently scaduto
                        $current_stato = get_post_meta($socio_id, '_fg_stato', true);
                        if ($current_stato === 'scaduto' || empty($current_stato)) {
                            update_post_meta($socio_id, '_fg_stato', 'attivo');
                        }
                    }
                }
            }
            // Update donor's total donations after saving payment
            if ($socio_id) {
                $this->update_donor_total($socio_id);
            }
            
            // Generate automatic reference title
            $metodo_pagamento = isset($_POST['fg_metodo_pagamento']) ? sanitize_text_field($_POST['fg_metodo_pagamento']) : get_post_meta($post_id, '_fg_metodo_pagamento', true);
            $tipo_pagamento = isset($_POST['fg_tipo_pagamento']) ? sanitize_text_field($_POST['fg_tipo_pagamento']) : get_post_meta($post_id, '_fg_tipo_pagamento', true);
            
            if ($metodo_pagamento && $tipo_pagamento) {
                // Get or create progressive number for this payment
                $progressive_number = get_post_meta($post_id, '_fg_progressive_number', true);
                if (empty($progressive_number)) {
                    // Get the highest progressive number
                    global $wpdb;
                    $max_number = $wpdb->get_var(
                        "SELECT MAX(CAST(meta_value AS UNSIGNED)) 
                        FROM {$wpdb->postmeta} 
                        WHERE meta_key = '_fg_progressive_number'"
                    );
                    $progressive_number = $max_number ? intval($max_number) + 1 : 1;
                    update_post_meta($post_id, '_fg_progressive_number', $progressive_number);
                }
                
                // Format progressive number with leading zeros (4 digits)
                $formatted_number = str_pad($progressive_number, 4, '0', STR_PAD_LEFT);
                
                // Translate payment method and type to Italian
                $metodi_labels = array(
                    'contanti' => 'Contanti',
                    'bonifico' => 'Bonifico',
                    'carta' => 'Carta',
                    'paypal' => 'PayPal',
                    'altro' => 'Altro'
                );
                
                $tipi_labels = array(
                    'quota' => 'Quota',
                    'donazione' => 'Donazione',
                    'raccolta' => 'Raccolta',
                    'evento' => 'Evento',
                    'altro' => 'Altro'
                );
                
                $metodo_label = isset($metodi_labels[$metodo_pagamento]) ? $metodi_labels[$metodo_pagamento] : ucfirst($metodo_pagamento);
                $tipo_label = isset($tipi_labels[$tipo_pagamento]) ? $tipi_labels[$tipo_pagamento] : ucfirst($tipo_pagamento);
                
                // Create reference title: #[4-digit number] - [metodo] - [tipo]
                $reference_title = '#' . $formatted_number . ' - ' . $metodo_label . ' - ' . $tipo_label;
                
                // Update post title
                remove_action('save_post', array($this, 'save_meta_boxes'), 10);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $reference_title
                ));
                add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
            }
            
            // Update raccolta total if this is a raccolta payment
            if (isset($_POST['fg_tipo_pagamento']) && $_POST['fg_tipo_pagamento'] === 'raccolta' && isset($_POST['fg_raccolta_id']) && !empty($_POST['fg_raccolta_id'])) {
                $this->update_raccolta_total(absint($_POST['fg_raccolta_id']));
            }
        }
        
        // Save Raccolta meta
        if ($post->post_type === 'fg_raccolta') {
            if (!isset($_POST['fg_raccolta_meta_box_nonce']) || !wp_verify_nonce($_POST['fg_raccolta_meta_box_nonce'], 'fg_raccolta_meta_box')) {
                return;
            }
            
            // Update post title with titolo_raccolta
            if (isset($_POST['fg_titolo_raccolta'])) {
                $titolo_raccolta = sanitize_text_field($_POST['fg_titolo_raccolta']);
                
                // Update post title
                remove_action('save_post', array($this, 'save_meta_boxes'), 10);
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $titolo_raccolta
                ));
                add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
                
                update_post_meta($post_id, '_fg_titolo_raccolta', $titolo_raccolta);
            }
            
            if (isset($_POST['fg_obiettivo'])) {
                update_post_meta($post_id, '_fg_obiettivo', floatval($_POST['fg_obiettivo']));
            }
            if (isset($_POST['fg_raccolto'])) {
                update_post_meta($post_id, '_fg_raccolto', floatval($_POST['fg_raccolto']));
            }
            if (isset($_POST['fg_fondi_extra'])) {
                update_post_meta($post_id, '_fg_fondi_extra', floatval($_POST['fg_fondi_extra']));
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
    
    /**
     * Update total amount raised for a fundraising campaign
     */
    /**
     * Before delete payment - update raccolta total if needed
     */
    public function before_delete_payment($post_id) {
        $post = get_post($post_id);
        if ($post && $post->post_type === 'fg_pagamento') {
            $tipo_pagamento = get_post_meta($post_id, '_fg_tipo_pagamento', true);
            $raccolta_id = get_post_meta($post_id, '_fg_raccolta_id', true);
            $socio_id = get_post_meta($post_id, '_fg_socio_id', true);
            
            if ($tipo_pagamento === 'raccolta' && $raccolta_id) {
                // Update total after this payment is deleted
                // We need to do this on shutdown to ensure the post is actually deleted
                add_action('shutdown', function() use ($raccolta_id) {
                    $this->update_raccolta_total($raccolta_id);
                });
            }
            
            // Update donor's total donations after payment is deleted
            if ($socio_id) {
                add_action('shutdown', function() use ($socio_id) {
                    $this->update_donor_total($socio_id);
                });
            }
        }
    }
    
    private function update_raccolta_total($raccolta_id) {
        // Get all payments for this raccolta
        $payments = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_raccolta_id',
                    'value' => $raccolta_id,
                    'compare' => '='
                )
            )
        ));
        
        $total = 0;
        foreach ($payments as $payment) {
            $importo = get_post_meta($payment->ID, '_fg_importo', true);
            $total += floatval($importo);
        }
        
        // Update the raccolta total
        update_post_meta($raccolta_id, '_fg_raccolto', $total);
    }
    
    private function update_donor_total($socio_id) {
        // Get all payments for this donor
        $payments = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_socio_id',
                    'value' => $socio_id,
                    'compare' => '='
                )
            )
        ));
        
        $total = 0;
        foreach ($payments as $payment) {
            $importo = get_post_meta($payment->ID, '_fg_importo', true);
            $total += floatval($importo);
        }
        
        // Update the donor's total donations for sortable column
        update_post_meta($socio_id, '_fg_totale_donato', $total);
    }
    
    /**
     * AJAX handler to get payment form for modal
     */
    public function ajax_get_payment_form() {
        check_ajax_referer('fg_ajax_nonce', 'nonce');
        
        $donor_id = isset($_POST['donor_id']) ? absint($_POST['donor_id']) : 0;
        
        if (!$donor_id) {
            wp_send_json_error('Invalid donor ID');
        }
        
        $donor = get_post($donor_id);
        if (!$donor) {
            wp_send_json_error('Donor not found');
        }
        
        // Get all events for dropdown
        $eventi = get_posts(array(
            'post_type' => 'fg_evento',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Get all fundraising campaigns for dropdown
        $raccolte = get_posts(array(
            'post_type' => 'fg_raccolta',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        // Get all member categories
        $categorie = get_terms(array(
            'taxonomy' => 'fg_categoria_socio',
            'hide_empty' => false
        ));
        
        ob_start();
        ?>
        <form id="fg-modal-payment-form" style="max-width: 100%;">
            <input type="hidden" name="donor_id" value="<?php echo esc_attr($donor_id); ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                    <strong><?php _e('Donatore:', 'friends-gestionale'); ?></strong>
                </label>
                <p style="padding: 10px; background: #f0f0f1; border-radius: 4px; margin: 0;">
                    <?php echo esc_html($donor->post_title); ?>
                </p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                        <strong><?php _e('Importo (€):', 'friends-gestionale'); ?></strong> <span style="color: #d63638;">*</span>
                    </label>
                    <input type="number" name="importo" id="fg_modal_importo" step="0.01" min="0" required 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                        <strong><?php _e('Data Pagamento:', 'friends-gestionale'); ?></strong> <span style="color: #d63638;">*</span>
                    </label>
                    <input type="date" name="data_pagamento" id="fg_modal_data_pagamento" required 
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;" />
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                        <strong><?php _e('Metodo di Pagamento:', 'friends-gestionale'); ?></strong>
                    </label>
                    <select name="metodo_pagamento" id="fg_modal_metodo_pagamento" required
                            style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value=""><?php _e('Seleziona metodo di pagamento', 'friends-gestionale'); ?></option>
                        <option value="contanti"><?php _e('Contanti', 'friends-gestionale'); ?></option>
                        <option value="bonifico"><?php _e('Bonifico Bancario', 'friends-gestionale'); ?></option>
                        <option value="carta"><?php _e('Carta di Credito', 'friends-gestionale'); ?></option>
                        <option value="paypal"><?php _e('PayPal', 'friends-gestionale'); ?></option>
                        <option value="altro"><?php _e('Altro', 'friends-gestionale'); ?></option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                        <strong><?php _e('Tipo di Pagamento:', 'friends-gestionale'); ?></strong>
                    </label>
                    <select name="tipo_pagamento" id="fg_modal_tipo_pagamento" required
                            style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value=""><?php _e('Seleziona tipo di pagamento', 'friends-gestionale'); ?></option>
                        <option value="quota"><?php _e('Quota Associativa', 'friends-gestionale'); ?></option>
                        <option value="donazione"><?php _e('Donazione singola', 'friends-gestionale'); ?></option>
                        <option value="raccolta"><?php _e('Raccolta Fondi', 'friends-gestionale'); ?></option>
                        <option value="evento"><?php _e('Evento', 'friends-gestionale'); ?></option>
                        <option value="altro"><?php _e('Altro', 'friends-gestionale'); ?></option>
                    </select>
                </div>
            </div>
            
            <div id="fg_modal_evento_field" class="fg-modal-conditional-field" style="display: none; margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                    <strong><?php _e('Seleziona Evento:', 'friends-gestionale'); ?></strong>
                </label>
                <select name="evento_id" id="fg_modal_evento_id" 
                        style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value=""><?php _e('Seleziona Evento', 'friends-gestionale'); ?></option>
                    <?php foreach ($eventi as $evento): ?>
                        <option value="<?php echo $evento->ID; ?>"><?php echo esc_html($evento->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="fg_modal_raccolta_field" class="fg-modal-conditional-field" style="display: none; margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                    <strong><?php _e('Seleziona Raccolta Fondi:', 'friends-gestionale'); ?></strong>
                </label>
                <select name="raccolta_id" id="fg_modal_raccolta_id" 
                        style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value=""><?php _e('Seleziona Raccolta Fondi', 'friends-gestionale'); ?></option>
                    <?php foreach ($raccolte as $raccolta): ?>
                        <option value="<?php echo $raccolta->ID; ?>"><?php echo esc_html($raccolta->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="fg_modal_categoria_socio_field" class="fg-modal-conditional-field" style="display: none; margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                    <strong><?php _e('Categoria Socio:', 'friends-gestionale'); ?></strong>
                    <button type="button" id="fg_modal_unlock_categoria_socio" class="button button-small" style="margin-left: 10px;">
                        <span class="dashicons dashicons-lock" style="margin-top: 3px;"></span>
                        <?php _e('Sblocca', 'friends-gestionale'); ?>
                    </button>
                </label>
                <select name="categoria_socio_id" id="fg_modal_categoria_socio_id" disabled
                        style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; background-color: #f0f0f0;">
                    <option value=""><?php _e('Seleziona Categoria', 'friends-gestionale'); ?></option>
                    <?php if (!empty($categorie) && !is_wp_error($categorie)): ?>
                        <?php foreach ($categorie as $categoria): ?>
                            <option value="<?php echo $categoria->term_id; ?>"><?php echo esc_html($categoria->name); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <p class="description" id="fg_modal_categoria_socio_description" style="color: #666; margin-top: 8px;"><?php _e('Clicca su "Sblocca" per modificare la categoria. La modifica aggiornerà anche la tipologia socio del donatore.', 'friends-gestionale'); ?></p>
            </div>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px;">
                    <strong><?php _e('Note:', 'friends-gestionale'); ?></strong>
                </label>
                <textarea name="note" id="fg_modal_note" rows="3" 
                          style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            
            <div style="text-align: right; padding-top: 15px; border-top: 1px solid #ddd;">
                <button type="button" id="fg-close-payment-modal" class="button" style="margin-right: 10px;">
                    <?php _e('Annulla', 'friends-gestionale'); ?>
                </button>
                <button type="submit" class="button button-primary">
                    <?php _e('Salva Pagamento', 'friends-gestionale'); ?>
                </button>
            </div>
        </form>
        <?php
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * AJAX handler to save payment from modal
     */
    public function ajax_save_payment() {
        check_ajax_referer('fg_ajax_nonce', 'nonce');
        
        $donor_id = isset($_POST['donor_id']) ? absint($_POST['donor_id']) : 0;
        $importo = isset($_POST['importo']) ? floatval($_POST['importo']) : 0;
        $data_pagamento = isset($_POST['data_pagamento']) ? sanitize_text_field($_POST['data_pagamento']) : '';
        $metodo_pagamento = isset($_POST['metodo_pagamento']) ? sanitize_text_field($_POST['metodo_pagamento']) : 'contanti';
        $tipo_pagamento = isset($_POST['tipo_pagamento']) ? sanitize_text_field($_POST['tipo_pagamento']) : 'donazione';
        $evento_id = isset($_POST['evento_id']) ? absint($_POST['evento_id']) : 0;
        $raccolta_id = isset($_POST['raccolta_id']) ? absint($_POST['raccolta_id']) : 0;
        $categoria_socio_id = isset($_POST['categoria_socio_id']) ? absint($_POST['categoria_socio_id']) : 0;
        $note = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';
        
        if (!$donor_id || !$importo || !$data_pagamento) {
            wp_send_json_error('Dati mancanti');
        }
        
        // Create payment post
        $payment_id = wp_insert_post(array(
            'post_type' => 'fg_pagamento',
            'post_status' => 'publish',
            'post_title' => 'Pagamento ' . date('Y-m-d H:i:s')
        ));
        
        if (is_wp_error($payment_id)) {
            wp_send_json_error('Errore nella creazione del pagamento');
        }
        
        // Save payment metadata
        update_post_meta($payment_id, '_fg_socio_id', $donor_id);
        update_post_meta($payment_id, '_fg_importo', $importo);
        update_post_meta($payment_id, '_fg_data_pagamento', $data_pagamento);
        update_post_meta($payment_id, '_fg_metodo_pagamento', $metodo_pagamento);
        update_post_meta($payment_id, '_fg_tipo_pagamento', $tipo_pagamento);
        
        if ($evento_id) {
            update_post_meta($payment_id, '_fg_evento_id', $evento_id);
        }
        if ($raccolta_id) {
            update_post_meta($payment_id, '_fg_raccolta_id', $raccolta_id);
        }
        if ($categoria_socio_id) {
            update_post_meta($payment_id, '_fg_categoria_socio_id', $categoria_socio_id);
        }
        if ($note) {
            update_post_meta($payment_id, '_fg_note', $note);
        }
        
        // Update member expiry date if this is a quota payment and current year
        if ($tipo_pagamento === 'quota' && $donor_id) {
            $payment_timestamp = strtotime($data_pagamento);
            if ($payment_timestamp !== false) {
                $current_year = date('Y');
                $payment_year = date('Y', $payment_timestamp);
                
                if ($payment_year == $current_year) {
                    $current_expiry = get_post_meta($donor_id, '_fg_data_scadenza', true);
                    
                    if ($current_expiry) {
                        $expiry_date = new DateTime($current_expiry);
                        $expiry_date->modify('+1 year');
                        $new_expiry = $expiry_date->format('Y-m-d');
                    } else {
                        $expiry_date = new DateTime();
                        $expiry_date->modify('+1 year');
                        $new_expiry = $expiry_date->format('Y-m-d');
                    }
                    
                    update_post_meta($donor_id, '_fg_data_scadenza', $new_expiry);
                    
                    $current_stato = get_post_meta($donor_id, '_fg_stato', true);
                    if ($current_stato === 'scaduto' || empty($current_stato)) {
                        update_post_meta($donor_id, '_fg_stato', 'attivo');
                    }
                }
            }
        }
        
        // If categoria socio was modified and we have a valid member, update member's category
        if ($tipo_pagamento === 'quota' && $donor_id && $categoria_socio_id) {
            wp_set_post_terms($donor_id, array($categoria_socio_id), 'fg_categoria_socio', false);
        }
        
        // Update donor's total donations
        $this->update_donor_total($donor_id);
        
        // Generate automatic reference title
        $metodi_labels = array(
            'contanti' => 'Contanti',
            'bonifico' => 'Bonifico',
            'carta' => 'Carta',
            'paypal' => 'PayPal',
            'altro' => 'Altro'
        );
        
        $tipi_labels = array(
            'quota' => 'Quota',
            'donazione' => 'Donazione',
            'raccolta' => 'Raccolta',
            'evento' => 'Evento',
            'altro' => 'Altro'
        );
        
        // Get or create progressive number
        global $wpdb;
        $max_number = $wpdb->get_var(
            "SELECT MAX(CAST(meta_value AS UNSIGNED)) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_fg_progressive_number'"
        );
        $progressive_number = $max_number ? intval($max_number) + 1 : 1;
        update_post_meta($payment_id, '_fg_progressive_number', $progressive_number);
        
        $formatted_number = str_pad($progressive_number, 4, '0', STR_PAD_LEFT);
        $metodo_label = isset($metodi_labels[$metodo_pagamento]) ? $metodi_labels[$metodo_pagamento] : $metodo_pagamento;
        $tipo_label = isset($tipi_labels[$tipo_pagamento]) ? $tipi_labels[$tipo_pagamento] : $tipo_pagamento;
        
        $title = sprintf('#%s - %s - %s', $formatted_number, $metodo_label, $tipo_label);
        
        wp_update_post(array(
            'ID' => $payment_id,
            'post_title' => $title
        ));
        
        wp_send_json_success(array(
            'payment_id' => $payment_id,
            'message' => 'Pagamento salvato con successo'
        ));
    }
    
    /**
     * AJAX handler to get member's quota and category
     */
    public function ajax_get_member_quota() {
        $socio_id = isset($_POST['socio_id']) ? absint($_POST['socio_id']) : 0;
        
        if (!$socio_id) {
            wp_send_json_error('Invalid member ID');
        }
        
        // Get member's category
        $categories = wp_get_post_terms($socio_id, 'fg_categoria_socio', array('fields' => 'ids'));
        $categoria_id = !empty($categories) && !is_wp_error($categories) ? $categories[0] : 0;
        
        $quota = 0;
        if ($categoria_id) {
            $quota = get_term_meta($categoria_id, 'fg_quota_associativa', true);
        }
        
        wp_send_json_success(array(
            'categoria_id' => $categoria_id,
            'quota' => $quota ? floatval($quota) : 0
        ));
    }
}

// Initialize
new Friends_Gestionale_Meta_Boxes();
