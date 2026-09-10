<?php
/**
 * Settings class for Alex Travel Consultant - Groq FREE API Version
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlexSettings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Set default settings
     */
    public static function set_defaults() {
        $defaults = array(
            'alex_consultant_name' => 'Alex',
            'alex_consultant_title' => 'Senior Travel Advisor',
            'alex_admin_email' => get_option('admin_email'),
            'alex_groq_api_key' => '',
            'alex_enable_widget' => 'yes',
            'alex_widget_position' => 'bottom-right',
            'alex_color_primary' => '#d4af37',
            'alex_color_secondary' => '#1a1a1a',
        );
        
        foreach ($defaults as $key => $value) {
            if (!get_option($key)) {
                add_option($key, $value);
            }
        }
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap alex-settings-wrap">
            <h1><?php echo esc_html__('Alex Travel Consultant - Settings', 'alex-travel-consultant'); ?></h1>
            
            <div style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #2e7d32;">✅ <strong>COMPLETELY FREE AI - No Costs!</strong></h3>
                <p style="margin: 10px 0; color: #1b5e20;">This plugin uses <strong>Groq API</strong> which is <strong>100% FREE</strong>. No OpenAI costs, no hidden charges.</p>
                <p style="margin: 10px 0; color: #1b5e20;">Get your free Groq API key at: <a href="https://console.groq.com" target="_blank" style="color: #2e7d32; font-weight: bold;">https://console.groq.com</a></p>
            </div>
            
            <form method="post" action="options.php" class="alex-settings-form">
                <?php settings_fields('alex_consultant_settings'); ?>
                <?php do_settings_sections('alex_consultant_settings'); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting('alex_consultant_settings', 'alex_consultant_name');
        register_setting('alex_consultant_settings', 'alex_consultant_title');
        register_setting('alex_consultant_settings', 'alex_admin_email');
        register_setting('alex_consultant_settings', 'alex_groq_api_key');
        register_setting('alex_consultant_settings', 'alex_enable_widget');
        register_setting('alex_consultant_settings', 'alex_widget_position');
        register_setting('alex_consultant_settings', 'alex_color_primary');
        register_setting('alex_consultant_settings', 'alex_color_secondary');
        
        add_settings_section(
            'alex_basic_settings',
            __('Basic Settings', 'alex-travel-consultant'),
            array(__CLASS__, 'basic_settings_callback'),
            'alex_consultant_settings'
        );
        
        add_settings_field(
            'alex_consultant_name',
            __('Consultant Name', 'alex-travel-consultant'),
            array(__CLASS__, 'text_field_callback'),
            'alex_consultant_settings',
            'alex_basic_settings',
            array('name' => 'alex_consultant_name', 'value' => get_option('alex_consultant_name'))
        );
        
        add_settings_field(
            'alex_consultant_title',
            __('Consultant Title', 'alex-travel-consultant'),
            array(__CLASS__, 'text_field_callback'),
            'alex_consultant_settings',
            'alex_basic_settings',
            array('name' => 'alex_consultant_title', 'value' => get_option('alex_consultant_title'))
        );
        
        add_settings_field(
            'alex_admin_email',
            __('Admin Email (Leads Destination)', 'alex-travel-consultant'),
            array(__CLASS__, 'email_field_callback'),
            'alex_consultant_settings',
            'alex_basic_settings',
            array('name' => 'alex_admin_email', 'value' => get_option('alex_admin_email'))
        );
        
        add_settings_field(
            'alex_groq_api_key',
            __('Groq API Key (FREE)', 'alex-travel-consultant'),
            array(__CLASS__, 'groq_api_field_callback'),
            'alex_consultant_settings',
            'alex_basic_settings',
            array('name' => 'alex_groq_api_key', 'value' => get_option('alex_groq_api_key'))
        );
    }
    
    public static function basic_settings_callback() {
        echo esc_html__('Configure your Alex Travel Consultant settings', 'alex-travel-consultant');
    }
    
    public static function text_field_callback($args) {
        ?>
        <input type="text" name="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_attr($args['value']); ?>" class="regular-text" />
        <?php
    }
    
    public static function email_field_callback($args) {
        ?>
        <input type="email" name="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_attr($args['value']); ?>" class="regular-text" />
        <?php
    }
    
    public static function groq_api_field_callback($args) {
        ?>
        <input type="password" name="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_attr($args['value']); ?>" class="regular-text" />
        <p class="description">
            <strong>🎉 Get your FREE Groq API key:</strong><br>
            1. Visit: <a href="https://console.groq.com" target="_blank" style="color: #d4af37; text-decoration: none; font-weight: bold;">https://console.groq.com</a><br>
            2. Sign up (FREE - no credit card needed)<br>
            3. Go to "API Keys" section<br>
            4. Create new API key<br>
            5. Copy and paste it here<br>
            <br>
            <strong>No costs - Completely FREE!</strong> Groq offers free unlimited API access for reasonable usage.
        </p>
        <?php
    }
}

add_action('admin_init', array('AlexSettings', 'register_settings'));
