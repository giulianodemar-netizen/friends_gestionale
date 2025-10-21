<?php
/**
 * Import Functionality for Donors
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Import {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_import_page'));
        add_action('admin_init', array($this, 'handle_ajax_requests'));
        add_action('wp_ajax_fg_upload_import_file', array($this, 'ajax_upload_file'));
        add_action('wp_ajax_fg_preview_import', array($this, 'ajax_preview_import'));
        add_action('wp_ajax_fg_execute_import', array($this, 'ajax_execute_import'));
        add_action('wp_ajax_fg_save_mapping_template', array($this, 'ajax_save_mapping_template'));
        add_action('wp_ajax_fg_load_mapping_template', array($this, 'ajax_load_mapping_template'));
    }
    
    /**
     * Add import page to admin menu
     */
    public function add_import_page() {
        add_submenu_page(
            'friends-gestionale',
            __('Importa da file', 'friends-gestionale'),
            __('Importa da file', 'friends-gestionale'),
            'edit_posts',
            'fg-import',
            array($this, 'render_import_page')
        );
    }
    
    /**
     * Register AJAX handlers
     */
    public function handle_ajax_requests() {
        // AJAX handlers are registered in constructor
    }
    
    /**
     * Render import page
     */
    public function render_import_page() {
        ?>
        <div class="wrap fg-import-wrap">
            <h1><?php _e('Importa Donatori da File CSV/XLSX', 'friends-gestionale'); ?></h1>
            
            <div id="fg-import-container">
                <!-- Step 1: Upload File -->
                <div id="fg-import-step-upload" class="fg-import-step active">
                    <h2><?php _e('Step 1: Carica il file', 'friends-gestionale'); ?></h2>
                    <div class="fg-upload-area">
                        <div id="fg-drop-zone" class="fg-drop-zone">
                            <div class="fg-drop-zone-content">
                                <span class="dashicons dashicons-upload" style="font-size: 48px; color: #0073aa;"></span>
                                <p><?php _e('Trascina qui il file CSV o XLSX', 'friends-gestionale'); ?></p>
                                <p><?php _e('oppure', 'friends-gestionale'); ?></p>
                                <button type="button" class="button button-primary" id="fg-select-file-btn">
                                    <?php _e('Scegli file', 'friends-gestionale'); ?>
                                </button>
                                <input type="file" id="fg-file-input" accept=".csv,.xlsx,.xls" style="display: none;" />
                            </div>
                        </div>
                        <div id="fg-upload-progress" style="display: none;">
                            <p><?php _e('Caricamento in corso...', 'friends-gestionale'); ?></p>
                            <div class="fg-progress-bar">
                                <div class="fg-progress-fill"></div>
                            </div>
                        </div>
                        <div id="fg-upload-error" class="notice notice-error" style="display: none;">
                            <p></p>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2: Column Mapping -->
                <div id="fg-import-step-mapping" class="fg-import-step" style="display: none;">
                    <h2><?php _e('Step 2: Mapping Colonne', 'friends-gestionale'); ?></h2>
                    <p class="description">
                        <?php _e('Associa le colonne del file ai campi del donatore. I campi con * sono raccomandati.', 'friends-gestionale'); ?>
                    </p>
                    
                    <!-- Mapping templates -->
                    <div class="fg-template-section">
                        <label for="fg-mapping-template">
                            <strong><?php _e('Template di mapping:', 'friends-gestionale'); ?></strong>
                        </label>
                        <select id="fg-mapping-template" class="widefat" style="max-width: 300px;">
                            <option value=""><?php _e('-- Seleziona template salvato --', 'friends-gestionale'); ?></option>
                            <?php
                            $templates = get_option('fg_import_mapping_templates', array());
                            foreach ($templates as $key => $template) {
                                echo '<option value="' . esc_attr($key) . '">' . esc_html($template['name']) . '</option>';
                            }
                            ?>
                        </select>
                        <button type="button" class="button" id="fg-save-template-btn">
                            <?php _e('Salva mapping corrente', 'friends-gestionale'); ?>
                        </button>
                    </div>
                    
                    <div id="fg-mapping-container" class="fg-mapping-container">
                        <!-- Mapping fields will be generated dynamically -->
                    </div>
                    
                    <!-- Import options -->
                    <div class="fg-import-options">
                        <h3><?php _e('Opzioni Import', 'friends-gestionale'); ?></h3>
                        <label>
                            <input type="checkbox" id="fg-update-existing" value="1" checked />
                            <?php _e('Aggiorna i record esistenti con la stessa email', 'friends-gestionale'); ?>
                        </label>
                        <p class="description">
                            <?php _e("L'email è usata come campo univoco. Se deselezionato, verrà creato un duplicato invece di aggiornare il record esistente.", 'friends-gestionale'); ?>
                        </p>
                    </div>
                    
                    <div class="fg-step-actions">
                        <button type="button" class="button" id="fg-back-to-upload-btn">
                            <?php _e('← Torna indietro', 'friends-gestionale'); ?>
                        </button>
                        <button type="button" class="button button-primary" id="fg-preview-import-btn">
                            <?php _e('Anteprima Import', 'friends-gestionale'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Preview -->
                <div id="fg-import-step-preview" class="fg-import-step" style="display: none;">
                    <h2><?php _e('Step 3: Anteprima Import', 'friends-gestionale'); ?></h2>
                    <div id="fg-preview-summary" class="fg-preview-summary">
                        <!-- Summary will be generated dynamically -->
                    </div>
                    
                    <div id="fg-preview-table-container">
                        <!-- Preview table will be generated dynamically -->
                    </div>
                    
                    <div class="fg-step-actions">
                        <button type="button" class="button" id="fg-back-to-mapping-btn">
                            <?php _e('← Modifica Mapping', 'friends-gestionale'); ?>
                        </button>
                        <button type="button" class="button button-primary button-large" id="fg-execute-import-btn">
                            <?php _e('Esegui Import', 'friends-gestionale'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Step 4: Results -->
                <div id="fg-import-step-results" class="fg-import-step" style="display: none;">
                    <h2><?php _e('Import Completato', 'friends-gestionale'); ?></h2>
                    <div id="fg-import-results" class="fg-import-results">
                        <!-- Results will be generated dynamically -->
                    </div>
                    
                    <div class="fg-step-actions">
                        <a href="<?php echo admin_url('edit.php?post_type=fg_socio'); ?>" class="button button-primary">
                            <?php _e('Vai ai Donatori', 'friends-gestionale'); ?>
                        </a>
                        <button type="button" class="button" id="fg-new-import-btn">
                            <?php _e('Nuovo Import', 'friends-gestionale'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .fg-import-wrap {
                max-width: 1200px;
            }
            .fg-import-step {
                background: #fff;
                padding: 20px;
                margin: 20px 0;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .fg-drop-zone {
                border: 3px dashed #0073aa;
                border-radius: 8px;
                padding: 40px;
                text-align: center;
                background: #f9f9f9;
                transition: all 0.3s;
            }
            .fg-drop-zone.drag-over {
                background: #e3f2fd;
                border-color: #1976d2;
            }
            .fg-drop-zone-content p {
                margin: 10px 0;
                font-size: 16px;
            }
            .fg-progress-bar {
                width: 100%;
                height: 30px;
                background: #f0f0f0;
                border-radius: 4px;
                overflow: hidden;
                margin-top: 10px;
            }
            .fg-progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #0073aa, #00a0d2);
                width: 0%;
                transition: width 0.3s;
            }
            .fg-mapping-container {
                margin: 20px 0;
            }
            .fg-mapping-row {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 15px;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 4px;
            }
            .fg-mapping-label {
                flex: 0 0 200px;
                font-weight: 600;
            }
            .fg-tooltip {
                display: inline-block;
                cursor: help;
                color: #0073aa;
                font-weight: bold;
                font-size: 14px;
                margin-left: 5px;
            }
            .fg-tooltip:hover::after {
                content: attr(title);
                position: absolute;
                background: #333;
                color: #fff;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: normal;
                white-space: normal;
                max-width: 300px;
                z-index: 1000;
                margin-left: 10px;
                margin-top: -30px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .fg-mapping-select {
                flex: 1;
                max-width: 300px;
            }
            .fg-mapping-static {
                flex: 1;
                max-width: 300px;
                display: none;
            }
            .fg-import-options {
                margin: 20px 0;
                padding: 15px;
                background: #fffbcc;
                border-left: 4px solid #ffb900;
            }
            .fg-step-actions {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
            }
            .fg-template-section {
                margin: 20px 0;
                padding: 15px;
                background: #f0f0f0;
                border-radius: 4px;
            }
            .fg-preview-summary {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
                margin: 20px 0;
            }
            .fg-preview-stat {
                padding: 15px;
                background: #fff;
                border-left: 4px solid #0073aa;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .fg-preview-stat.success {
                border-left-color: #46b450;
            }
            .fg-preview-stat.warning {
                border-left-color: #ffb900;
            }
            .fg-preview-stat.error {
                border-left-color: #dc3232;
            }
            .fg-preview-stat-value {
                font-size: 32px;
                font-weight: bold;
                margin: 5px 0;
            }
            .fg-preview-stat-label {
                color: #666;
                font-size: 14px;
            }
            #fg-preview-table-container {
                overflow-x: auto;
                margin: 20px 0;
            }
            .fg-preview-table {
                width: 100%;
                border-collapse: collapse;
            }
            .fg-preview-table th,
            .fg-preview-table td {
                padding: 10px;
                border: 1px solid #ddd;
                text-align: left;
            }
            .fg-preview-table th {
                background: #f0f0f0;
                font-weight: 600;
            }
            .fg-preview-table tr.row-error {
                background: #ffeaea;
            }
            .fg-preview-table tr.row-warning {
                background: #fff8e1;
            }
            .fg-preview-table tr.row-success {
                background: #e8f5e9;
            }
            .fg-row-status {
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 12px;
                font-weight: 600;
            }
            .fg-row-status.create {
                background: #46b450;
                color: #fff;
            }
            .fg-row-status.update {
                background: #00a0d2;
                color: #fff;
            }
            .fg-row-status.skip {
                background: #ffb900;
                color: #000;
            }
            .fg-row-status.error {
                background: #dc3232;
                color: #fff;
            }
            .fg-import-results {
                margin: 20px 0;
            }
            .fg-result-box {
                padding: 20px;
                margin: 10px 0;
                border-radius: 4px;
            }
            .fg-result-box.success {
                background: #e8f5e9;
                border: 1px solid #46b450;
            }
            .fg-result-box h3 {
                margin-top: 0;
            }
        </style>
        <?php
    }
    
    /**
     * AJAX: Upload file
     */
    public function ajax_upload_file() {
        check_ajax_referer('fg-import-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permessi insufficienti', 'friends-gestionale')));
        }
        
        if (!isset($_FILES['file'])) {
            wp_send_json_error(array('message' => __('Nessun file caricato', 'friends-gestionale')));
        }
        
        $file = $_FILES['file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, array('csv', 'xlsx', 'xls'))) {
            wp_send_json_error(array('message' => __('Formato file non supportato. Usa CSV o XLSX.', 'friends-gestionale')));
        }
        
        // Move uploaded file to temp location
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/fg-import-temp';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        $temp_file = $temp_dir . '/' . uniqid('import_') . '.' . $file_ext;
        if (!move_uploaded_file($file['tmp_name'], $temp_file)) {
            wp_send_json_error(array('message' => __('Errore nel caricamento del file', 'friends-gestionale')));
        }
        
        // Parse file and get headers + preview
        $result = $this->parse_file($temp_file, $file_ext);
        
        if (is_wp_error($result)) {
            unlink($temp_file);
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        // Store file path in transient for later use
        $import_id = uniqid('import_');
        set_transient('fg_import_' . $import_id, array(
            'file_path' => $temp_file,
            'file_type' => $file_ext,
            'file_name' => $file['name'],
            'headers' => $result['headers'],
            'row_count' => $result['row_count']
        ), 3600); // 1 hour
        
        wp_send_json_success(array(
            'import_id' => $import_id,
            'headers' => $result['headers'],
            'preview_rows' => $result['preview_rows'],
            'row_count' => $result['row_count']
        ));
    }
    
    /**
     * Parse uploaded file
     */
    private function parse_file($file_path, $file_type) {
        if ($file_type === 'csv') {
            return $this->parse_csv($file_path);
        } elseif (in_array($file_type, array('xlsx', 'xls'))) {
            return $this->parse_xlsx($file_path);
        }
        
        return new WP_Error('invalid_type', __('Tipo file non supportato', 'friends-gestionale'));
    }
    
    /**
     * Parse CSV file
     */
    private function parse_csv($file_path) {
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return new WP_Error('file_error', __('Impossibile aprire il file', 'friends-gestionale'));
        }
        
        // Detect delimiter
        $first_line = fgets($handle);
        rewind($handle);
        
        $delimiters = array(',', ';', "\t");
        $delimiter_counts = array();
        foreach ($delimiters as $delim) {
            $delimiter_counts[$delim] = substr_count($first_line, $delim);
        }
        arsort($delimiter_counts);
        $delimiter = key($delimiter_counts);
        
        // Read headers
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            fclose($handle);
            return new WP_Error('parse_error', __('Impossibile leggere le intestazioni', 'friends-gestionale'));
        }
        
        // Clean headers
        $headers = array_map('trim', $headers);
        
        // Read preview rows (max 100)
        $preview_rows = array();
        $row_count = 0;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false && count($preview_rows) < 100) {
            if (count($row) === count($headers)) {
                $row_data = array();
                foreach ($headers as $i => $header) {
                    // Use deep_trim to remove all whitespace including non-breaking spaces
                    $row_data[$header] = isset($row[$i]) ? $this->deep_trim($row[$i]) : '';
                }
                $preview_rows[] = $row_data;
                $row_count++;
            }
        }
        
        // Count remaining rows
        while (fgets($handle) !== false) {
            $row_count++;
        }
        
        fclose($handle);
        
        return array(
            'headers' => $headers,
            'preview_rows' => $preview_rows,
            'row_count' => $row_count
        );
    }
    
    /**
     * Parse XLSX file (simplified - requires SimpleXLSX library or manual implementation)
     */
    private function parse_xlsx($file_path) {
        // Check if SimpleXLSX class is available
        if (!class_exists('Shuchkin\SimpleXLSX')) {
            // Try to include the library if it exists
            $lib_path = FRIENDS_GESTIONALE_PLUGIN_DIR . 'includes/lib/simplexlsx.php';
            if (file_exists($lib_path)) {
                require_once $lib_path;
            } else {
                return new WP_Error('library_missing', __('Libreria XLSX non disponibile. Usa file CSV.', 'friends-gestionale'));
            }
        }
        
        // If we still don't have the library, return error
        if (!class_exists('Shuchkin\SimpleXLSX')) {
            return new WP_Error('library_missing', __('Libreria XLSX non disponibile. Usa file CSV.', 'friends-gestionale'));
        }
        
        // Parse XLSX
        $xlsx = \Shuchkin\SimpleXLSX::parse($file_path);
        if (!$xlsx) {
            return new WP_Error('parse_error', __('Errore nella lettura del file XLSX', 'friends-gestionale'));
        }
        
        $rows = $xlsx->rows();
        if (empty($rows)) {
            return new WP_Error('empty_file', __('File vuoto', 'friends-gestionale'));
        }
        
        $headers = array_shift($rows);
        $headers = array_map('trim', $headers);
        
        $preview_rows = array();
        $row_count = 0;
        foreach ($rows as $row) {
            if (count($preview_rows) < 100) {
                $row_data = array();
                foreach ($headers as $i => $header) {
                    // Use deep_trim to remove all whitespace including non-breaking spaces
                    $row_data[$header] = isset($row[$i]) ? $this->deep_trim($row[$i]) : '';
                }
                $preview_rows[] = $row_data;
            }
            $row_count++;
        }
        
        return array(
            'headers' => $headers,
            'preview_rows' => $preview_rows,
            'row_count' => $row_count
        );
    }
    
    /**
     * AJAX: Preview import
     */
    public function ajax_preview_import() {
        check_ajax_referer('fg-import-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permessi insufficienti', 'friends-gestionale')));
        }
        
        $import_id = sanitize_text_field($_POST['import_id']);
        $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : array();
        $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === 'true';
        
        $import_data = get_transient('fg_import_' . $import_id);
        if (!$import_data) {
            wp_send_json_error(array('message' => __('Sessione di import scaduta', 'friends-gestionale')));
        }
        
        // Re-parse file to get all rows for preview
        $parse_result = $this->parse_file($import_data['file_path'], $import_data['file_type']);
        if (is_wp_error($parse_result)) {
            wp_send_json_error(array('message' => $parse_result->get_error_message()));
        }
        
        // Validate and preview each row
        $preview = array(
            'total' => count($parse_result['preview_rows']),
            'will_create' => 0,
            'will_update' => 0,
            'will_skip' => 0,
            'has_errors' => 0,
            'rows' => array()
        );
        
        foreach ($parse_result['preview_rows'] as $row_data) {
            $row_preview = $this->validate_and_preview_row($row_data, $mapping, $update_existing);
            $preview['rows'][] = $row_preview;
            
            if ($row_preview['status'] === 'create') {
                $preview['will_create']++;
            } elseif ($row_preview['status'] === 'update') {
                $preview['will_update']++;
            } elseif ($row_preview['status'] === 'skip') {
                $preview['will_skip']++;
            } elseif ($row_preview['status'] === 'error') {
                $preview['has_errors']++;
            }
        }
        
        wp_send_json_success($preview);
    }
    
    /**
     * Deep trim: removes all types of whitespace including non-breaking spaces
     */
    private function deep_trim($value) {
        if (!is_string($value)) {
            return $value;
        }
        // Remove regular spaces, tabs, newlines, and non-breaking spaces (char 160, &nbsp;)
        // Also remove other Unicode whitespace characters
        $value = trim($value);
        $value = preg_replace('/^[\s\x{00A0}\x{200B}\x{FEFF}]+|[\s\x{00A0}\x{200B}\x{FEFF}]+$/u', '', $value);
        return $value;
    }
    
    /**
     * Validate and preview a single row
     */
    private function validate_and_preview_row($row_data, $mapping, $update_existing) {
        $errors = array();
        $warnings = array();
        $mapped_data = array();
        
        // Map columns to fields
        foreach ($mapping as $field => $source) {
            if (strpos($source, 'static:') === 0) {
                // Static value
                $mapped_data[$field] = $this->deep_trim(substr($source, 7));
            } elseif ($source !== '' && $source !== 'skip') {
                // Column value - apply deep trim to remove all whitespace types
                $mapped_data[$field] = isset($row_data[$source]) ? $this->deep_trim($row_data[$source]) : '';
            }
        }
        
        // Validation rules
        $ragione_sociale = isset($mapped_data['ragione_sociale']) ? $mapped_data['ragione_sociale'] : '';
        $nome = isset($mapped_data['nome']) ? $mapped_data['nome'] : '';
        $cognome = isset($mapped_data['cognome']) ? $mapped_data['cognome'] : '';
        $email = isset($mapped_data['email']) ? $mapped_data['email'] : '';
        $ruolo_value = isset($mapped_data['ruolo']) ? $mapped_data['ruolo'] : '';
        
        // Normalize ruolo value
        // If contains "donatore" -> it's a donor (solo_donatore)
        // Otherwise, treat as socio category name (anche_socio)
        $tipo_donatore = 'anche_socio';
        $categoria_socio_name = '';
        
        if (!empty($ruolo_value)) {
            if (stripos($ruolo_value, 'donatore') !== false || stripos($ruolo_value, 'donor') !== false) {
                $tipo_donatore = 'solo_donatore';
            } else {
                // It's a socio with a category
                $tipo_donatore = 'anche_socio';
                $categoria_socio_name = $ruolo_value;
            }
        }
        
        $mapped_data['tipo_donatore'] = $tipo_donatore;
        $mapped_data['categoria_socio_name'] = $categoria_socio_name;
        
        // Rule: Either ragione_sociale OR (nome AND cognome) required
        if (empty($ragione_sociale)) {
            if (empty($nome) && empty($cognome)) {
                $errors[] = __('Richiesto almeno Nome e Cognome o Ragione Sociale', 'friends-gestionale');
            } elseif (empty($nome)) {
                $errors[] = __('Nome richiesto', 'friends-gestionale');
            } elseif (empty($cognome)) {
                $errors[] = __('Cognome richiesto', 'friends-gestionale');
            }
        }
        
        // Email validation
        if (!empty($email) && !is_email($email)) {
            $errors[] = __('Email non valida', 'friends-gestionale');
        }
        
        // Check if email exists
        $existing_post = null;
        if (!empty($email)) {
            $existing_post = $this->find_donor_by_email($email);
        }
        
        // Determine action
        $status = 'create';
        $action_label = __('Nuovo', 'friends-gestionale');
        
        if ($existing_post) {
            if ($update_existing) {
                $status = 'update';
                $action_label = __('Aggiorna', 'friends-gestionale');
                $warnings[] = __('Email già esistente - il record verrà aggiornato', 'friends-gestionale');
            } else {
                $status = 'create';
                $action_label = __('Crea nuovo', 'friends-gestionale');
                $warnings[] = __('Email già esistente - verrà creato un nuovo record (duplicato)', 'friends-gestionale');
            }
        }
        
        // Default data_iscrizione for soci
        if ($tipo_donatore === 'anche_socio') {
            if (empty($mapped_data['data_iscrizione'])) {
                $mapped_data['data_iscrizione'] = current_time('Y-m-d');
                $warnings[] = __('Data iscrizione impostata a oggi', 'friends-gestionale');
            }
        }
        
        if (!empty($errors)) {
            $status = 'error';
            $action_label = __('Errore', 'friends-gestionale');
        }
        
        return array(
            'status' => $status,
            'action_label' => $action_label,
            'data' => $mapped_data,
            'errors' => $errors,
            'warnings' => $warnings,
            'original' => $row_data
        );
    }
    
    /**
     * Find donor by email
     */
    private function find_donor_by_email($email) {
        $args = array(
            'post_type' => 'fg_socio',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_fg_email',
                    'value' => $email,
                    'compare' => '='
                )
            )
        );
        
        $posts = get_posts($args);
        return !empty($posts) ? $posts[0] : null;
    }
    
    /**
     * AJAX: Execute import
     */
    public function ajax_execute_import() {
        check_ajax_referer('fg-import-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permessi insufficienti', 'friends-gestionale')));
        }
        
        $import_id = sanitize_text_field($_POST['import_id']);
        $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : array();
        $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === 'true';
        
        $import_data = get_transient('fg_import_' . $import_id);
        if (!$import_data) {
            wp_send_json_error(array('message' => __('Sessione di import scaduta', 'friends-gestionale')));
        }
        
        // Re-parse file to get all rows
        $parse_result = $this->parse_file($import_data['file_path'], $import_data['file_type']);
        if (is_wp_error($parse_result)) {
            wp_send_json_error(array('message' => $parse_result->get_error_message()));
        }
        
        // Process all rows
        $results = array(
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => array()
        );
        
        foreach ($parse_result['preview_rows'] as $index => $row_data) {
            $row_preview = $this->validate_and_preview_row($row_data, $mapping, $update_existing);
            
            if ($row_preview['status'] === 'error') {
                $results['errors'][] = array(
                    'row' => $index + 2, // +2 because of header row and 0-index
                    'data' => $row_data,
                    'errors' => $row_preview['errors']
                );
                continue;
            }
            
            if ($row_preview['status'] === 'skip') {
                $results['skipped']++;
                continue;
            }
            
            // Create or update donor
            $post_id = $this->create_or_update_donor($row_preview['data'], $row_preview['status'] === 'update');
            
            if (is_wp_error($post_id)) {
                $results['errors'][] = array(
                    'row' => $index + 2,
                    'data' => $row_data,
                    'errors' => array($post_id->get_error_message())
                );
            } else {
                if ($row_preview['status'] === 'update') {
                    $results['updated']++;
                } else {
                    $results['created']++;
                }
            }
        }
        
        // Clean up
        if (file_exists($import_data['file_path'])) {
            unlink($import_data['file_path']);
        }
        delete_transient('fg_import_' . $import_id);
        
        // Generate error CSV if there are errors
        $error_csv_url = '';
        if (!empty($results['errors'])) {
            $error_csv_url = $this->generate_error_csv($results['errors'], $import_data['headers']);
        }
        
        $results['error_csv_url'] = $error_csv_url;
        
        wp_send_json_success($results);
    }
    
    /**
     * Create or update donor
     */
    private function create_or_update_donor($data, $update = false) {
        // Find existing post if update
        $post_id = 0;
        if ($update && !empty($data['email'])) {
            $existing = $this->find_donor_by_email($data['email']);
            if ($existing) {
                $post_id = $existing->ID;
            }
        }
        
        // Determine post title (nome completo or ragione sociale)
        $post_title = '';
        if (!empty($data['ragione_sociale'])) {
            $post_title = $data['ragione_sociale'];
        } elseif (!empty($data['nome']) || !empty($data['cognome'])) {
            $nome = isset($data['nome']) ? $data['nome'] : '';
            $cognome = isset($data['cognome']) ? $data['cognome'] : '';
            $post_title = trim($nome . ' ' . $cognome);
        }
        
        // Create or update post
        $post_data = array(
            'post_type' => 'fg_socio',
            'post_status' => 'publish',
            'post_title' => $post_title
        );
        
        if ($post_id > 0) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        $post_id = $result;
        
        // Update meta fields
        $meta_mapping = array(
            'ragione_sociale' => '_fg_ragione_sociale',
            'nome' => '_fg_nome',
            'cognome' => '_fg_cognome',
            'email' => '_fg_email',
            'telefono' => '_fg_telefono',
            'indirizzo' => '_fg_indirizzo',
            'citta' => '_fg_citta',
            'cap' => '_fg_cap',
            'provincia' => '_fg_provincia',
            'nazione' => '_fg_nazione',
            'codice_fiscale' => '_fg_codice_fiscale',
            'partita_iva' => '_fg_partita_iva',
            'data_iscrizione' => '_fg_data_iscrizione',
            'note' => '_fg_note',
            'tipo_donatore' => '_fg_tipo_donatore'
        );
        
        foreach ($meta_mapping as $key => $meta_key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                update_post_meta($post_id, $meta_key, sanitize_text_field($data[$key]));
            }
        }
        
        // Set tipo_persona based on ragione_sociale
        if (!empty($data['ragione_sociale'])) {
            update_post_meta($post_id, '_fg_tipo_persona', 'societa');
        } else {
            update_post_meta($post_id, '_fg_tipo_persona', 'privato');
        }
        
        // Set categoria_socio if it's a socio with a category
        if (!empty($data['categoria_socio_name']) && $data['tipo_donatore'] === 'anche_socio') {
            // Find or create the category term
            $term = get_term_by('name', $data['categoria_socio_name'], 'fg_categoria_socio');
            if (!$term) {
                // Try case-insensitive search
                $terms = get_terms(array(
                    'taxonomy' => 'fg_categoria_socio',
                    'hide_empty' => false,
                ));
                foreach ($terms as $t) {
                    if (strcasecmp($t->name, $data['categoria_socio_name']) === 0) {
                        $term = $t;
                        break;
                    }
                }
            }
            
            if ($term) {
                // Assign the existing category
                wp_set_post_terms($post_id, array($term->term_id), 'fg_categoria_socio', false);
                
                // Get quota_annuale from category term meta
                $quota_annuale = get_term_meta($term->term_id, 'fg_quota_associativa', true);
                if ($quota_annuale) {
                    update_post_meta($post_id, '_fg_quota_annuale', floatval($quota_annuale));
                }
            }
            // Note: If category doesn't exist, we don't create it - user needs to create categories first
        }
        
        // Set default data_scadenza if not provided and it's a socio
        if (empty($data['data_scadenza']) && $data['tipo_donatore'] === 'anche_socio' && !empty($data['data_iscrizione'])) {
            // Calculate default expiration: current year + month/day from registration
            // If already past, use next year
            $data_iscrizione = $data['data_iscrizione'];
            $iscrizione_date = DateTime::createFromFormat('Y-m-d', $data_iscrizione);
            
            if ($iscrizione_date) {
                $current_year = date('Y');
                $month = $iscrizione_date->format('m');
                $day = $iscrizione_date->format('d');
                
                // Create expiration date for current year
                $data_scadenza = $current_year . '-' . $month . '-' . $day;
                $scadenza_date = DateTime::createFromFormat('Y-m-d', $data_scadenza);
                $today = new DateTime();
                
                // If expiration date is in the past, use next year
                if ($scadenza_date < $today) {
                    $data_scadenza = ($current_year + 1) . '-' . $month . '-' . $day;
                }
                
                update_post_meta($post_id, '_fg_data_scadenza', $data_scadenza);
            }
        } elseif (!empty($data['data_scadenza'])) {
            // Use provided data_scadenza
            update_post_meta($post_id, '_fg_data_scadenza', sanitize_text_field($data['data_scadenza']));
        }
        
        // Set default stato to attivo
        update_post_meta($post_id, '_fg_stato', 'attivo');
        
        return $post_id;
    }
    
    /**
     * Generate error CSV
     */
    private function generate_error_csv($errors, $headers) {
        $upload_dir = wp_upload_dir();
        $csv_filename = 'import-errors-' . date('Y-m-d-His') . '.csv';
        $csv_path = $upload_dir['basedir'] . '/' . $csv_filename;
        
        $handle = fopen($csv_path, 'w');
        if (!$handle) {
            return '';
        }
        
        // Write BOM for Excel UTF-8 compatibility
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header
        $csv_headers = array_merge(array('Riga', 'Errori'), $headers);
        fputcsv($handle, $csv_headers);
        
        // Write error rows
        foreach ($errors as $error) {
            $row = array(
                $error['row'],
                implode('; ', $error['errors'])
            );
            foreach ($headers as $header) {
                $row[] = isset($error['data'][$header]) ? $error['data'][$header] : '';
            }
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        
        return $upload_dir['baseurl'] . '/' . $csv_filename;
    }
    
    /**
     * AJAX: Save mapping template
     */
    public function ajax_save_mapping_template() {
        check_ajax_referer('fg-import-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permessi insufficienti', 'friends-gestionale')));
        }
        
        $template_name = sanitize_text_field($_POST['template_name']);
        $mapping = isset($_POST['mapping']) ? $_POST['mapping'] : array();
        
        if (empty($template_name)) {
            wp_send_json_error(array('message' => __('Nome template richiesto', 'friends-gestionale')));
        }
        
        $templates = get_option('fg_import_mapping_templates', array());
        $template_key = sanitize_key($template_name);
        
        $templates[$template_key] = array(
            'name' => $template_name,
            'mapping' => $mapping,
            'created' => current_time('mysql')
        );
        
        update_option('fg_import_mapping_templates', $templates);
        
        wp_send_json_success(array('message' => __('Template salvato con successo', 'friends-gestionale')));
    }
    
    /**
     * AJAX: Load mapping template
     */
    public function ajax_load_mapping_template() {
        check_ajax_referer('fg-import-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permessi insufficienti', 'friends-gestionale')));
        }
        
        $template_key = sanitize_text_field($_POST['template_key']);
        
        $templates = get_option('fg_import_mapping_templates', array());
        
        if (!isset($templates[$template_key])) {
            wp_send_json_error(array('message' => __('Template non trovato', 'friends-gestionale')));
        }
        
        wp_send_json_success($templates[$template_key]);
    }
    
    /**
     * Get available fields for mapping
     */
    public static function get_mappable_fields() {
        return array(
            'ragione_sociale' => __('Ragione Sociale (per società)', 'friends-gestionale'),
            'nome' => __('Nome*', 'friends-gestionale'),
            'cognome' => __('Cognome*', 'friends-gestionale'),
            'email' => __('Email', 'friends-gestionale'),
            'telefono' => __('Telefono', 'friends-gestionale'),
            'indirizzo' => __('Indirizzo', 'friends-gestionale'),
            'citta' => __('Città', 'friends-gestionale'),
            'cap' => __('CAP', 'friends-gestionale'),
            'provincia' => __('Provincia', 'friends-gestionale'),
            'nazione' => __('Nazione', 'friends-gestionale'),
            'ruolo' => __('Ruolo (socio/donatore) ⓘ', 'friends-gestionale'),
            'data_iscrizione' => __('Data Iscrizione', 'friends-gestionale'),
            'data_scadenza' => __('Data Scadenza', 'friends-gestionale'),
            'partita_iva' => __('Partita IVA', 'friends-gestionale'),
            'codice_fiscale' => __('Codice Fiscale', 'friends-gestionale'),
            'note' => __('Note', 'friends-gestionale')
        );
    }
    
    /**
     * Get field tooltips
     */
    public static function get_field_tooltips() {
        return array(
            'ruolo' => __('Se contiene "donatore" → Donatore. Altrimenti → Socio con categoria (usa il nome della categoria socio presente nella cella)', 'friends-gestionale')
        );
    }
}
