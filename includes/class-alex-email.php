<?php
/**
 * Email handling class for Alex Travel Consultant
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlexEmail {
    
    private static $instance = null;
    private $admin_email = 'david.simone2006@gmail.com';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Save lead to database
     */
    public function save_lead($lead_data) {
        return AlexDatabase::insert_lead($lead_data);
    }
    
    /**
     * Send email to admin with lead details
     */
    public function send_lead_email($lead_data, $lead_id) {
        $admin_email = get_option('alex_admin_email', $this->admin_email);
        $consultant_name = get_option('alex_consultant_name', 'Alex');
        
        $subject = sprintf(
            __('[Alex Travel Consultant] New Travel Lead: %s - %s', 'alex-travel-consultant'),
            $lead_data['name'],
            $lead_data['destination']
        );
        
        $message = $this->get_admin_email_template($lead_data, $lead_id, $consultant_name);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        );
        
        wp_mail($admin_email, $subject, $message, $headers);
    }
    
    /**
     * Send confirmation email to user
     */
    public function send_user_confirmation($lead_data) {
        $subject = sprintf(
            __('Your Travel Inquiry - %s', 'alex-travel-consultant'),
            get_bloginfo('name')
        );
        
        $message = $this->get_user_email_template($lead_data);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
        );
        
        wp_mail($lead_data['email'], $subject, $message, $headers);
    }
    
    /**
     * Admin email template
     */
    private function get_admin_email_template($lead_data, $lead_id, $consultant_name) {
        $consultant_title = get_option('alex_consultant_title', 'Senior Travel Advisor');
        
        $html = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto;">';
        
        // Header
        $html .= '<div style="background: linear-gradient(135deg, #d4af37 0%, #1a1a1a 100%); padding: 30px; color: #fff; border-radius: 8px 8px 0 0;">';
        $html .= '<h2 style="margin: 0; font-size: 24px;">🎯 New Travel Lead Received</h2>';
        $html .= '<p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">From ' . esc_html($consultant_name) . ' - ' . esc_html($consultant_title) . '</p>';
        $html .= '</div>';
        
        // Content
        $html .= '<div style="padding: 30px; background: #f9f9f9; border: 1px solid #e0e0e0;">';
        
        $html .= '<h3 style="color: #d4af37; margin-top: 0;">Lead Details</h3>';
        
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold; width: 40%;">Name:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['name']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold;">Email:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['email']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold;">Phone:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['phone']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold;">Destination:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['destination']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold;">Travel Date:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['travel_date']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold;">Budget:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['budget']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold;">Service Type:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['service_type']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr style="border-bottom: 1px solid #ddd;">';
        $html .= '<td style="padding: 12px; font-weight: bold;">Travelers:</td>';
        $html .= '<td style="padding: 12px;">' . esc_html($lead_data['travelers']) . '</td>';
        $html .= '</tr>';
        
        $html .= '<tr>';
        $html .= '<td style="padding: 12px; font-weight: bold; vertical-align: top;">Conversation:</td>';
        $html .= '<td style="padding: 12px;">' . wp_kses_post(nl2br($lead_data['conversation_summary'])) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        $html .= '<h3 style="color: #d4af37; margin-top: 20px;">Next Steps</h3>';
        $html .= '<p style="line-height: 1.6;">';
        $html .= '✓ Review the lead details above<br>';
        $html .= '✓ Contact the customer with personalized travel offers<br>';
        $html .= '✓ Provide best deals for their budget and preferences<br>';
        $html .= '✓ Update lead status in your dashboard<br>';
        $html .= '</p>';
        
        $html .= '<p style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">';
        $html .= '<strong>Lead ID:</strong> #' . esc_html($lead_id) . '<br>';
        $html .= '<strong>Received:</strong> ' . current_time('F j, Y \a\t g:i a') . '
';
        $html .= '</p>';
        
        $html .= '</div>';
        
        // Footer
        $html .= '<div style="background: #1a1a1a; color: #d4af37; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px;">';
        $html .= '<p style="margin: 0;">' . get_bloginfo('name') . ' - ' . get_bloginfo('description') . '</p>';
        $html .= '</div>';
        
        $html .= '</div></body></html>';
        
        return $html;
    }
    
    /**
     * User confirmation email template
     */
    private function get_user_email_template($lead_data) {
        $consultant_name = get_option('alex_consultant_name', 'Alex');
        $consultant_title = get_option('alex_consultant_title', 'Senior Travel Advisor');
        
        $html = '<html><body style="font-family: Arial, sans-serif; color: #333;">';
        $html .= '<div style="max-width: 600px; margin: 0 auto;">';
        
        // Header
        $html .= '<div style="background: linear-gradient(135deg, #d4af37 0%, #1a1a1a 100%); padding: 30px; color: #fff; border-radius: 8px 8px 0 0;">';
        $html .= '<h2 style="margin: 0; font-size: 24px;">✈️ Thank You for Your Inquiry!</h2>';
        $html .= '<p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">We received your travel request and we\'re excited to help!</p>';
        $html .= '</div>';
        
        // Content
        $html .= '<div style="padding: 30px; background: #f9f9f9; border: 1px solid #e0e0e0;">';
        
        $html .= '<p>Hi <strong>' . esc_html($lead_data['name']) . '</strong>,</p>';
        
        $html .= '<p style="line-height: 1.6;">';
        $html .= 'Thank you for reaching out to us through ' . esc_html($consultant_name) . ', our AI Travel Consultant! 🌍<br><br>';
        $html .= 'We have received your travel inquiry and our team of expert travel advisors will review your preferences right away.<br><br>';
        $html .= '<strong>Your Travel Details:</strong><br>';
        $html .= '📍 Destination: ' . esc_html($lead_data['destination']) . '<br>';
        $html .= '📅 Travel Date: ' . esc_html($lead_data['travel_date']) . '<br>';
        $html .= '💰 Budget: ' . esc_html($lead_data['budget']) . '<br>';
        $html .= '👥 Travelers: ' . esc_html($lead_data['travelers']) . '<br>';
        $html .= '🎫 Service Type: ' . esc_html($lead_data['service_type']) . '<br>';
        $html .= '</p>';
        
        $html .= '<div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4caf50;">';
        $html .= '<h3 style="margin-top: 0; color: #2e7d32;">🎯 What Happens Next?</h3>';
        $html .= '<ol style="line-height: 1.8;">';
        $html .= '<li>Our expert travel advisors will review your request</li>';
        $html .= '<li>We\'ll search for the best deals across all our partners</li>';
        $html .= '<li>You\'ll receive personalized offers within <strong>24 hours</strong></li>';
        $html .= '<li>We\'ll provide you with detailed itineraries and pricing</li>';
        $html .= '<li>Book with confidence - we handle everything for you!</li>';
        $html .= '</ol>';
        $html .= '</div>';
        
        $html .= '<p style="line-height: 1.6;">';
        $html .= 'We\'re committed to finding you the <strong>best travel value</strong> without compromising on quality. Our team has access to exclusive deals and unpublished rates that you won\'t find online!<br><br>';
        $html .= 'If you have any additional questions or preferences in the meantime, feel free to reach out to us directly.<br><br>';
        $html .= '<strong>Questions?</strong><br>';
        $html .= 'Email: ' . esc_html(get_option('alex_admin_email')) . '<br>';
        $html .= 'Phone: ' . esc_html(get_option('admin_email')) . '
';
        $html .= '</p>';
        
        $html .= '<p style="margin-top: 30px;">';
        $html .= 'Best regards,<br><br>';
        $html .= '<strong>' . esc_html($consultant_name) . '</strong><br>';
        $html .= esc_html($consultant_title) . '<br>';
        $html .= '<em>' . get_bloginfo('name') . '</em>
';
        $html .= '</p>';
        
        $html .= '</div>';
        
        // Footer
        $html .= '<div style="background: #1a1a1a; color: #d4af37; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px;">';
        $html .= '<p style="margin: 0;">' . get_bloginfo('name') . ' - ' . get_bloginfo('description') . '</p>';
        $html .= '<p style="margin: 10px 0 0 0;">&copy; ' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.</p>';
        $html .= '</div>';
        
        $html .= '</div></body></html>';
        
        return $html;
    }
}
