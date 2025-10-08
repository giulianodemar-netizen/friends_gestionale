<?php
/**
 * Reminders System
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Reminders {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Schedule daily check
        add_action('wp', array($this, 'schedule_daily_check'));
        add_action('fg_daily_reminder_check', array($this, 'check_expiring_memberships'));
        
        // Admin notices
        add_action('admin_notices', array($this, 'show_expiring_members_notice'));
        
        // Manual reminder action
        add_action('admin_post_fg_send_manual_reminder', array($this, 'send_manual_reminder'));
    }
    
    /**
     * Schedule daily check
     */
    public function schedule_daily_check() {
        if (!wp_next_scheduled('fg_daily_reminder_check')) {
            wp_schedule_event(time(), 'daily', 'fg_daily_reminder_check');
        }
    }
    
    /**
     * Check for expiring memberships
     */
    public function check_expiring_memberships() {
        $reminder_days = get_option('friends_gestionale_reminder_days', 30);
        $email_enabled = get_option('friends_gestionale_email_notifications', true);
        
        // Calculate the date range for upcoming expirations
        $today = date('Y-m-d');
        $reminder_date = date('Y-m-d', strtotime("+{$reminder_days} days"));
        
        // Query members with upcoming expiration
        $args = array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_fg_data_scadenza',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_fg_data_scadenza',
                    'value' => $reminder_date,
                    'compare' => '<=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_fg_stato',
                    'value' => 'attivo',
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts() && $email_enabled) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Check if reminder was already sent (to avoid spam)
                $last_reminder = get_post_meta($post_id, '_fg_last_reminder_sent', true);
                $reminder_sent_date = $last_reminder ? date('Y-m-d', strtotime($last_reminder)) : '';
                
                // Only send reminder once per membership period
                if ($reminder_sent_date !== $today) {
                    $this->send_reminder_email($post_id);
                    update_post_meta($post_id, '_fg_last_reminder_sent', current_time('mysql'));
                }
            }
        }
        
        wp_reset_postdata();
        
        // Update expired memberships
        $this->update_expired_memberships();
    }
    
    /**
     * Update expired memberships
     */
    private function update_expired_memberships() {
        $today = date('Y-m-d');
        
        $args = array(
            'post_type' => 'fg_socio',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_fg_data_scadenza',
                    'value' => $today,
                    'compare' => '<',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_fg_stato',
                    'value' => 'attivo',
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                update_post_meta(get_the_ID(), '_fg_stato', 'scaduto');
            }
        }
        
        wp_reset_postdata();
    }
    
    /**
     * Send reminder email to member
     */
    private function send_reminder_email($post_id) {
        $email = get_post_meta($post_id, '_fg_email', true);
        
        if (empty($email) || !is_email($email)) {
            return false;
        }
        
        $nome = get_the_title($post_id);
        $data_scadenza = get_post_meta($post_id, '_fg_data_scadenza', true);
        $quota_annuale = get_post_meta($post_id, '_fg_quota_annuale', true);
        
        // Get email settings
        $subject = get_option('friends_gestionale_email_subject', __('Reminder Scadenza Quota', 'friends-gestionale'));
        $message = get_option('friends_gestionale_email_message', __('La tua quota associativa scadrà presto. Ti invitiamo a rinnovarla.', 'friends-gestionale'));
        
        // Replace placeholders
        $subject = str_replace('{nome}', $nome, $subject);
        $message = str_replace('{nome}', $nome, $message);
        $message = str_replace('{data_scadenza}', date_i18n(get_option('date_format'), strtotime($data_scadenza)), $message);
        $message = str_replace('{quota}', number_format($quota_annuale, 2) . '€', $message);
        
        // Build email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $email_body = '<html><body>';
        $email_body .= '<h2>' . esc_html($subject) . '</h2>';
        $email_body .= '<p>' . __('Caro/a', 'friends-gestionale') . ' <strong>' . esc_html($nome) . '</strong>,</p>';
        $email_body .= '<p>' . nl2br(esc_html($message)) . '</p>';
        $email_body .= '<table style="margin: 20px 0; border-collapse: collapse;">';
        $email_body .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Data Scadenza:', 'friends-gestionale') . '</strong></td>';
        $email_body .= '<td style="padding: 10px; border: 1px solid #ddd;">' . date_i18n(get_option('date_format'), strtotime($data_scadenza)) . '</td></tr>';
        if ($quota_annuale) {
            $email_body .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Quota Annuale:', 'friends-gestionale') . '</strong></td>';
            $email_body .= '<td style="padding: 10px; border: 1px solid #ddd;">€' . number_format($quota_annuale, 2, ',', '.') . '</td></tr>';
        }
        $email_body .= '</table>';
        $email_body .= '<p>' . __('Per ulteriori informazioni, contattaci.', 'friends-gestionale') . '</p>';
        $email_body .= '<p>' . __('Cordiali saluti,', 'friends-gestionale') . '<br><strong>Friends of Naples</strong></p>';
        $email_body .= '</body></html>';
        
        return wp_mail($email, $subject, $email_body, $headers);
    }
    
    /**
     * Show admin notice for expiring members
     */
    public function show_expiring_members_notice() {
        $screen = get_current_screen();
        
        if ($screen && ($screen->id === 'dashboard' || strpos($screen->id, 'fg_socio') !== false)) {
            $reminder_days = get_option('friends_gestionale_reminder_days', 30);
            $today = date('Y-m-d');
            $reminder_date = date('Y-m-d', strtotime("+{$reminder_days} days"));
            
            // Count expiring members
            $expiring = new WP_Query(array(
                'post_type' => 'fg_socio',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_fg_data_scadenza',
                        'value' => $today,
                        'compare' => '>=',
                        'type' => 'DATE'
                    ),
                    array(
                        'key' => '_fg_data_scadenza',
                        'value' => $reminder_date,
                        'compare' => '<=',
                        'type' => 'DATE'
                    ),
                    array(
                        'key' => '_fg_stato',
                        'value' => 'attivo'
                    )
                )
            ));
            
            // Count expired members
            $expired = new WP_Query(array(
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
            
            if ($expiring->found_posts > 0) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php _e('Attenzione:', 'friends-gestionale'); ?></strong>
                        <?php printf(
                            _n(
                                'C\'è %d socio con quota in scadenza nei prossimi %d giorni.',
                                'Ci sono %d soci con quota in scadenza nei prossimi %d giorni.',
                                $expiring->found_posts,
                                'friends-gestionale'
                            ),
                            $expiring->found_posts,
                            $reminder_days
                        ); ?>
                        <a href="<?php echo admin_url('edit.php?post_type=fg_socio'); ?>"><?php _e('Visualizza elenco', 'friends-gestionale'); ?></a>
                    </p>
                </div>
                <?php
            }
            
            if ($expired->found_posts > 0) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php _e('Attenzione:', 'friends-gestionale'); ?></strong>
                        <?php printf(
                            _n(
                                'C\'è %d socio con quota scaduta.',
                                'Ci sono %d soci con quota scaduta.',
                                $expired->found_posts,
                                'friends-gestionale'
                            ),
                            $expired->found_posts
                        ); ?>
                        <a href="<?php echo admin_url('edit.php?post_type=fg_socio'); ?>"><?php _e('Visualizza elenco', 'friends-gestionale'); ?></a>
                    </p>
                </div>
                <?php
            }
        }
    }
    
    /**
     * Send manual reminder
     */
    public function send_manual_reminder() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per eseguire questa azione.', 'friends-gestionale'));
        }
        
        check_admin_referer('fg_send_reminder');
        
        $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
        
        if ($post_id && get_post_type($post_id) === 'fg_socio') {
            $sent = $this->send_reminder_email($post_id);
            
            if ($sent) {
                update_post_meta($post_id, '_fg_last_reminder_sent', current_time('mysql'));
                wp_redirect(add_query_arg(array('message' => 'reminder_sent'), get_edit_post_link($post_id, 'redirect')));
            } else {
                wp_redirect(add_query_arg(array('message' => 'reminder_error'), get_edit_post_link($post_id, 'redirect')));
            }
        } else {
            wp_redirect(admin_url('edit.php?post_type=fg_socio'));
        }
        
        exit;
    }
}

// Initialize
new Friends_Gestionale_Reminders();
