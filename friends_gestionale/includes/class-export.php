<?php
/**
 * Export Functionality
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Export {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_export_page'));
        add_action('admin_init', array($this, 'handle_export'));
    }
    
    /**
     * Add export page
     * Note: Submenu removed to avoid duplicate. Access via dashboard button.
     */
    public function add_export_page() {
        // Export page accessed via dashboard button, not submenu
        // This prevents duplicate menu entries
    }
    
    /**
     * Render export page
     */
    public function render_export_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Esporta Dati', 'friends-gestionale'); ?></h1>
            
            <div class="fg-export-section">
                <h2><?php _e('Esporta Soci', 'friends-gestionale'); ?></h2>
                <p><?php _e('Esporta l\'elenco completo dei soci in formato CSV.', 'friends-gestionale'); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field('fg_export_soci', 'fg_export_nonce'); ?>
                    <input type="hidden" name="fg_export_type" value="soci" />
                    
                    <p>
                        <label>
                            <input type="checkbox" name="fg_export_stato[]" value="attivo" checked />
                            <?php _e('Soci Attivi', 'friends-gestionale'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="fg_export_stato[]" value="sospeso" />
                            <?php _e('Soci Sospesi', 'friends-gestionale'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="fg_export_stato[]" value="scaduto" />
                            <?php _e('Soci Scaduti', 'friends-gestionale'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="fg_export_stato[]" value="inattivo" />
                            <?php _e('Soci Inattivi', 'friends-gestionale'); ?>
                        </label>
                    </p>
                    
                    <?php submit_button(__('Esporta Soci CSV', 'friends-gestionale'), 'primary', 'submit', false); ?>
                </form>
            </div>
            
            <hr>
            
            <div class="fg-export-section">
                <h2><?php _e('Esporta Pagamenti', 'friends-gestionale'); ?></h2>
                <p><?php _e('Esporta l\'elenco dei pagamenti registrati in formato CSV.', 'friends-gestionale'); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field('fg_export_pagamenti', 'fg_export_nonce'); ?>
                    <input type="hidden" name="fg_export_type" value="pagamenti" />
                    
                    <p>
                        <label for="fg_data_inizio"><?php _e('Data Inizio:', 'friends-gestionale'); ?></label>
                        <input type="date" name="fg_data_inizio" id="fg_data_inizio" />
                    </p>
                    
                    <p>
                        <label for="fg_data_fine"><?php _e('Data Fine:', 'friends-gestionale'); ?></label>
                        <input type="date" name="fg_data_fine" id="fg_data_fine" />
                    </p>
                    
                    <?php submit_button(__('Esporta Pagamenti CSV', 'friends-gestionale'), 'primary', 'submit', false); ?>
                </form>
            </div>
            
            <hr>
            
            <div class="fg-export-section">
                <h2><?php _e('Esporta Raccolte Fondi', 'friends-gestionale'); ?></h2>
                <p><?php _e('Esporta l\'elenco delle raccolte fondi in formato CSV.', 'friends-gestionale'); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field('fg_export_raccolte', 'fg_export_nonce'); ?>
                    <input type="hidden" name="fg_export_type" value="raccolte" />
                    
                    <p>
                        <label>
                            <input type="checkbox" name="fg_export_stato_raccolta[]" value="attiva" checked />
                            <?php _e('Raccolte Attive', 'friends-gestionale'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="fg_export_stato_raccolta[]" value="completata" />
                            <?php _e('Raccolte Completate', 'friends-gestionale'); ?>
                        </label><br>
                        <label>
                            <input type="checkbox" name="fg_export_stato_raccolta[]" value="sospesa" />
                            <?php _e('Raccolte Sospese', 'friends-gestionale'); ?>
                        </label>
                    </p>
                    
                    <?php submit_button(__('Esporta Raccolte CSV', 'friends-gestionale'), 'primary', 'submit', false); ?>
                </form>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle export
     */
    public function handle_export() {
        if (!isset($_POST['fg_export_type'])) {
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Non hai i permessi per esportare i dati.', 'friends-gestionale'));
        }
        
        $export_type = sanitize_text_field($_POST['fg_export_type']);
        
        // Verify nonce
        $nonce_action = 'fg_export_' . $export_type;
        if (!isset($_POST['fg_export_nonce']) || !wp_verify_nonce($_POST['fg_export_nonce'], $nonce_action)) {
            wp_die(__('Verifica di sicurezza fallita.', 'friends-gestionale'));
        }
        
        switch ($export_type) {
            case 'soci':
                $this->export_soci();
                break;
            case 'pagamenti':
                $this->export_pagamenti();
                break;
            case 'raccolte':
                $this->export_raccolte();
                break;
        }
    }
    
    /**
     * Export members to CSV
     */
    private function export_soci() {
        $stati = isset($_POST['fg_export_stato']) ? $_POST['fg_export_stato'] : array('attivo');
        
        $args = array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        if (!empty($stati)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_fg_stato',
                    'value' => $stati,
                    'compare' => 'IN'
                )
            );
        }
        
        $query = new WP_Query($args);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=soci_export_' . date('Y-m-d') . '.csv');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, array(
            'ID',
            'Nome',
            'Codice Fiscale',
            'Email',
            'Telefono',
            'Indirizzo',
            'Data Iscrizione',
            'Data Scadenza',
            'Quota Annuale',
            'Stato',
            'Note'
        ));
        
        // CSV Data
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                fputcsv($output, array(
                    $post_id,
                    get_the_title(),
                    get_post_meta($post_id, '_fg_codice_fiscale', true),
                    get_post_meta($post_id, '_fg_email', true),
                    get_post_meta($post_id, '_fg_telefono', true),
                    str_replace(array("\r", "\n"), ' ', get_post_meta($post_id, '_fg_indirizzo', true)),
                    get_post_meta($post_id, '_fg_data_iscrizione', true),
                    get_post_meta($post_id, '_fg_data_scadenza', true),
                    get_post_meta($post_id, '_fg_quota_annuale', true),
                    get_post_meta($post_id, '_fg_stato', true),
                    str_replace(array("\r", "\n"), ' ', get_post_meta($post_id, '_fg_note', true))
                ));
            }
        }
        
        wp_reset_postdata();
        fclose($output);
        exit;
    }
    
    /**
     * Export payments to CSV
     */
    private function export_pagamenti() {
        $data_inizio = isset($_POST['fg_data_inizio']) ? sanitize_text_field($_POST['fg_data_inizio']) : '';
        $data_fine = isset($_POST['fg_data_fine']) ? sanitize_text_field($_POST['fg_data_fine']) : '';
        
        $args = array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        // Add date range filter
        if (!empty($data_inizio) || !empty($data_fine)) {
            $meta_query = array();
            
            if (!empty($data_inizio)) {
                $meta_query[] = array(
                    'key' => '_fg_data_pagamento',
                    'value' => $data_inizio,
                    'compare' => '>=',
                    'type' => 'DATE'
                );
            }
            
            if (!empty($data_fine)) {
                $meta_query[] = array(
                    'key' => '_fg_data_pagamento',
                    'value' => $data_fine,
                    'compare' => '<=',
                    'type' => 'DATE'
                );
            }
            
            if (count($meta_query) > 1) {
                $meta_query['relation'] = 'AND';
            }
            
            $args['meta_query'] = $meta_query;
        }
        
        $query = new WP_Query($args);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=pagamenti_export_' . date('Y-m-d') . '.csv');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, array(
            'ID',
            'Data Pagamento',
            'Socio ID',
            'Nome Socio',
            'Importo',
            'Metodo Pagamento',
            'Tipo Pagamento',
            'Note'
        ));
        
        // CSV Data
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $socio_id = get_post_meta($post_id, '_fg_socio_id', true);
                $socio_nome = $socio_id ? get_the_title($socio_id) : '';
                
                fputcsv($output, array(
                    $post_id,
                    get_post_meta($post_id, '_fg_data_pagamento', true),
                    $socio_id,
                    $socio_nome,
                    get_post_meta($post_id, '_fg_importo', true),
                    get_post_meta($post_id, '_fg_metodo_pagamento', true),
                    get_post_meta($post_id, '_fg_tipo_pagamento', true),
                    str_replace(array("\r", "\n"), ' ', get_post_meta($post_id, '_fg_note', true))
                ));
            }
        }
        
        wp_reset_postdata();
        fclose($output);
        exit;
    }
    
    /**
     * Export fundraising campaigns to CSV
     */
    private function export_raccolte() {
        $stati = isset($_POST['fg_export_stato_raccolta']) ? $_POST['fg_export_stato_raccolta'] : array('attiva');
        
        $args = array(
            'post_type' => 'fg_raccolta',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        if (!empty($stati)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_fg_stato',
                    'value' => $stati,
                    'compare' => 'IN'
                )
            );
        }
        
        $query = new WP_Query($args);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=raccolte_export_' . date('Y-m-d') . '.csv');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        fputcsv($output, array(
            'ID',
            'Titolo',
            'Obiettivo',
            'Raccolto',
            'Percentuale',
            'Data Inizio',
            'Data Fine',
            'Stato',
            'Descrizione'
        ));
        
        // CSV Data
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $obiettivo = floatval(get_post_meta($post_id, '_fg_obiettivo', true));
                $raccolto = floatval(get_post_meta($post_id, '_fg_raccolto', true));
                $percentuale = $obiettivo > 0 ? ($raccolto / $obiettivo) * 100 : 0;
                
                fputcsv($output, array(
                    $post_id,
                    get_the_title(),
                    $obiettivo,
                    $raccolto,
                    number_format($percentuale, 2),
                    get_post_meta($post_id, '_fg_data_inizio', true),
                    get_post_meta($post_id, '_fg_data_fine', true),
                    get_post_meta($post_id, '_fg_stato', true),
                    str_replace(array("\r", "\n"), ' ', wp_strip_all_tags(get_the_content()))
                ));
            }
        }
        
        wp_reset_postdata();
        fclose($output);
        exit;
    }
}

// Initialize
new Friends_Gestionale_Export();
