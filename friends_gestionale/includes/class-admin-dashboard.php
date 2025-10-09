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
            __('Friends Gestionale', 'friends-gestionale'),
            __('Friends Gestionale', 'friends-gestionale'),
            'edit_posts',
            'friends-gestionale',
            array($this, 'render_dashboard'),
            'dashicons-heart',
            30
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
    }
    
    /**
     * Render dashboard page
     */
    public function render_dashboard() {
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
        foreach ($pagamenti as $pagamento) {
            $totale_incassi += floatval(get_post_meta($pagamento->ID, '_fg_importo', true));
        }
        
        // Get recent payments
        $pagamenti_recenti = get_posts(array(
            'post_type' => 'fg_pagamento',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
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
        ?>
        <div class="wrap fg-dashboard-wrap">
            <h1><?php _e('Dashboard Friends Gestionale', 'friends-gestionale'); ?></h1>
            
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
                    <a href="<?php echo admin_url('post-new.php?post_type=fg_raccolta'); ?>" class="button button-primary button-hero">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Nuova Raccolta Fondi', 'friends-gestionale'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=fg-statistics'); ?>" class="button button-hero">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php _e('Visualizza Statistiche', 'friends-gestionale'); ?>
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
        ?>
        <div class="wrap fg-statistics-wrap">
            <h1><?php _e('Statistiche', 'friends-gestionale'); ?></h1>
            
            <div class="fg-chart-container">
                <h2><?php _e('Andamento Pagamenti (Ultimi 12 Mesi)', 'friends-gestionale'); ?></h2>
                <canvas id="fg-payments-chart" width="400" height="150"></canvas>
            </div>
            
            <div class="fg-chart-container">
                <h2><?php _e('Distribuzione Soci per Stato', 'friends-gestionale'); ?></h2>
                <canvas id="fg-members-chart" width="400" height="150"></canvas>
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
                    position: absolute;
                    z-index: 99999;
                    background: #fff;
                    border: 2px solid #0073aa;
                    border-radius: 5px;
                    padding: 12px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                    min-width: 250px;
                    left: 100%;
                    top: 0;
                    margin-left: 10px;
                }
                .fg-payment-item:hover .fg-payment-tooltip,
                .fg-payment-due:hover .fg-payment-tooltip,
                .fg-payment-overdue:hover .fg-payment-tooltip {
                    display: block;
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
                                        
                                        echo '<div class="fg-payment-item" onclick="window.open(\'' . esc_url($edit_url) . '\', \'_blank\')">';
                                        echo '✓ €' . number_format($importo, 2) . ' - ' . esc_html(substr($socio_nome, 0, 15));
                                        
                                        // Graphical popup tooltip
                                        echo '<div class="fg-payment-tooltip">';
                                        echo '<h4>Pagamento Effettuato</h4>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Socio:</span><span class="tooltip-value">' . esc_html($socio_nome) . '</span></div>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Importo:</span><span class="tooltip-value">€' . number_format($importo, 2) . '</span></div>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Tipo:</span><span class="tooltip-value">' . esc_html(ucfirst($tipo)) . '</span></div>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Metodo:</span><span class="tooltip-value">' . esc_html(ucfirst($metodo)) . '</span></div>';
                                        if ($note) {
                                            echo '<div class="tooltip-row"><span class="tooltip-label">Note:</span><span class="tooltip-value">' . esc_html($note) . '</span></div>';
                                        }
                                        echo '</div>';
                                        
                                        echo '</div>';
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
                                        
                                        echo '<div class="' . $class . '" onclick="window.open(\'' . esc_url($new_payment_url) . '\', \'_blank\')">';
                                        echo ($is_overdue ? '⚠' : '○') . ' €' . number_format($quota, 2) . ' - ' . esc_html(substr($socio->post_title, 0, 15));
                                        
                                        // Graphical popup tooltip
                                        echo '<div class="fg-payment-tooltip">';
                                        echo '<h4>' . ($is_overdue ? 'Pagamento Arretrato' : 'Pagamento in Scadenza') . '</h4>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Socio:</span><span class="tooltip-value">' . esc_html($socio->post_title) . '</span></div>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Quota:</span><span class="tooltip-value">€' . number_format($quota, 2) . '</span></div>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Scadenza:</span><span class="tooltip-value">' . date_i18n(get_option('date_format'), strtotime($data_scadenza)) . '</span></div>';
                                        echo '<div class="tooltip-row"><span class="tooltip-label">Stato:</span><span class="tooltip-value">' . ($is_overdue ? 'Arretrato' : 'In scadenza') . '</span></div>';
                                        echo '</div>';
                                        
                                        echo '</div>';
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
}

// Initialize
new Friends_Gestionale_Admin_Dashboard();
