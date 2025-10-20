<?php
/**
 * Shortcodes
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('fg_elenco_soci', array($this, 'elenco_soci_shortcode'));
        add_shortcode('fg_dettaglio_socio', array($this, 'dettaglio_socio_shortcode'));
        add_shortcode('fg_elenco_raccolte', array($this, 'elenco_raccolte_shortcode'));
        add_shortcode('fg_dettaglio_raccolta', array($this, 'dettaglio_raccolta_shortcode'));
        add_shortcode('fg_progress_bar', array($this, 'progress_bar_shortcode'));
        add_shortcode('fg_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('fg_filtro_soci', array($this, 'filtro_soci_shortcode'));
    }
    
    /**
     * Elenco Donatori Shortcode
     * Usage: [fg_elenco_soci categoria="" stato="attivo" limite="10"]
     */
    public function elenco_soci_shortcode($atts) {
        $atts = shortcode_atts(array(
            'categoria' => '',
            'stato' => '',
            'limite' => -1,
            'ordina' => 'title',
            'ordine' => 'ASC'
        ), $atts);
        
        $args = array(
            'post_type' => 'fg_socio',
            'posts_per_page' => intval($atts['limite']),
            'orderby' => $atts['ordina'],
            'order' => $atts['ordine']
        );
        
        // Add meta query for stato
        if (!empty($atts['stato'])) {
            $args['meta_query'] = array(
                array(
                    'key' => '_fg_stato',
                    'value' => $atts['stato'],
                    'compare' => '='
                )
            );
        }
        
        // Add taxonomy query for categoria
        if (!empty($atts['categoria'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'fg_categoria_socio',
                    'field' => 'slug',
                    'terms' => $atts['categoria']
                )
            );
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="fg-elenco-soci">';
            echo '<table class="fg-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . __('Nome', 'friends-gestionale') . '</th>';
            echo '<th>' . __('Email', 'friends-gestionale') . '</th>';
            echo '<th>' . __('Telefono', 'friends-gestionale') . '</th>';
            echo '<th>' . __('Stato', 'friends-gestionale') . '</th>';
            echo '<th>' . __('Scadenza', 'friends-gestionale') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $email = get_post_meta($post_id, '_fg_email', true);
                $telefono = get_post_meta($post_id, '_fg_telefono', true);
                $stato = get_post_meta($post_id, '_fg_stato', true);
                $data_scadenza = get_post_meta($post_id, '_fg_data_scadenza', true);
                
                echo '<tr>';
                echo '<td><a href="' . get_permalink() . '">' . get_the_title() . '</a></td>';
                echo '<td>' . esc_html($email) . '</td>';
                echo '<td>' . esc_html($telefono) . '</td>';
                echo '<td><span class="fg-stato fg-stato-' . esc_attr($stato) . '">' . esc_html(ucfirst($stato)) . '</span></td>';
                echo '<td>' . esc_html($data_scadenza ? date_i18n(get_option('date_format'), strtotime($data_scadenza)) : '-') . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        } else {
            echo '<p>' . __('Nessun socio trovato.', 'friends-gestionale') . '</p>';
        }
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Dettaglio Donatore Shortcode
     * Usage: [fg_dettaglio_socio id="123"]
     */
    public function dettaglio_socio_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => get_the_ID()
        ), $atts);
        
        $post_id = intval($atts['id']);
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'fg_socio') {
            return '<p>' . __('Donatore non trovato.', 'friends-gestionale') . '</p>';
        }
        
        $codice_fiscale = get_post_meta($post_id, '_fg_codice_fiscale', true);
        $email = get_post_meta($post_id, '_fg_email', true);
        $telefono = get_post_meta($post_id, '_fg_telefono', true);
        $indirizzo = get_post_meta($post_id, '_fg_indirizzo', true);
        $data_iscrizione = get_post_meta($post_id, '_fg_data_iscrizione', true);
        $data_scadenza = get_post_meta($post_id, '_fg_data_scadenza', true);
        $quota_annuale = get_post_meta($post_id, '_fg_quota_annuale', true);
        $stato = get_post_meta($post_id, '_fg_stato', true);
        
        ob_start();
        ?>
        <div class="fg-dettaglio-socio">
            <h3><?php echo esc_html($post->post_title); ?></h3>
            
            <div class="fg-socio-info">
                <?php if ($codice_fiscale): ?>
                    <p><strong><?php _e('Codice Fiscale:', 'friends-gestionale'); ?></strong> <?php echo esc_html($codice_fiscale); ?></p>
                <?php endif; ?>
                
                <?php if ($email): ?>
                    <p><strong><?php _e('Email:', 'friends-gestionale'); ?></strong> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                <?php endif; ?>
                
                <?php if ($telefono): ?>
                    <p><strong><?php _e('Telefono:', 'friends-gestionale'); ?></strong> <?php echo esc_html($telefono); ?></p>
                <?php endif; ?>
                
                <?php if ($indirizzo): ?>
                    <p><strong><?php _e('Indirizzo:', 'friends-gestionale'); ?></strong> <?php echo nl2br(esc_html($indirizzo)); ?></p>
                <?php endif; ?>
                
                <?php if ($data_iscrizione): ?>
                    <p><strong><?php _e('Data Iscrizione:', 'friends-gestionale'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($data_iscrizione)); ?></p>
                <?php endif; ?>
                
                <?php if ($data_scadenza): ?>
                    <p><strong><?php _e('Data Scadenza:', 'friends-gestionale'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($data_scadenza)); ?></p>
                <?php endif; ?>
                
                <?php if ($quota_annuale): ?>
                    <p><strong><?php _e('Quota Annuale:', 'friends-gestionale'); ?></strong> €<?php echo number_format($quota_annuale, 2, ',', '.'); ?></p>
                <?php endif; ?>
                
                <?php if ($stato): ?>
                    <p><strong><?php _e('Stato:', 'friends-gestionale'); ?></strong> <span class="fg-stato fg-stato-<?php echo esc_attr($stato); ?>"><?php echo esc_html(ucfirst($stato)); ?></span></p>
                <?php endif; ?>
            </div>
            
            <?php if ($post->post_content): ?>
                <div class="fg-socio-descrizione">
                    <?php echo wpautop($post->post_content); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Elenco Raccolte Shortcode
     * Usage: [fg_elenco_raccolte stato="attiva" limite="5"]
     */
    public function elenco_raccolte_shortcode($atts) {
        $atts = shortcode_atts(array(
            'stato' => '',
            'limite' => -1
        ), $atts);
        
        $args = array(
            'post_type' => 'fg_raccolta',
            'posts_per_page' => intval($atts['limite']),
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        if (!empty($atts['stato'])) {
            $args['meta_query'] = array(
                array(
                    'key' => '_fg_stato',
                    'value' => $atts['stato'],
                    'compare' => '='
                )
            );
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($query->have_posts()) {
            echo '<div class="fg-elenco-raccolte">';
            
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $obiettivo = get_post_meta($post_id, '_fg_obiettivo', true);
                $raccolto = get_post_meta($post_id, '_fg_raccolto', true);
                $data_fine = get_post_meta($post_id, '_fg_data_fine', true);
                $percentuale = $obiettivo > 0 ? ($raccolto / $obiettivo) * 100 : 0;
                
                echo '<div class="fg-raccolta-card">';
                
                if (has_post_thumbnail()) {
                    echo '<div class="fg-raccolta-thumb">';
                    the_post_thumbnail('medium');
                    echo '</div>';
                }
                
                echo '<div class="fg-raccolta-content">';
                echo '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                echo '<div class="fg-raccolta-excerpt">' . get_the_excerpt() . '</div>';
                
                echo '<div class="fg-raccolta-progress">';
                echo '<div class="fg-progress-bar">';
                echo '<div class="fg-progress-fill" style="width: ' . min(100, $percentuale) . '%"></div>';
                echo '</div>';
                echo '<div class="fg-progress-text">';
                echo '<span class="fg-raccolto">€' . number_format($raccolto, 2) . '</span>';
                echo ' / ';
                echo '<span class="fg-obiettivo">€' . number_format($obiettivo, 2) . '</span>';
                echo ' <span class="fg-percentuale">(' . number_format($percentuale, 1) . '%)</span>';
                echo '</div>';
                echo '</div>';
                
                if ($data_fine) {
                    $giorni_rimanenti = ceil((strtotime($data_fine) - time()) / 86400);
                    if ($giorni_rimanenti > 0) {
                        echo '<p class="fg-scadenza">' . sprintf(__('Termina tra %d giorni', 'friends-gestionale'), $giorni_rimanenti) . '</p>';
                    }
                }
                
                echo '<a href="' . get_permalink() . '" class="fg-button">' . __('Scopri di più', 'friends-gestionale') . '</a>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<p>' . __('Nessuna raccolta fondi trovata.', 'friends-gestionale') . '</p>';
        }
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Dettaglio Raccolta Shortcode
     * Usage: [fg_dettaglio_raccolta id="123"]
     */
    public function dettaglio_raccolta_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => get_the_ID()
        ), $atts);
        
        $post_id = intval($atts['id']);
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'fg_raccolta') {
            return '<p>' . __('Raccolta fondi non trovata.', 'friends-gestionale') . '</p>';
        }
        
        $obiettivo = get_post_meta($post_id, '_fg_obiettivo', true);
        $raccolto = get_post_meta($post_id, '_fg_raccolto', true);
        $data_inizio = get_post_meta($post_id, '_fg_data_inizio', true);
        $data_fine = get_post_meta($post_id, '_fg_data_fine', true);
        $stato = get_post_meta($post_id, '_fg_stato', true);
        $percentuale = $obiettivo > 0 ? ($raccolto / $obiettivo) * 100 : 0;
        
        ob_start();
        ?>
        <div class="fg-dettaglio-raccolta">
            <h2><?php echo esc_html($post->post_title); ?></h2>
            
            <?php if (has_post_thumbnail()): ?>
                <div class="fg-raccolta-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>
            
            <div class="fg-raccolta-progress-large">
                <div class="fg-progress-bar-large">
                    <div class="fg-progress-fill" style="width: <?php echo min(100, $percentuale); ?>%"></div>
                </div>
                <div class="fg-progress-stats">
                    <div class="fg-stat">
                        <span class="fg-stat-value">€<?php echo number_format($raccolto, 2); ?></span>
                        <span class="fg-stat-label"><?php _e('Raccolto', 'friends-gestionale'); ?></span>
                    </div>
                    <div class="fg-stat">
                        <span class="fg-stat-value">€<?php echo number_format($obiettivo, 2); ?></span>
                        <span class="fg-stat-label"><?php _e('Obiettivo', 'friends-gestionale'); ?></span>
                    </div>
                    <div class="fg-stat">
                        <span class="fg-stat-value"><?php echo number_format($percentuale, 1); ?>%</span>
                        <span class="fg-stat-label"><?php _e('Completato', 'friends-gestionale'); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($post->post_content): ?>
                <div class="fg-raccolta-descrizione">
                    <?php echo wpautop($post->post_content); ?>
                </div>
            <?php endif; ?>
            
            <div class="fg-raccolta-info">
                <?php if ($data_inizio): ?>
                    <p><strong><?php _e('Data Inizio:', 'friends-gestionale'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($data_inizio)); ?></p>
                <?php endif; ?>
                
                <?php if ($data_fine): ?>
                    <p><strong><?php _e('Data Fine:', 'friends-gestionale'); ?></strong> <?php echo date_i18n(get_option('date_format'), strtotime($data_fine)); ?></p>
                <?php endif; ?>
                
                <?php if ($stato): ?>
                    <p><strong><?php _e('Stato:', 'friends-gestionale'); ?></strong> <span class="fg-stato"><?php echo esc_html(ucfirst($stato)); ?></span></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Progress Bar Shortcode
     * Usage: [fg_progress_bar raccolta_id="123"]
     */
    public function progress_bar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'raccolta_id' => ''
        ), $atts);
        
        if (empty($atts['raccolta_id'])) {
            return '';
        }
        
        $post_id = intval($atts['raccolta_id']);
        $obiettivo = get_post_meta($post_id, '_fg_obiettivo', true);
        $raccolto = get_post_meta($post_id, '_fg_raccolto', true);
        $percentuale = $obiettivo > 0 ? ($raccolto / $obiettivo) * 100 : 0;
        
        ob_start();
        ?>
        <div class="fg-progress-widget">
            <div class="fg-progress-bar">
                <div class="fg-progress-fill" style="width: <?php echo min(100, $percentuale); ?>%"></div>
            </div>
            <div class="fg-progress-text">
                <span>€<?php echo number_format($raccolto, 2); ?> / €<?php echo number_format($obiettivo, 2); ?></span>
                <span><?php echo number_format($percentuale, 1); ?>%</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Dashboard Shortcode
     * Usage: [fg_dashboard]
     */
    public function dashboard_shortcode($atts) {
        if (!current_user_can('manage_options')) {
            return '<p>' . __('Non hai i permessi per visualizzare questa dashboard.', 'friends-gestionale') . '</p>';
        }
        
        // Get statistics
        $total_soci = wp_count_posts('fg_socio')->publish;
        $total_pagamenti = wp_count_posts('fg_pagamento')->publish;
        $total_raccolte = wp_count_posts('fg_raccolta')->publish;
        
        // Get active members
        $soci_attivi = new WP_Query(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_stato',
                    'value' => 'attivo'
                )
            )
        ));
        $count_attivi = $soci_attivi->found_posts;
        
        // Calculate total payments
        $pagamenti = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1
        ));
        $totale_incassi = 0;
        foreach ($pagamenti as $pagamento) {
            $totale_incassi += floatval(get_post_meta($pagamento->ID, '_fg_importo', true));
        }
        
        ob_start();
        ?>
        <div class="fg-dashboard">
            <h2><?php _e('Dashboard Statistica', 'friends-gestionale'); ?></h2>
            
            <div class="fg-stats-grid">
                <div class="fg-stat-box">
                    <div class="fg-stat-icon dashicons dashicons-groups"></div>
                    <div class="fg-stat-content">
                        <div class="fg-stat-number"><?php echo $total_soci; ?></div>
                        <div class="fg-stat-label"><?php _e('Totale Donatori', 'friends-gestionale'); ?></div>
                    </div>
                </div>
                
                <div class="fg-stat-box">
                    <div class="fg-stat-icon dashicons dashicons-yes"></div>
                    <div class="fg-stat-content">
                        <div class="fg-stat-number"><?php echo $count_attivi; ?></div>
                        <div class="fg-stat-label"><?php _e('Donatori Attivi', 'friends-gestionale'); ?></div>
                    </div>
                </div>
                
                <div class="fg-stat-box">
                    <div class="fg-stat-icon dashicons dashicons-money-alt"></div>
                    <div class="fg-stat-content">
                        <div class="fg-stat-number">€<?php echo number_format($totale_incassi, 2); ?></div>
                        <div class="fg-stat-label"><?php _e('Totale Incassi', 'friends-gestionale'); ?></div>
                    </div>
                </div>
                
                <div class="fg-stat-box">
                    <div class="fg-stat-icon dashicons dashicons-heart"></div>
                    <div class="fg-stat-content">
                        <div class="fg-stat-number"><?php echo $total_raccolte; ?></div>
                        <div class="fg-stat-label"><?php _e('Raccolte Fondi', 'friends-gestionale'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="fg-export-section">
                <h3><?php _e('Esportazione Dati', 'friends-gestionale'); ?></h3>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=fg-export&type=soci'); ?>" class="button button-primary"><?php _e('Esporta Donatori (CSV)', 'friends-gestionale'); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=fg-export&type=pagamenti'); ?>" class="button button-primary"><?php _e('Esporta Pagamenti (CSV)', 'friends-gestionale'); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=fg-export&type=raccolte'); ?>" class="button button-primary"><?php _e('Esporta Raccolte (CSV)', 'friends-gestionale'); ?></a>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Filtro Donatori Shortcode
     * Usage: [fg_filtro_soci]
     */
    public function filtro_soci_shortcode($atts) {
        ob_start();
        ?>
        <div class="fg-filtro-soci">
            <form method="get" class="fg-filter-form">
                <div class="fg-filter-row">
                    <div class="fg-filter-field">
                        <label for="fg_filter_stato"><?php _e('Stato:', 'friends-gestionale'); ?></label>
                        <select name="fg_filter_stato" id="fg_filter_stato">
                            <option value=""><?php _e('Tutti', 'friends-gestionale'); ?></option>
                            <option value="attivo" <?php selected(isset($_GET['fg_filter_stato']) ? $_GET['fg_filter_stato'] : '', 'attivo'); ?>><?php _e('Attivo', 'friends-gestionale'); ?></option>
                            <option value="sospeso" <?php selected(isset($_GET['fg_filter_stato']) ? $_GET['fg_filter_stato'] : '', 'sospeso'); ?>><?php _e('Sospeso', 'friends-gestionale'); ?></option>
                            <option value="scaduto" <?php selected(isset($_GET['fg_filter_stato']) ? $_GET['fg_filter_stato'] : '', 'scaduto'); ?>><?php _e('Scaduto', 'friends-gestionale'); ?></option>
                        </select>
                    </div>
                    
                    <div class="fg-filter-field">
                        <label for="fg_filter_search"><?php _e('Cerca:', 'friends-gestionale'); ?></label>
                        <input type="text" name="fg_filter_search" id="fg_filter_search" value="<?php echo isset($_GET['fg_filter_search']) ? esc_attr($_GET['fg_filter_search']) : ''; ?>" placeholder="<?php _e('Nome o email', 'friends-gestionale'); ?>" />
                    </div>
                    
                    <div class="fg-filter-field">
                        <button type="submit" class="fg-button"><?php _e('Filtra', 'friends-gestionale'); ?></button>
                    </div>
                </div>
            </form>
            
            <?php
            // Display filtered results
            if (isset($_GET['fg_filter_stato']) || isset($_GET['fg_filter_search'])) {
                $args = array(
                    'post_type' => 'fg_socio',
                    'posts_per_page' => -1
                );
                
                if (!empty($_GET['fg_filter_stato'])) {
                    $args['meta_query'] = array(
                        array(
                            'key' => '_fg_stato',
                            'value' => sanitize_text_field($_GET['fg_filter_stato'])
                        )
                    );
                }
                
                if (!empty($_GET['fg_filter_search'])) {
                    $args['s'] = sanitize_text_field($_GET['fg_filter_search']);
                }
                
                echo do_shortcode('[fg_elenco_soci stato="' . (isset($_GET['fg_filter_stato']) ? $_GET['fg_filter_stato'] : '') . '"]');
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize
new Friends_Gestionale_Shortcodes();
