<?php
/**
 * Email Notifications
 *
 * @package Friends_Gestionale
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Friends_Gestionale_Email {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add action for sending welcome email on new member
        add_action('save_post_fg_socio', array($this, 'send_welcome_email'), 10, 3);
        
        // Add action for payment confirmation
        add_action('save_post_fg_pagamento', array($this, 'send_payment_confirmation'), 10, 3);
    }
    
    /**
     * Send welcome email to new member
     */
    public function send_welcome_email($post_id, $post, $update) {
        // Only send on new posts, not updates
        if ($update || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Check if already sent
        if (get_post_meta($post_id, '_fg_welcome_email_sent', true)) {
            return;
        }
        
        $email = get_post_meta($post_id, '_fg_email', true);
        
        if (empty($email) || !is_email($email)) {
            return;
        }
        
        $nome = $post->post_title;
        $data_iscrizione = get_post_meta($post_id, '_fg_data_iscrizione', true);
        $quota_annuale = get_post_meta($post_id, '_fg_quota_annuale', true);
        
        $subject = sprintf(__('Benvenuto/a in Friends of Naples - %s', 'friends-gestionale'), $nome);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $message = '<html><body>';
        $message .= '<h2>' . __('Benvenuto/a in Friends of Naples!', 'friends-gestionale') . '</h2>';
        $message .= '<p>' . sprintf(__('Caro/a <strong>%s</strong>,', 'friends-gestionale'), esc_html($nome)) . '</p>';
        $message .= '<p>' . __('Siamo lieti di darti il benvenuto come nuovo socio della nostra associazione!', 'friends-gestionale') . '</p>';
        $message .= '<table style="margin: 20px 0; border-collapse: collapse;">';
        
        if ($data_iscrizione) {
            $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Data Iscrizione:', 'friends-gestionale') . '</strong></td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . date_i18n(get_option('date_format'), strtotime($data_iscrizione)) . '</td></tr>';
        }
        
        if ($quota_annuale) {
            $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Quota Annuale:', 'friends-gestionale') . '</strong></td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">€' . number_format($quota_annuale, 2, ',', '.') . '</td></tr>';
        }
        
        $message .= '</table>';
        $message .= '<p>' . __('Grazie per il tuo supporto e per far parte della nostra comunità!', 'friends-gestionale') . '</p>';
        $message .= '<p>' . __('Cordiali saluti,', 'friends-gestionale') . '<br><strong>Friends of Naples</strong></p>';
        $message .= '</body></html>';
        
        $sent = wp_mail($email, $subject, $message, $headers);
        
        if ($sent) {
            update_post_meta($post_id, '_fg_welcome_email_sent', true);
        }
    }
    
    /**
     * Send payment confirmation email
     */
    public function send_payment_confirmation($post_id, $post, $update) {
        // Only send on new posts, not updates
        if ($update || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Check if already sent
        if (get_post_meta($post_id, '_fg_payment_email_sent', true)) {
            return;
        }
        
        $socio_id = get_post_meta($post_id, '_fg_socio_id', true);
        
        if (!$socio_id) {
            return;
        }
        
        $email = get_post_meta($socio_id, '_fg_email', true);
        
        if (empty($email) || !is_email($email)) {
            return;
        }
        
        $nome = get_the_title($socio_id);
        $importo = get_post_meta($post_id, '_fg_importo', true);
        $data_pagamento = get_post_meta($post_id, '_fg_data_pagamento', true);
        $metodo_pagamento = get_post_meta($post_id, '_fg_metodo_pagamento', true);
        $tipo_pagamento = get_post_meta($post_id, '_fg_tipo_pagamento', true);
        
        $subject = __('Conferma Ricezione Pagamento - Friends of Naples', 'friends-gestionale');
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $message = '<html><body>';
        $message .= '<h2>' . __('Conferma Ricezione Pagamento', 'friends-gestionale') . '</h2>';
        $message .= '<p>' . sprintf(__('Caro/a <strong>%s</strong>,', 'friends-gestionale'), esc_html($nome)) . '</p>';
        $message .= '<p>' . __('Ti confermiamo la ricezione del tuo pagamento con i seguenti dettagli:', 'friends-gestionale') . '</p>';
        $message .= '<table style="margin: 20px 0; border-collapse: collapse;">';
        $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Importo:', 'friends-gestionale') . '</strong></td>';
        $message .= '<td style="padding: 10px; border: 1px solid #ddd;">€' . number_format($importo, 2, ',', '.') . '</td></tr>';
        
        if ($data_pagamento) {
            $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Data:', 'friends-gestionale') . '</strong></td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . date_i18n(get_option('date_format'), strtotime($data_pagamento)) . '</td></tr>';
        }
        
        if ($metodo_pagamento) {
            $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Metodo:', 'friends-gestionale') . '</strong></td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html(ucfirst($metodo_pagamento)) . '</td></tr>';
        }
        
        if ($tipo_pagamento) {
            $message .= '<tr><td style="padding: 10px; border: 1px solid #ddd;"><strong>' . __('Tipo:', 'friends-gestionale') . '</strong></td>';
            $message .= '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html(ucfirst($tipo_pagamento)) . '</td></tr>';
        }
        
        $message .= '</table>';
        $message .= '<p>' . __('Grazie per il tuo contributo e per il tuo supporto continuo!', 'friends-gestionale') . '</p>';
        $message .= '<p>' . __('Cordiali saluti,', 'friends-gestionale') . '<br><strong>Friends of Naples</strong></p>';
        $message .= '</body></html>';
        
        $sent = wp_mail($email, $subject, $message, $headers);
        
        if ($sent) {
            update_post_meta($post_id, '_fg_payment_email_sent', true);
        }
    }
}

// Initialize
new Friends_Gestionale_Email();
