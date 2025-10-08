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
            'manage_options',
            'friends-gestionale',
            array($this, 'render_dashboard'),
            'dashicons-heart',
            30
        );
        
        add_submenu_page(
            'friends-gestionale',
            __('Dashboard', 'friends-gestionale'),
            __('Dashboard', 'friends-gestionale'),
            'manage_options',
            'friends-gestionale',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'friends-gestionale',
            __('Statistiche', 'friends-gestionale'),
            __('Statistiche', 'friends-gestionale'),
            'manage_options',
            'fg-statistics',
            array($this, 'render_statistics')
        );
        
        add_submenu_page(
            'friends-gestionale',
            __('Impostazioni', 'friends-gestionale'),
            __('Impostazioni', 'friends-gestionale'),
            'manage_options',
            'fg-settings',
            array($this, 'render_settings')
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
}

// Initialize
new Friends_Gestionale_Admin_Dashboard();
