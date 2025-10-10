<?php
/**
 * Admin Dashboard
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Admin_Dashboard {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Gestionale Donazioni Friends', 'friends-gestionale'),
            __('Gestionale Donazioni Friends', 'friends-gestionale'),
            'edit_posts',
            'friends-gestionale',
            array($this, 'render_dashboard'),
            'dashicons-heart',
            24
        );
        
        add_submenu_page(
            'friends-gestionale',
            __('Dashboard', 'friends-gestionale'),
            __('Dashboard', 'friends-gestionale'),
            'edit_posts',
            'friends-gestionale',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'friends-gestionale',
            __('Statistiche', 'friends-gestionale'),
            __('Statistiche', 'friends-gestionale'),
            'edit_posts',
            'fg-statistics',
            array($this, 'render_statistics')
        );
        
        add_submenu_page(
            'friends-gestionale',
            __('Impostazioni', 'friends-gestionale'),
            __('Impostazioni', 'friends-gestionale'),
            'edit_posts',
            'fg-settings',
            array($this, 'render_settings')
        );
        
        // Add Calendario Pagamenti as a top-level menu item
        add_menu_page(
            __('Calendario Pagamenti', 'friends-gestionale'),
            __('Calendario Pagamenti', 'friends-gestionale'),
            'edit_posts',
            'fg-payment-calendar',
            array($this, 'render_payment_calendar'),
            'dashicons-money-alt', // Changed icon to money instead of calendar
            31
        );
        
        // Add Calendario Eventi as a top-level menu item
        add_menu_page(
            __('Calendario Eventi', 'friends-gestionale'),
            __('Calendario Eventi', 'friends-gestionale'),
            'edit_posts',
            'fg-event-calendar',
            array($this, 'render_event_calendar'),
            'dashicons-calendar-alt',
            32
        );
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard() {
        // Get statistics
        $total_soci = wp_count_posts('fg_socio')->publish;
        $total_pagamenti = wp_count_posts('fg_pagamento')->publish;
        $total_raccolte = wp_count_posts('fg_raccolta')->publish;
        $total_eventi = wp_count_posts('fg_evento')->publish;
        
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
        
        // Get expired members
        $today = date('Y-m-d');
        $soci_scaduti = new WP_Query(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_data_scadenza',
                    'value' => $today,
                    'compare' => '<',
                    'type' => 'DATE'
                )
            )
        ));
        $count_scaduti = $soci_scaduti->found_posts;
        
        // Calculate total payments
        $pagamenti = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1
        ));
        $totale_incassi = 0;
        $count_pagamenti = 0;
        foreach ($pagamenti as $pagamento) {
            $importo = floatval(get_post_meta($pagamento->ID, '_fg_importo', true));
            $totale_incassi += $importo;
            if ($importo > 0) $count_pagamenti++;
        }
        $media_donazione = $count_pagamenti > 0 ? $totale_incassi / $count_pagamenti : 0;
        
        // Get upcoming events
        $eventi_prossimi = new WP_Query(array(
            'post_type' => 'fg_evento',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_data_evento',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        ));
        $count_eventi_prossimi = $eventi_prossimi->found_posts;
        
        // Get active fundraising campaigns
        $raccolte_attive = new WP_Query(array(
            'post_type' => 'fg_raccolta',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_stato',
                    'value' => 'attiva'
                )
            )
        ));
        $count_raccolte_attive = $raccolte_attive->found_posts;
        
        // Monthly statistics
        $primo_giorno_mese = date('Y-m-01');
        $ultimo_giorno_mese = date('Y-m-t');
        
        // Payments this month
        $pagamenti_mese = new WP_Query(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_data_pagamento',
                    'value' => array($primo_giorno_mese, $ultimo_giorno_mese),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        ));
        $count_pagamenti_mese = $pagamenti_mese->found_posts;
        
        // Revenue this month
        $incassi_mese = 0;
        if ($pagamenti_mese->have_posts()) {
            while ($pagamenti_mese->have_posts()) {
                $pagamenti_mese->the_post();
                $incassi_mese += floatval(get_post_meta(get_the_ID(), '_fg_importo', true));
            }
            wp_reset_postdata();
        }
        
        // New members this month
        $nuovi_soci_mese = new WP_Query(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'after' => $primo_giorno_mese,
                    'before' => $ultimo_giorno_mese,
                    'inclusive' => true,
                )
            )
        ));
        $count_nuovi_soci_mese = $nuovi_soci_mese->found_posts;
        
        // Get recent payments
        $pagamenti_recenti = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        ?>
        <div class="wrap fg-dashboard-wrap">
            <h1><?php _e('Dashboard Friends Gestionale', 'friends-gestionale'); ?></h1>
            
            <!-- Primary Statistics -->
            <div class="fg-dashboard-stats">
                <div class="fg-stat-card">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $total_soci; ?></h3>
                        <p><?php _e('Totale Soci', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card fg-stat-success">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-yes"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $count_attivi; ?></h3>
                        <p><?php _e('Soci Attivi', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card fg-stat-warning">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-warning"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $count_scaduti; ?></h3>
                        <p><?php _e('Soci Scaduti', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card fg-stat-info">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3>€<?php echo number_format($totale_incassi, 2, ',', '.'); ?></h3>
                        <p><?php _e('Totale Incassi', 'friends-gestionale'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Secondary Statistics -->
            <div class="fg-dashboard-stats">
                <div class="fg-stat-card">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-calendar"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $total_eventi; ?></h3>
                        <p><?php _e('Eventi Totali', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card fg-stat-success">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $count_eventi_prossimi; ?></h3>
                        <p><?php _e('Eventi Prossimi', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-heart"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $count_raccolte_attive; ?></h3>
                        <p><?php _e('Raccolte Attive', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card fg-stat-info">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3>€<?php echo number_format($media_donazione, 2, ',', '.'); ?></h3>
                        <p><?php _e('Media Donazione', 'friends-gestionale'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Statistics -->
            <div class="fg-dashboard-stats">
                <div class="fg-stat-card">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-admin-page"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $count_pagamenti_mese; ?></h3>
                        <p><?php _e('Pagamenti Questo Mese', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card fg-stat-success">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-money"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3>€<?php echo number_format($incassi_mese, 2, ',', '.'); ?></h3>
                        <p><?php _e('Incassi Questo Mese', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $count_nuovi_soci_mese; ?></h3>
                        <p><?php _e('Nuovi Soci Questo Mese', 'friends-gestionale'); ?></p>
                    </div>
                </div>
                
                <div class="fg-stat-card">
                    <div class="fg-stat-icon">
                        <span class="dashicons dashicons-megaphone"></span>
                    </div>
                    <div class="fg-stat-content">
                        <h3><?php echo $total_raccolte; ?></h3>
                        <p><?php _e('Raccolte Fondi Totali', 'friends-gestionale'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="fg-dashboard-grid">
                <div class="fg-dashboard-box">
                    <h2><?php _e('Pagamenti Recenti', 'friends-gestionale'); ?></h2>
                    <?php if (!empty($pagamenti_recenti)): ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Data', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Socio', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Importo', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Tipo', 'friends-gestionale'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagamenti_recenti as $pagamento): ?>
                                    <?php
                                    $data = get_post_meta($pagamento->ID, '_fg_data_pagamento', true);
                                    $importo = get_post_meta($pagamento->ID, '_fg_importo', true);
                                    $tipo = get_post_meta($pagamento->ID, '_fg_tipo_pagamento', true);
                                    $socio_id = get_post_meta($pagamento->ID, '_fg_socio_id', true);
                                    $socio_nome = $socio_id ? get_the_title($socio_id) : '-';
                                    ?>
                                    <tr>
                                        <td><?php echo $data ? date_i18n(get_option('date_format'), strtotime($data)) : '-'; ?></td>
                                        <td><?php echo esc_html($socio_nome); ?></td>
                                        <td>€<?php echo number_format($importo, 2, ',', '.'); ?></td>
                                        <td><?php echo esc_html(ucfirst($tipo)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('Nessun pagamento registrato.', 'friends-gestionale'); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="fg-dashboard-box">
                    <h2><?php _e('Raccolte Fondi Attive', 'friends-gestionale'); ?></h2>
                    <?php if ($raccolte_attive->have_posts()): ?>
                        <div class="fg-raccolte-list">
                            <?php while ($raccolte_attive->have_posts()): $raccolte_attive->the_post(); ?>
                                <?php
                                $obiettivo = get_post_meta(get_the_ID(), '_fg_obiettivo', true);
                                $raccolto = get_post_meta(get_the_ID(), '_fg_raccolto', true);
                                $percentuale = $obiettivo > 0 ? ($raccolto / $obiettivo) * 100 : 0;
                                ?>
                                <div class="fg-raccolta-item">
                                    <h4><?php the_title(); ?></h4>
                                    <div class="fg-progress-bar">
                                        <div class="fg-progress-fill" style="width: <?php echo min(100, $percentuale); ?>%"></div>
                                    </div>
                                    <p>€<?php echo number_format($raccolto, 2); ?> / €<?php echo number_format($obiettivo, 2); ?> (<?php echo number_format($percentuale, 1); ?>%)</p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p><?php _e('Nessuna raccolta fondi attiva.', 'friends-gestionale'); ?></p>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
            
            <!-- Eventi Riepilogo -->
            <div class="fg-dashboard-grid" style="margin-top: 20px;">
                <div class="fg-dashboard-box">
                    <h2><?php _e('Ultimi Eventi', 'friends-gestionale'); ?></h2>
                    <?php
                    // Get last 10 events (past and future)
                    $eventi_dashboard = new WP_Query(array(
                        'post_type' => 'fg_evento',
                        'posts_per_page' => 10,
                        'orderby' => 'meta_value',
                        'order' => 'DESC',
                        'meta_key' => '_fg_data_evento'
                    ));
                    $today = date('Y-m-d');
                    ?>
                    <?php if ($eventi_dashboard->have_posts()): ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Evento', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Data', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Stato', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Partecipanti', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Luogo', 'friends-gestionale'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($eventi_dashboard->have_posts()): $eventi_dashboard->the_post(); ?>
                                    <?php
                                    $data_evento = get_post_meta(get_the_ID(), '_fg_data_evento', true);
                                    $luogo = get_post_meta(get_the_ID(), '_fg_luogo', true);
                                    $invitati = get_post_meta(get_the_ID(), '_fg_invitati', true);
                                    $num_invitati = is_array($invitati) ? count($invitati) : 0;
                                    $is_past = ($data_evento && $data_evento < $today);
                                    $label = $is_past ? __('Passato', 'friends-gestionale') : __('Futuro', 'friends-gestionale');
                                    $label_class = $is_past ? 'fg-badge fg-stato-completato' : 'fg-badge fg-stato-programmato';
                                    ?>
                                    <tr>
                                        <td><strong><a href="<?php echo get_edit_post_link(get_the_ID()); ?>"><?php the_title(); ?></a></strong></td>
                                        <td><?php echo $data_evento ? date_i18n(get_option('date_format'), strtotime($data_evento)) : '-'; ?></td>
                                        <td><span class="<?php echo $label_class; ?>"><?php echo $label; ?></span></td>
                                        <td><?php echo $num_invitati; ?></td>
                                        <td><?php echo esc_html($luogo ? $luogo : '-'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('Nessun evento registrato.', 'friends-gestionale'); ?></p>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
                
                <div class="fg-dashboard-box">
                    <h2><?php _e('Top Donatori', 'friends-gestionale'); ?></h2>
                    <?php
                    // Calculate top donors (10 instead of 5)
                    $soci_donations = array();
                    $all_soci = get_posts(array(
                        'post_type' => 'fg_socio',
                        'posts_per_page' => -1
                    ));
                    
                    foreach ($all_soci as $socio) {
                        $payments = get_posts(array(
                            'post_type' => 'fg_pagamento',
                            'posts_per_page' => -1,
                            'meta_query' => array(
                                array(
                                    'key' => '_fg_socio_id',
                                    'value' => $socio->ID
                                )
                            )
                        ));
                        
                        $total = 0;
                        foreach ($payments as $payment) {
                            $total += floatval(get_post_meta($payment->ID, '_fg_importo', true));
                        }
                        
                        if ($total > 0) {
                            $soci_donations[] = array(
                                'nome' => $socio->post_title,
                                'totale' => $total
                            );
                        }
                    }
                    
                    // Sort by total and get top 10
                    usort($soci_donations, function($a, $b) {
                        return $b['totale'] - $a['totale'];
                    });
                    $top_donors_dashboard = array_slice($soci_donations, 0, 10);
                    ?>
                    <?php if (!empty($top_donors_dashboard)): ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Posizione', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Nome', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Totale Donato', 'friends-gestionale'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $pos = 1; foreach ($top_donors_dashboard as $donor): ?>
                                    <tr>
                                        <td><strong><?php echo $pos++; ?>°</strong></td>
                                        <td><?php echo esc_html($donor['nome']); ?></td>
                                        <td><strong>€<?php echo number_format($donor['totale'], 2, ',', '.'); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('Nessuna donazione registrata.', 'friends-gestionale'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="fg-quick-actions">
                <h2><?php _e('Azioni Rapide', 'friends-gestionale'); ?></h2>
                <div class="fg-actions-grid">
                    <a href="<?php echo admin_url('post-new.php?post_type=fg_socio'); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Aggiungi Socio', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('post-new.php?post_type=fg_pagamento'); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Registra Pagamento', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('post-new.php?post_type=fg_evento'); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-calendar"></span>
                        <?php _e('Crea Evento', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('post-new.php?post_type=fg_raccolta'); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Nuova Raccolta Fondi', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=fg-payment-calendar'); ?>" class="button button-hero">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php _e('Calendario Pagamenti', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=fg-event-calendar'); ?>" class="button button-hero">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php _e('Calendario Eventi', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=fg-statistics'); ?>" class="button button-hero">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php _e('Visualizza Statistiche', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=fg-export'); ?>" class="button button-hero">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Esporta Dati', 'friends-gestionale'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render statistics page
     */
    public function render_statistics() {
        // Get payment data for last 12 months
        $months = array();
        $payments_data = array();
        
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $months[] = date_i18n('M Y', strtotime($month . '-01'));
            
            $start_date = $month . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $pagamenti = get_posts(array(
                'post_type' => 'fg_pagamento',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_fg_data_pagamento',
                        'value' => array($start_date, $end_date),
                        'compare' => 'BETWEEN',
                        'type' => 'DATE'
                    )
                )
            ));
            
            $total = 0;
            foreach ($pagamenti as $pagamento) {
                $total += floatval(get_post_meta($pagamento->ID, '_fg_importo', true));
            }
            $payments_data[] = $total;
        }
        
        // Get member status distribution
        $stati = array('attivo', 'sospeso', 'scaduto', 'inattivo');
        $members_by_status = array();
        
        foreach ($stati as $stato) {
            $query = new WP_Query(array(
                'post_type' => 'fg_socio',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_fg_stato',
                        'value' => $stato
                    )
                )
            ));
            $members_by_status[$stato] = $query->found_posts;
        }
        
        // Get donations by type
        $tipi_pagamento = array('quota', 'donazione', 'raccolta', 'evento', 'altro');
        $payments_by_type = array();
        $payments_by_type_totals = array();
        
        foreach ($tipi_pagamento as $tipo) {
            $pagamenti_tipo = get_posts(array(
                'post_type' => 'fg_pagamento',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_fg_tipo_pagamento',
                        'value' => $tipo
                    )
                )
            ));
            $payments_by_type[$tipo] = count($pagamenti_tipo);
            
            $total_tipo = 0;
            foreach ($pagamenti_tipo as $pag) {
                $total_tipo += floatval(get_post_meta($pag->ID, '_fg_importo', true));
            }
            $payments_by_type_totals[$tipo] = $total_tipo;
        }
        
        // Get monthly new members data
        $new_members_data = array();
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $start_date = $month . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $new_members = new WP_Query(array(
                'post_type' => 'fg_socio',
                'posts_per_page' => -1,
                'date_query' => array(
                    array(
                        'after' => $start_date,
                        'before' => $end_date,
                        'inclusive' => true,
                    )
                )
            ));
            $new_members_data[] = $new_members->found_posts;
        }
        
        // Get payment methods distribution
        $metodi_pagamento = array('contanti', 'bonifico', 'carta', 'paypal', 'altro');
        $payments_by_method = array();
        
        foreach ($metodi_pagamento as $metodo) {
            $pagamenti_metodo = get_posts(array(
                'post_type' => 'fg_pagamento',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_fg_metodo_pagamento',
                        'value' => $metodo
                    )
                )
            ));
            $payments_by_method[$metodo] = count($pagamenti_metodo);
        }
        
        // Get upcoming events
        $today = date('Y-m-d');
        $eventi_prossimi = new WP_Query(array(
            'post_type' => 'fg_evento',
            'posts_per_page' => 10,
            'meta_key' => '_fg_data_evento',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_fg_data_evento',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        ));
        
        // Get top donors
        $all_soci = get_posts(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1
        ));
        
        $soci_donations = array();
        foreach ($all_soci as $socio) {
            $payments = get_posts(array(
                'post_type' => 'fg_pagamento',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_fg_socio_id',
                        'value' => $socio->ID
                    )
                )
            ));
            
            $total = 0;
            foreach ($payments as $payment) {
                $total += floatval(get_post_meta($payment->ID, '_fg_importo', true));
            }
            
            if ($total > 0) {
                $soci_donations[] = array(
                    'nome' => $socio->post_title,
                    'totale' => $total
                );
            }
        }
        
        // Sort by total and get top 5
        usort($soci_donations, function($a, $b) {
            return $b['totale'] - $a['totale'];
        });
        $top_donors = array_slice($soci_donations, 0, 10);
        ?>
        <div class="wrap fg-statistics-wrap">
            <h1><?php _e('Statistiche', 'friends-gestionale'); ?></h1>
            
            <style>
                .fg-charts-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }
                .fg-chart-container {
                    background: #fff;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                }
                .fg-chart-container h2 {
                    margin: 0 0 15px 0;
                    font-size: 16px;
                    color: #23282d;
                }
                @media screen and (max-width: 1024px) {
                    .fg-charts-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
            
            <div class="fg-charts-grid">
                <div class="fg-chart-container">
                    <h2><?php _e('Andamento Pagamenti (Ultimi 12 Mesi)', 'friends-gestionale'); ?></h2>
                    <canvas id="fg-payments-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="fg-chart-container">
                    <h2><?php _e('Distribuzione Soci per Stato', 'friends-gestionale'); ?></h2>
                    <canvas id="fg-members-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="fg-chart-container">
                    <h2><?php _e('Donazioni per Tipo', 'friends-gestionale'); ?></h2>
                    <canvas id="fg-donations-type-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="fg-chart-container">
                    <h2><?php _e('Nuovi Soci (Ultimi 12 Mesi)', 'friends-gestionale'); ?></h2>
                    <canvas id="fg-new-members-chart" width="400" height="200"></canvas>
                </div>
                
                <div class="fg-chart-container">
                    <h2><?php _e('Distribuzione Metodi di Pagamento', 'friends-gestionale'); ?></h2>
                    <canvas id="fg-payment-methods-chart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <div class="fg-dashboard-grid" style="margin-top: 30px;">
                <div class="fg-dashboard-box">
                    <h2><?php _e('Eventi Prossimi', 'friends-gestionale'); ?></h2>
                    <?php if ($eventi_prossimi->have_posts()): ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Evento', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Data', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Partecipanti', 'friends-gestionale'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($eventi_prossimi->have_posts()): $eventi_prossimi->the_post(); ?>
                                    <?php
                                    $data_evento = get_post_meta(get_the_ID(), '_fg_data_evento', true);
                                    $invitati = get_post_meta(get_the_ID(), '_fg_invitati', true);
                                    $num_invitati = is_array($invitati) ? count($invitati) : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?php the_title(); ?></strong></td>
                                        <td><?php echo $data_evento ? date_i18n(get_option('date_format'), strtotime($data_evento)) : '-'; ?></td>
                                        <td><?php echo $num_invitati; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('Nessun evento in programma.', 'friends-gestionale'); ?></p>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
                
                <div class="fg-dashboard-box">
                    <h2><?php _e('Top Donatori', 'friends-gestionale'); ?></h2>
                    <?php if (!empty($top_donors)): ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Posizione', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Nome', 'friends-gestionale'); ?></th>
                                    <th><?php _e('Totale Donato', 'friends-gestionale'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $pos = 1; foreach ($top_donors as $donor): ?>
                                    <tr>
                                        <td><strong><?php echo $pos++; ?>°</strong></td>
                                        <td><?php echo esc_html($donor['nome']); ?></td>
                                        <td><strong>€<?php echo number_format($donor['totale'], 2, ',', '.'); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('Nessuna donazione registrata.', 'friends-gestionale'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Payments chart
                var paymentsCtx = document.getElementById('fg-payments-chart').getContext('2d');
                new Chart(paymentsCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($months); ?>,
                        datasets: [{
                            label: '<?php _e('Incassi (€)', 'friends-gestionale'); ?>',
                            data: <?php echo json_encode($payments_data); ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                
                // Members chart
                var membersCtx = document.getElementById('fg-members-chart').getContext('2d');
                new Chart(membersCtx, {
                    type: 'pie',
                    data: {
                        labels: [
                            '<?php _e('Attivi', 'friends-gestionale'); ?>',
                            '<?php _e('Sospesi', 'friends-gestionale'); ?>',
                            '<?php _e('Scaduti', 'friends-gestionale'); ?>',
                            '<?php _e('Inattivi', 'friends-gestionale'); ?>'
                        ],
                        datasets: [{
                            data: <?php echo json_encode(array_values($members_by_status)); ?>,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(201, 203, 207, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
                
                // Donations by type chart
                var donationsTypeCtx = document.getElementById('fg-donations-type-chart').getContext('2d');
                new Chart(donationsTypeCtx, {
                    type: 'pie',
                    data: {
                        labels: [
                            '<?php _e('Quota', 'friends-gestionale'); ?>',
                            '<?php _e('Donazione', 'friends-gestionale'); ?>',
                            '<?php _e('Raccolta Fondi', 'friends-gestionale'); ?>',
                            '<?php _e('Evento', 'friends-gestionale'); ?>',
                            '<?php _e('Altro', 'friends-gestionale'); ?>'
                        ],
                        datasets: [{
                            data: <?php echo json_encode(array_values($payments_by_type_totals)); ?>,
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
                
                // New members chart
                var newMembersCtx = document.getElementById('fg-new-members-chart').getContext('2d');
                new Chart(newMembersCtx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($months); ?>,
                        datasets: [{
                            label: '<?php _e('Nuovi Soci', 'friends-gestionale'); ?>',
                            data: <?php echo json_encode($new_members_data); ?>,
                            borderColor: 'rgb(153, 102, 255)',
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
                
                // Payment methods chart
                var paymentMethodsCtx = document.getElementById('fg-payment-methods-chart').getContext('2d');
                new Chart(paymentMethodsCtx, {
                    type: 'pie',
                    data: {
                        labels: ['<?php _e('Contanti', 'friends-gestionale'); ?>', '<?php _e('Bonifico Bancario', 'friends-gestionale'); ?>', '<?php _e('Carta di Credito', 'friends-gestionale'); ?>', '<?php _e('PayPal', 'friends-gestionale'); ?>', '<?php _e('Altro', 'friends-gestionale'); ?>'],
                        datasets: [{
                            data: <?php echo json_encode(array_values($payments_by_method)); ?>,
                            backgroundColor: [
                                'rgb(54, 162, 235)',
                                'rgb(75, 192, 192)',
                                'rgb(255, 99, 132)',
                                'rgb(255, 205, 86)',
                                'rgb(201, 203, 207)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right'
                            }
                        }
                    }
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('fg_settings_group', 'friends_gestionale_quota_annuale');
        register_setting('fg_settings_group', 'friends_gestionale_reminder_days');
        register_setting('fg_settings_group', 'friends_gestionale_email_notifications');
        register_setting('fg_settings_group', 'friends_gestionale_email_subject');
        register_setting('fg_settings_group', 'friends_gestionale_email_message');
    }
    
    /**
     * Render settings page
     */
    public function render_settings() {
        ?>
        <div class="wrap">
            <h1><?php _e('Impostazioni Friends Gestionale', 'friends-gestionale'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('fg_settings_group'); ?>
                <?php do_settings_sections('fg_settings_group'); ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Quota Annuale Predefinita (€)', 'friends-gestionale'); ?></th>
                        <td>
                            <input type="number" name="friends_gestionale_quota_annuale" value="<?php echo esc_attr(get_option('friends_gestionale_quota_annuale', 50)); ?>" step="0.01" min="0" />
                            <p class="description"><?php _e('Importo predefinito per la quota associativa annuale.', 'friends-gestionale'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php _e('Giorni Anticipo Reminder', 'friends-gestionale'); ?></th>
                        <td>
                            <input type="number" name="friends_gestionale_reminder_days" value="<?php echo esc_attr(get_option('friends_gestionale_reminder_days', 30)); ?>" min="1" />
                            <p class="description"><?php _e('Numero di giorni prima della scadenza per inviare il reminder.', 'friends-gestionale'); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php _e('Notifiche Email', 'friends-gestionale'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="friends_gestionale_email_notifications" value="1" <?php checked(get_option('friends_gestionale_email_notifications', true), 1); ?> />
                                <?php _e('Abilita l\'invio automatico di email per i reminder di scadenza.', 'friends-gestionale'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php _e('Oggetto Email Reminder', 'friends-gestionale'); ?></th>
                        <td>
                            <input type="text" name="friends_gestionale_email_subject" value="<?php echo esc_attr(get_option('friends_gestionale_email_subject', __('Reminder Scadenza Quota', 'friends-gestionale'))); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row"><?php _e('Messaggio Email Reminder', 'friends-gestionale'); ?></th>
                        <td>
                            <textarea name="friends_gestionale_email_message" rows="5" class="large-text"><?php echo esc_textarea(get_option('friends_gestionale_email_message', __('La tua quota associativa scadrà presto. Ti invitiamo a rinnovarla.', 'friends-gestionale'))); ?></textarea>
                            <p class="description"><?php _e('Usa {nome} per il nome del socio e {data_scadenza} per la data di scadenza.', 'friends-gestionale'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render payment calendar page
     */
    public function render_payment_calendar() {
        // Get current month and year from URL or use current date
        $current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
        $current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        
        // Calculate previous and next month
        $prev_month = $current_month == 1 ? 12 : $current_month - 1;
        $prev_year = $current_month == 1 ? $current_year - 1 : $current_year;
        $next_month = $current_month == 12 ? 1 : $current_month + 1;
        $next_year = $current_month == 12 ? $current_year + 1 : $current_year;
        
        // Calculate previous and next year
        $prev_year_same_month = $current_year - 1;
        $next_year_same_month = $current_year + 1;
        
        // Get month name
        $month_name = date_i18n('F Y', strtotime("$current_year-$current_month-01"));
        
        // Get first and last day of month
        $first_day = strtotime("$current_year-$current_month-01");
        $last_day = strtotime(date('Y-m-t', $first_day));
        
        // Get all payments for this month
        $payments = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_data_pagamento',
                    'value' => array(date('Y-m-01', $first_day), date('Y-m-t', $first_day)),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        ));
        
        // Get all soci with scadenza in this month (future payments)
        $soci_scadenza = get_posts(array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_data_scadenza',
                    'value' => array(date('Y-m-01', $first_day), date('Y-m-t', $first_day)),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        ));
        
        // Organize payments by day
        $payments_by_day = array();
        foreach ($payments as $payment) {
            $data_pagamento = get_post_meta($payment->ID, '_fg_data_pagamento', true);
            if ($data_pagamento) {
                $day = date('j', strtotime($data_pagamento));
                if (!isset($payments_by_day[$day])) {
                    $payments_by_day[$day] = array('paid' => array(), 'due' => array());
                }
                $payments_by_day[$day]['paid'][] = $payment;
            }
        }
        
        // Organize scadenze by day
        foreach ($soci_scadenza as $socio) {
            $data_scadenza = get_post_meta($socio->ID, '_fg_data_scadenza', true);
            if ($data_scadenza) {
                $day = date('j', strtotime($data_scadenza));
                if (!isset($payments_by_day[$day])) {
                    $payments_by_day[$day] = array('paid' => array(), 'due' => array());
                }
                $payments_by_day[$day]['due'][] = $socio;
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Calendario Pagamenti', 'friends-gestionale'); ?></h1>
            
            <div class="fg-calendar-navigation" style="margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <a href="?page=fg-payment-calendar&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> <?php _e('Mese Precedente', 'friends-gestionale'); ?>
                    </a>
                    <h2 style="margin: 0;"><?php echo esc_html($month_name); ?></h2>
                    <a href="?page=fg-payment-calendar&month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <?php _e('Mese Successivo', 'friends-gestionale'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
                <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <a href="?page=fg-payment-calendar&month=<?php echo $current_month; ?>&year=<?php echo $prev_year_same_month; ?>" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> <?php _e('Anno Precedente', 'friends-gestionale'); ?>
                    </a>
                    <a href="?page=fg-payment-calendar&month=<?php echo $current_month; ?>&year=<?php echo $next_year_same_month; ?>" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <?php _e('Anno Successivo', 'friends-gestionale'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
            
            <style>
                .fg-calendar {
                    width: 100%;
                    border-collapse: collapse;
                    background: #fff;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    overflow: visible;
                }
                .fg-calendar th {
                    background: #0073aa;
                    color: #fff;
                    padding: 10px;
                    text-align: center;
                    font-weight: bold;
                }
                .fg-calendar td {
                    border: 1px solid #ddd;
                    padding: 5px;
                    vertical-align: top;
                    height: 100px;
                    width: 14.28%;
                    overflow: visible;
                    position: relative;
                }
                .fg-calendar .day-number {
                    font-weight: bold;
                    font-size: 16px;
                    margin-bottom: 5px;
                }
                .fg-calendar .today {
                    background: #e7f5fe;
                }
                .fg-calendar .other-month {
                    background: #f5f5f5;
                    color: #999;
                }
                .fg-payment-item {
                    font-size: 11px;
                    padding: 3px 5px;
                    margin: 2px 0;
                    border-radius: 3px;
                    background: #d4edda;
                    border-left: 3px solid #28a745;
                    cursor: pointer;
                    transition: all 0.2s;
                    position: relative;
                }
                .fg-payment-item:hover {
                    background: #c3e6cb;
                    transform: translateX(2px);
                }
                .fg-payment-due {
                    font-size: 11px;
                    padding: 3px 5px;
                    margin: 2px 0;
                    border-radius: 3px;
                    background: #fff3cd;
                    border-left: 3px solid #ffc107;
                    cursor: pointer;
                    transition: all 0.2s;
                    position: relative;
                }
                .fg-payment-due:hover {
                    background: #ffe8a1;
                    transform: translateX(2px);
                }
                .fg-payment-overdue {
                    font-size: 11px;
                    padding: 3px 5px;
                    margin: 2px 0;
                    border-radius: 3px;
                    background: #f8d7da;
                    border-left: 3px solid #dc3545;
                    cursor: pointer;
                    transition: all 0.2s;
                    position: relative;
                }
                .fg-payment-overdue:hover {
                    background: #f5c6cb;
                    transform: translateX(2px);
                }
                
                /* Graphical popup tooltip */
                .fg-payment-tooltip {
                    display: none;
                    position: fixed;
                    z-index: 999999 !important;
                    background: #fff;
                    border: 2px solid #0073aa;
                    border-radius: 5px;
                    padding: 12px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                    min-width: 250px;
                    max-width: 350px;
                    pointer-events: none;
                    white-space: normal;
                }
                .fg-payment-item,
                .fg-payment-due,
                .fg-payment-overdue {
                    overflow: visible !important;
                }
                .fg-payment-tooltip h4 {
                    margin: 0 0 10px 0;
                    padding: 0 0 8px 0;
                    border-bottom: 1px solid #ddd;
                    color: #0073aa;
                    font-size: 14px;
                }
                .fg-payment-tooltip .tooltip-row {
                    display: flex;
                    margin: 5px 0;
                    font-size: 12px;
                }
                .fg-payment-tooltip .tooltip-label {
                    font-weight: bold;
                    width: 80px;
                    color: #666;
                }
                .fg-payment-tooltip .tooltip-value {
                    flex: 1;
                    color: #333;
                }
            </style>
            
            <table class="fg-calendar">
                <thead>
                    <tr>
                        <th><?php _e('Lunedì', 'friends-gestionale'); ?></th>
                        <th><?php _e('Martedì', 'friends-gestionale'); ?></th>
                        <th><?php _e('Mercoledì', 'friends-gestionale'); ?></th>
                        <th><?php _e('Giovedì', 'friends-gestionale'); ?></th>
                        <th><?php _e('Venerdì', 'friends-gestionale'); ?></th>
                        <th><?php _e('Sabato', 'friends-gestionale'); ?></th>
                        <th><?php _e('Domenica', 'friends-gestionale'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $days_in_month = date('t', $first_day);
                    $first_day_of_week = date('N', $first_day); // 1 (Monday) through 7 (Sunday)
                    $today = date('Y-m-d');
                    
                    $day_counter = 1;
                    $weeks = ceil(($days_in_month + $first_day_of_week - 1) / 7);
                    
                    for ($week = 0; $week < $weeks; $week++) {
                        echo '<tr>';
                        for ($day_of_week = 1; $day_of_week <= 7; $day_of_week++) {
                            if ($week == 0 && $day_of_week < $first_day_of_week) {
                                echo '<td class="other-month"></td>';
                            } elseif ($day_counter > $days_in_month) {
                                echo '<td class="other-month"></td>';
                            } else {
                                $current_date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day_counter);
                                $is_today = ($current_date == $today) ? 'today' : '';
                                
                                echo '<td class="' . $is_today . '">';
                                echo '<div class="day-number">' . $day_counter . '</div>';
                                
                                // Show payments
                                if (isset($payments_by_day[$day_counter])) {
                                    foreach ($payments_by_day[$day_counter]['paid'] as $payment) {
                                        $importo = get_post_meta($payment->ID, '_fg_importo', true);
                                        $socio_id = get_post_meta($payment->ID, '_fg_socio_id', true);
                                        $socio_nome = $socio_id ? get_the_title($socio_id) : 'N/A';
                                        $metodo = get_post_meta($payment->ID, '_fg_metodo_pagamento', true);
                                        $tipo = get_post_meta($payment->ID, '_fg_tipo_pagamento', true);
                                        $note = get_post_meta($payment->ID, '_fg_note', true);
                                        
                                        $edit_url = admin_url('post.php?post=' . $payment->ID . '&action=edit');
                                        
                                        // Create unique ID for tooltip
                                        $tooltip_id = 'tooltip-' . $payment->ID;
                                        
                                        echo '<div class="fg-payment-item" data-tooltip-id="' . $tooltip_id . '" onclick="window.open(\'' . esc_url($edit_url) . '\', \'_blank\')">';
                                        echo '✓ €' . number_format($importo, 2) . ' - ' . esc_html(substr($socio_nome, 0, 15));
                                        echo '</div>';
                                        
                                        // Store tooltip data for later rendering (outside table)
                                        if (!isset($tooltips_data)) {
                                            $tooltips_data = array();
                                        }
                                        $tooltips_data[$tooltip_id] = array(
                                            'title' => 'Pagamento Effettuato',
                                            'rows' => array(
                                                array('label' => 'Socio:', 'value' => esc_html($socio_nome)),
                                                array('label' => 'Importo:', 'value' => '€' . number_format($importo, 2)),
                                                array('label' => 'Tipo:', 'value' => esc_html(ucfirst($tipo))),
                                                array('label' => 'Metodo:', 'value' => esc_html(ucfirst($metodo))),
                                            )
                                        );
                                        if ($note) {
                                            $tooltips_data[$tooltip_id]['rows'][] = array('label' => 'Note:', 'value' => esc_html($note));
                                        }
                                    }
                                    
                                    // Show due payments
                                    foreach ($payments_by_day[$day_counter]['due'] as $socio) {
                                        // Get member category and its quota
                                        $terms = wp_get_post_terms($socio->ID, 'fg_categoria_socio');
                                        $quota = 0;
                                        if (!empty($terms) && !is_wp_error($terms)) {
                                            $categoria_quota = get_term_meta($terms[0]->term_id, 'fg_quota_associativa', true);
                                            $quota = $categoria_quota ? floatval($categoria_quota) : floatval(get_post_meta($socio->ID, '_fg_quota_annuale', true));
                                        } else {
                                            $quota = floatval(get_post_meta($socio->ID, '_fg_quota_annuale', true));
                                        }
                                        
                                        $is_overdue = ($current_date < $today);
                                        $class = $is_overdue ? 'fg-payment-overdue' : 'fg-payment-due';
                                        $data_scadenza = get_post_meta($socio->ID, '_fg_data_scadenza', true);
                                        
                                        // Build URL for creating new payment with pre-filled data
                                        $new_payment_url = admin_url('post-new.php?post_type=fg_pagamento&socio_id=' . $socio->ID);
                                        
                                        // Create unique ID for tooltip
                                        $tooltip_id = 'tooltip-due-' . $socio->ID . '-' . $day_counter;
                                        
                                        echo '<div class="' . $class . '" data-tooltip-id="' . $tooltip_id . '" onclick="window.open(\'' . esc_url($new_payment_url) . '\', \'_blank\')">';
                                        echo ($is_overdue ? '⚠' : '○') . ' €' . number_format($quota, 2) . ' - ' . esc_html(substr($socio->post_title, 0, 15));
                                        echo '</div>';
                                        
                                        // Store tooltip data for later rendering (outside table)
                                        if (!isset($tooltips_data)) {
                                            $tooltips_data = array();
                                        }
                                        $tooltips_data[$tooltip_id] = array(
                                            'title' => $is_overdue ? 'Pagamento Arretrato' : 'Pagamento in Scadenza',
                                            'rows' => array(
                                                array('label' => 'Socio:', 'value' => esc_html($socio->post_title)),
                                                array('label' => 'Quota:', 'value' => '€' . number_format($quota, 2)),
                                                array('label' => 'Scadenza:', 'value' => date_i18n(get_option('date_format'), strtotime($data_scadenza))),
                                                array('label' => 'Stato:', 'value' => $is_overdue ? 'Arretrato' : 'In scadenza'),
                                            )
                                        );
                                    }
                                }
                                
                                echo '</td>';
                                $day_counter++;
                            }
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
            
            <?php
            // Render all tooltips outside the table structure (appended to body via JS)
            if (isset($tooltips_data) && !empty($tooltips_data)) {
                echo '<div id="fg-tooltips-container" style="display: none;">';
                foreach ($tooltips_data as $tooltip_id => $tooltip_data) {
                    echo '<div id="' . esc_attr($tooltip_id) . '" class="fg-payment-tooltip">';
                    echo '<h4>' . esc_html($tooltip_data['title']) . '</h4>';
                    foreach ($tooltip_data['rows'] as $row) {
                        echo '<div class="tooltip-row">';
                        echo '<span class="tooltip-label">' . esc_html($row['label']) . '</span>';
                        echo '<span class="tooltip-value">' . $row['value'] . '</span>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Move tooltips to body for proper positioning
                $('#fg-tooltips-container .fg-payment-tooltip').appendTo('body');
                
                // Position tooltips dynamically on hover
                $('.fg-payment-item, .fg-payment-due, .fg-payment-overdue').on('mouseenter', function(e) {
                    var tooltipId = $(this).data('tooltip-id');
                    var $tooltip = $('#' + tooltipId);
                    
                    if ($tooltip.length) {
                        // Get element position
                        var rect = this.getBoundingClientRect();
                        
                        // Show tooltip hidden to measure its natural size
                        $tooltip.css({
                            'display': 'block',
                            'visibility': 'hidden',
                            'position': 'fixed',
                            'left': '0px',
                            'top': '0px'
                        });
                        
                        var tooltipWidth = $tooltip.outerWidth();
                        var tooltipHeight = $tooltip.outerHeight();
                        var windowWidth = $(window).width();
                        var windowHeight = $(window).height();
                        
                        var left, top;
                        
                        // Default: position to the right of the element
                        left = rect.right + 10;
                        top = rect.top;
                        
                        // Check if it goes off the right edge of screen
                        if (left + tooltipWidth > windowWidth - 10) {
                            // Try positioning to the left instead
                            left = rect.left - tooltipWidth - 10;
                        }
                        
                        // If still off screen on the left, position below
                        if (left < 10) {
                            left = Math.max(10, rect.left);
                            top = rect.bottom + 10;
                        }
                        
                        // Check if it goes off the bottom of screen
                        if (top + tooltipHeight > windowHeight - 10) {
                            // Try positioning above the element
                            top = rect.top - tooltipHeight - 10;
                        }
                        
                        // Final safety checks
                        if (top < 10) {
                            top = 10;
                        }
                        
                        if (left + tooltipWidth > windowWidth - 10) {
                            left = windowWidth - tooltipWidth - 10;
                        }
                        
                        if (left < 10) {
                            left = 10;
                        }
                        
                        // Apply final position and make visible
                        $tooltip.css({
                            'left': left + 'px',
                            'top': top + 'px',
                            'visibility': 'visible',
                            'display': 'block'
                        });
                    }
                }).on('mouseleave', function(e) {
                    // Hide tooltip when mouse leaves
                    var tooltipId = $(this).data('tooltip-id');
                    var $tooltip = $('#' + tooltipId);
                    
                    if ($tooltip.length) {
                        $tooltip.css({
                            'display': 'none',
                            'visibility': 'hidden'
                        });
                    }
                });
            });
            </script>
            
            <div class="fg-calendar-legend" style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd;">
                <h3><?php _e('Legenda', 'friends-gestionale'); ?></h3>
                <div style="display: flex; gap: 20px;">
                    <div><span style="display: inline-block; width: 20px; height: 10px; background: #28a745; margin-right: 5px;"></span> <?php _e('Pagamento Effettuato', 'friends-gestionale'); ?></div>
                    <div><span style="display: inline-block; width: 20px; height: 10px; background: #ffc107; margin-right: 5px;"></span> <?php _e('Pagamento in Scadenza', 'friends-gestionale'); ?></div>
                    <div><span style="display: inline-block; width: 20px; height: 10px; background: #dc3545; margin-right: 5px;"></span> <?php _e('Pagamento Arretrato', 'friends-gestionale'); ?></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render event calendar page
     */
    public function render_event_calendar() {
        // Get current month and year from URL or use current date
        $current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
        $current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        
        // Calculate previous and next month
        $prev_month = $current_month == 1 ? 12 : $current_month - 1;
        $prev_year = $current_month == 1 ? $current_year - 1 : $current_year;
        $next_month = $current_month == 12 ? 1 : $current_month + 1;
        $next_year = $current_month == 12 ? $current_year + 1 : $current_year;
        
        // Calculate previous and next year
        $prev_year_same_month = $current_year - 1;
        $next_year_same_month = $current_year + 1;
        
        // Get month name
        $month_name = date_i18n('F Y', strtotime("$current_year-$current_month-01"));
        
        // Get first and last day of month
        $first_day = strtotime("$current_year-$current_month-01");
        $last_day = strtotime(date('Y-m-t', $first_day));
        
        // Get all events for this month
        $eventi = get_posts(array(
            'post_type' => 'fg_evento',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_fg_data_evento',
                    'value' => array(date('Y-m-01', $first_day), date('Y-m-t', $first_day)),
                    'compare' => 'BETWEEN',
                    'type' => 'DATE'
                )
            )
        ));
        
        // Organize events by day
        $events_by_day = array();
        foreach ($eventi as $evento) {
            $data_evento = get_post_meta($evento->ID, '_fg_data_evento', true);
            if ($data_evento) {
                $day = date('j', strtotime($data_evento));
                if (!isset($events_by_day[$day])) {
                    $events_by_day[$day] = array();
                }
                $events_by_day[$day][] = $evento;
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Calendario Eventi', 'friends-gestionale'); ?></h1>
            
            <div class="fg-calendar-navigation" style="margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <a href="?page=fg-event-calendar&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> <?php _e('Mese Precedente', 'friends-gestionale'); ?>
                    </a>
                    <h2 style="margin: 0;"><?php echo esc_html($month_name); ?></h2>
                    <a href="?page=fg-event-calendar&month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="button button-primary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <?php _e('Mese Successivo', 'friends-gestionale'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
                <div style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                    <a href="?page=fg-event-calendar&month=<?php echo $current_month; ?>&year=<?php echo $prev_year_same_month; ?>" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <span class="dashicons dashicons-arrow-left-alt2"></span> <?php _e('Anno Precedente', 'friends-gestionale'); ?>
                    </a>
                    <a href="?page=fg-event-calendar&month=<?php echo $current_month; ?>&year=<?php echo $next_year_same_month; ?>" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 5px;">
                        <?php _e('Anno Successivo', 'friends-gestionale'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
            
            <style>
                .fg-calendar {
                    width: 100%;
                    border-collapse: collapse;
                    background: #fff;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    overflow: visible;
                }
                .fg-calendar th {
                    background: #0073aa;
                    color: #fff;
                    padding: 10px;
                    text-align: center;
                    font-weight: bold;
                }
                .fg-calendar td {
                    border: 1px solid #ddd;
                    padding: 5px;
                    vertical-align: top;
                    height: 100px;
                    width: 14.28%;
                    overflow: visible;
                    position: relative;
                }
                .fg-calendar .day-number {
                    font-weight: bold;
                    font-size: 16px;
                    margin-bottom: 5px;
                }
                .fg-calendar .today {
                    background: #e7f5fe;
                }
                .fg-calendar .other-month {
                    background: #f5f5f5;
                    color: #999;
                }
                .fg-event-item {
                    font-size: 11px;
                    padding: 3px 5px;
                    margin-bottom: 2px;
                    border-radius: 3px;
                    color: #fff;
                    cursor: pointer;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .fg-event-future {
                    background: #2271b1;
                }
                .fg-event-future:hover {
                    background: #135e96;
                }
                .fg-event-past {
                    background: #95a5a6;
                }
                .fg-event-past:hover {
                    background: #7f8c8d;
                }
                .fg-event-item a {
                    color: #fff;
                    text-decoration: none;
                }
                .fg-event-tooltip {
                    display: none;
                    position: fixed;
                    z-index: 999999 !important;
                    background: #fff;
                    border: 2px solid #0073aa;
                    border-radius: 5px;
                    padding: 12px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                    min-width: 250px;
                    max-width: 350px;
                    pointer-events: none;
                    white-space: normal;
                }
                .fg-event-item {
                    overflow: visible !important;
                }
                .fg-event-tooltip h4 {
                    margin: 0 0 10px 0;
                    padding: 0 0 8px 0;
                    border-bottom: 1px solid #ddd;
                    color: #0073aa;
                    font-size: 14px;
                }
                .fg-event-tooltip .tooltip-row {
                    display: flex;
                    margin: 5px 0;
                    font-size: 12px;
                }
                .fg-event-tooltip .tooltip-label {
                    font-weight: bold;
                    width: 80px;
                    color: #666;
                }
                .fg-event-tooltip .tooltip-value {
                    flex: 1;
                    color: #333;
                }
            </style>
            
            <table class="fg-calendar">
                <thead>
                    <tr>
                        <th><?php _e('Lun', 'friends-gestionale'); ?></th>
                        <th><?php _e('Mar', 'friends-gestionale'); ?></th>
                        <th><?php _e('Mer', 'friends-gestionale'); ?></th>
                        <th><?php _e('Gio', 'friends-gestionale'); ?></th>
                        <th><?php _e('Ven', 'friends-gestionale'); ?></th>
                        <th><?php _e('Sab', 'friends-gestionale'); ?></th>
                        <th><?php _e('Dom', 'friends-gestionale'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get first day of month (1 = Monday, 7 = Sunday)
                    $first_day_of_month = date('N', $first_day);
                    $days_in_month = date('t', $first_day);
                    $today = date('j');
                    $current_month_check = date('n');
                    $current_year_check = date('Y');
                    
                    $day_counter = 1;
                    $week_counter = 0;
                    
                    // Calculate starting position
                    $blank_days = $first_day_of_month - 1;
                    
                    while ($day_counter <= $days_in_month) {
                        echo '<tr>';
                        
                        for ($i = 0; $i < 7; $i++) {
                            if ($week_counter == 0 && $i < $blank_days) {
                                // Empty cells before first day
                                echo '<td class="other-month"></td>';
                            } elseif ($day_counter > $days_in_month) {
                                // Empty cells after last day
                                echo '<td class="other-month"></td>';
                            } else {
                                // Current month day
                                $is_today = ($day_counter == $today && $current_month == $current_month_check && $current_year == $current_year_check);
                                $class = $is_today ? 'today' : '';
                                
                                echo '<td class="' . $class . '">';
                                echo '<div class="day-number">' . $day_counter . '</div>';
                                
                                // Display events for this day
                                if (isset($events_by_day[$day_counter])) {
                                    // Initialize tooltips data array if needed
                                    if (!isset($event_tooltips_data)) {
                                        $event_tooltips_data = array();
                                    }
                                    
                                    foreach ($events_by_day[$day_counter] as $evento) {
                                        $titolo = get_post_meta($evento->ID, '_fg_titolo_evento', true);
                                        $ora = get_post_meta($evento->ID, '_fg_ora_evento', true);
                                        $luogo = get_post_meta($evento->ID, '_fg_luogo', true);
                                        $stato = get_post_meta($evento->ID, '_fg_stato_evento', true);
                                        $data_evento = get_post_meta($evento->ID, '_fg_data_evento', true);
                                        
                                        // Determine if event is past or future
                                        $today_date = date('Y-m-d');
                                        $is_past_event = ($data_evento && $data_evento < $today_date);
                                        $event_class = $is_past_event ? 'fg-event-item fg-event-past' : 'fg-event-item fg-event-future';
                                        
                                        $tooltip_id = 'event-tooltip-' . $evento->ID . '-' . $day_counter;
                                        $edit_link = get_edit_post_link($evento->ID);
                                        
                                        echo '<a href="' . esc_url($edit_link) . '" target="_blank" class="' . $event_class . '" data-tooltip-id="' . esc_attr($tooltip_id) . '" style="text-decoration: none; display: block;">';
                                        echo esc_html($titolo ? $titolo : get_the_title($evento->ID));
                                        echo '</a>';
                                        
                                        // Store tooltip data for later rendering (outside table)
                                        $tooltip_rows = array();
                                        if ($ora) {
                                            $tooltip_rows[] = array('label' => __('Ora:', 'friends-gestionale'), 'value' => esc_html($ora));
                                        }
                                        if ($luogo) {
                                            $tooltip_rows[] = array('label' => __('Luogo:', 'friends-gestionale'), 'value' => esc_html($luogo));
                                        }
                                        if ($stato) {
                                            $tooltip_rows[] = array('label' => __('Stato:', 'friends-gestionale'), 'value' => esc_html(ucfirst($stato)));
                                        }
                                        
                                        $event_tooltips_data[$tooltip_id] = array(
                                            'title' => $titolo ? $titolo : get_the_title($evento->ID),
                                            'rows' => $tooltip_rows
                                        );
                                    }
                                }
                                
                                echo '</td>';
                                $day_counter++;
                            }
                        }
                        
                        echo '</tr>';
                        $week_counter++;
                    }
                    ?>
                </tbody>
            </table>
            
            <?php
            // Render all event tooltips outside the table structure (appended to body via JS)
            if (isset($event_tooltips_data) && !empty($event_tooltips_data)) {
                echo '<div id="fg-event-tooltips-container" style="display: none;">';
                foreach ($event_tooltips_data as $tooltip_id => $tooltip_data) {
                    echo '<div id="' . esc_attr($tooltip_id) . '" class="fg-event-tooltip">';
                    echo '<h4>' . esc_html($tooltip_data['title']) . '</h4>';
                    foreach ($tooltip_data['rows'] as $row) {
                        echo '<div class="tooltip-row">';
                        if (!empty($row['label'])) {
                            echo '<span class="tooltip-label">' . $row['label'] . '</span>';
                        }
                        echo '<span class="tooltip-value">' . $row['value'] . '</span>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Move tooltips to body for proper positioning
                $('#fg-event-tooltips-container .fg-event-tooltip').appendTo('body');
                
                // Position tooltips dynamically on hover
                $('.fg-event-item').on('mouseenter', function(e) {
                    var tooltipId = $(this).data('tooltip-id');
                    var $tooltip = $('#' + tooltipId);
                    
                    if ($tooltip.length) {
                        // Get element position
                        var rect = this.getBoundingClientRect();
                        
                        // Show tooltip hidden to measure its natural size
                        $tooltip.css({
                            'display': 'block',
                            'visibility': 'hidden',
                            'position': 'fixed',
                            'left': '0px',
                            'top': '0px'
                        });
                        
                        var tooltipWidth = $tooltip.outerWidth();
                        var tooltipHeight = $tooltip.outerHeight();
                        var windowWidth = $(window).width();
                        var windowHeight = $(window).height();
                        
                        var left, top;
                        
                        // Default: position to the right of the element
                        left = rect.right + 10;
                        top = rect.top;
                        
                        // Check if it goes off the right edge of screen
                        if (left + tooltipWidth > windowWidth - 10) {
                            // Try positioning to the left instead
                            left = rect.left - tooltipWidth - 10;
                        }
                        
                        // If still off screen on the left, position below
                        if (left < 10) {
                            left = Math.max(10, rect.left);
                            top = rect.bottom + 10;
                        }
                        
                        // Check if it goes off the bottom of screen
                        if (top + tooltipHeight > windowHeight - 10) {
                            // Try positioning above the element
                            top = rect.top - tooltipHeight - 10;
                        }
                        
                        // Final safety checks
                        if (top < 10) {
                            top = 10;
                        }
                        
                        if (left + tooltipWidth > windowWidth - 10) {
                            left = windowWidth - tooltipWidth - 10;
                        }
                        
                        if (left < 10) {
                            left = 10;
                        }
                        
                        // Apply final position and make visible
                        $tooltip.css({
                            'left': left + 'px',
                            'top': top + 'px',
                            'visibility': 'visible',
                            'display': 'block'
                        });
                    }
                }).on('mouseleave', function(e) {
                    // Hide tooltip when mouse leaves
                    var tooltipId = $(this).data('tooltip-id');
                    var $tooltip = $('#' + tooltipId);
                    
                    if ($tooltip.length) {
                        $tooltip.css({
                            'display': 'none',
                            'visibility': 'hidden'
                        });
                    }
                });
            });
            </script>
            
            <div class="fg-calendar-legend" style="margin-top: 20px; padding: 15px; background: #fff; border: 1px solid #ddd;">
                <h3><?php _e('Legenda', 'friends-gestionale'); ?></h3>
                <div style="display: flex; gap: 20px;">
                    <div><span style="display: inline-block; width: 20px; height: 10px; background: #2271b1; margin-right: 5px;"></span> <?php _e('Evento Futuro', 'friends-gestionale'); ?></div>
                    <div><span style="display: inline-block; width: 20px; height: 10px; background: #95a5a6; margin-right: 5px;"></span> <?php _e('Evento Passato', 'friends-gestionale'); ?></div>
                </div>
                <div style="margin-top: 10px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=fg_evento'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-plus"></span> <?php _e('Aggiungi Nuovo Evento', 'friends-gestionale'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize
new Friends_Gestionale_Admin_Dashboard();
