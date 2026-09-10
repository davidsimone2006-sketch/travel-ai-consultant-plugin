<?php
/*
Plugin Name: Alex - AI Travel Consultant
Plugin URI: https://mytripfares.com
Description: Premium AI Travel Consultant with 3D immersive interface for WordPress. Handles Flights, Hotels, Cruises, Packages & Amtrak with intelligent lead generation.
Version: 1.0.0
Author: MyTripFares
Author URI: https://mytripfares.com
License: MIT
License URI: https://opensource.org/licenses/MIT
Text Domain: alex-travel-consultant
Domain Path: /languages
Requires at least: 6.0
Requires PHP: 7.4
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed.');
}

// Version constant
define('ALEX_TC_VERSION', '1.0.0');
define('ALEX_TC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALEX_TC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALEX_TC_ASSETS_URL', ALEX_TC_PLUGIN_URL . 'assets/');

// Load plugin text domain
add_action('plugins_loaded', function() {
    load_plugin_textdomain('alex-travel-consultant', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Include core files
require_once ALEX_TC_PLUGIN_DIR . 'includes/class-alex-admin.php';
require_once ALEX_TC_PLUGIN_DIR . 'includes/class-alex-frontend.php';
require_once ALEX_TC_PLUGIN_DIR . 'includes/class-alex-api.php';
require_once ALEX_TC_PLUGIN_DIR . 'includes/class-alex-database.php';
require_once ALEX_TC_PLUGIN_DIR . 'includes/class-alex-settings.php';
require_once ALEX_TC_PLUGIN_DIR . 'includes/class-alex-email.php';

/**
 * Main plugin class
 */
class AlexTravelConsultant {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->init();
    }
    
    private function init() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'load_components'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        add_action('wp_ajax_nopriv_alex_get_consultation', array($this, 'ajax_consultation'));
        add_action('wp_ajax_alex_get_consultation', array($this, 'ajax_consultation'));
        add_action('wp_ajax_nopriv_alex_submit_lead', array($this, 'ajax_submit_lead'));
        add_action('wp_ajax_alex_submit_lead', array($this, 'ajax_submit_lead'));
        add_action('wp_ajax_alex_update_lead_status', array($this, 'ajax_update_lead_status'));
        
        add_shortcode('alex_travel_consultant', array($this, 'render_shortcode'));
    }
    
    public function load_components() {
        AlexDatabase::get_instance();
        AlexSettings::get_instance();
        AlexAPI::get_instance();
    }
    
    public function activate() {
        AlexDatabase::create_tables();
        AlexSettings::set_defaults();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Alex Travel Consultant', 'alex-travel-consultant'),
            __('Alex Consultant', 'alex-travel-consultant'),
            'manage_options',
            'alex-consultant',
            array(AlexAdmin::get_instance(), 'render_dashboard'),
            'dashicons-businessman',
            25
        );
        
        add_submenu_page(
            'alex-consultant',
            __('Dashboard', 'alex-travel-consultant'),
            __('Dashboard', 'alex-travel-consultant'),
            'manage_options',
            'alex-consultant',
            array(AlexAdmin::get_instance(), 'render_dashboard')
        );
        
        add_submenu_page(
            'alex-consultant',
            __('Leads', 'alex-travel-consultant'),
            __('Travel Leads', 'alex-travel-consultant'),
            'manage_options',
            'alex-leads',
            array(AlexAdmin::get_instance(), 'render_leads')
        );
        
        add_submenu_page(
            'alex-consultant',
            __('Conversations', 'alex-travel-consultant'),
            __('Conversations', 'alex-travel-consultant'),
            'manage_options',
            'alex-conversations',
            array(AlexAdmin::get_instance(), 'render_conversations')
        );
        
        add_submenu_page(
            'alex-consultant',
            __('Settings', 'alex-travel-consultant'),
            __('Settings', 'alex-travel-consultant'),
            'manage_options',
            'alex-settings',
            array(AlexSettings::get_instance(), 'render_settings_page')
        );
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_script(
            'threejs',
            ALEX_TC_ASSETS_URL . 'js/three.min.js',
            array(),
            ALEX_TC_VERSION,
            true
        );
        
        wp_enqueue_style(
            'alex-consultant-style',
            ALEX_TC_ASSETS_URL . 'css/alex-consultant.css',
            array(),
            ALEX_TC_VERSION
        );
        
        wp_enqueue_script(
            'alex-consultant-app',
            ALEX_TC_ASSETS_URL . 'js/alex-consultant.js',
            array('jquery', 'threejs'),
            ALEX_TC_VERSION,
            true
        );
        
        wp_localize_script('alex-consultant-app', 'alexConsultantData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('alex_consultant_nonce'),
            'consultantName' => get_option('alex_consultant_name', 'Alex'),
            'consultantTitle' => get_option('alex_consultant_title', 'Senior Travel Advisor'),
            'userEmail' => wp_get_current_user()->user_email || '',
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'alex-consultant') === false) {
            return;
        }
        
        wp_enqueue_style(
            'alex-consultant-admin',
            ALEX_TC_ASSETS_URL . 'css/alex-admin.css',
            array(),
            ALEX_TC_VERSION
        );
        
        wp_enqueue_script(
            'alex-consultant-admin',
            ALEX_TC_ASSETS_URL . 'js/alex-admin.js',
            array('jquery'),
            ALEX_TC_VERSION,
            true
        );
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'modal',
            'show_button' => 'true',
        ), $atts, 'alex_travel_consultant');
        
        ob_start();
        include ALEX_TC_PLUGIN_DIR . 'templates/shortcode.php';
        return ob_get_clean();
    }
    
    public function ajax_consultation() {
        check_ajax_referer('alex_consultant_nonce');
        
        $user_message = sanitize_text_field($_POST['message'] ?? '');
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($user_message)) {
            wp_send_json_error('Message is required');
        }
        
        $api = AlexAPI::get_instance();
        $response = $api->process_consultation($user_message, $session_id);
        
        wp_send_json_success($response);
    }
    
    public function ajax_submit_lead() {
        check_ajax_referer('alex_consultant_nonce');
        
        $lead_data = array(
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'destination' => sanitize_text_field($_POST['destination'] ?? ''),
            'travel_date' => sanitize_text_field($_POST['travel_date'] ?? ''),
            'budget' => sanitize_text_field($_POST['budget'] ?? ''),
            'service_type' => sanitize_text_field($_POST['service_type'] ?? ''),
            'travelers' => sanitize_text_field($_POST['travelers'] ?? ''),
            'preferences' => sanitize_textarea_field($_POST['preferences'] ?? ''),
            'conversation_summary' => sanitize_textarea_field($_POST['conversation_summary'] ?? ''),
        );
        
        if (empty($lead_data['name']) || empty($lead_data['email'])) {
            wp_send_json_error('Name and email are required');
        }
        
        $email_handler = AlexEmail::get_instance();
        $lead_id = $email_handler->save_lead($lead_data);
        $email_handler->send_lead_email($lead_data, $lead_id);
        $email_handler->send_user_confirmation($lead_data);
        
        wp_send_json_success(array(
            'lead_id' => $lead_id,
            'message' => 'Lead submitted successfully!'
        ));
    }
    
    public function ajax_update_lead_status() {
        check_admin_referer('alex_update_lead_status');
        
        $lead_id = intval($_POST['lead_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? 'new');
        
        if ($lead_id > 0) {
            AlexDatabase::update_lead_status($lead_id, $status);
            wp_send_json_success('Status updated');
        } else {
            wp_send_json_error('Invalid lead ID');
        }
    }
}

// Initialize plugin
AlexTravelConsultant::get_instance();
