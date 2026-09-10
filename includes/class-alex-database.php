<?php
/**
 * Database class for Alex Travel Consultant
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlexDatabase {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create database tables on plugin activation
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Leads table
        $leads_table = $wpdb->prefix . 'alex_leads';
        $wpdb->query("CREATE TABLE IF NOT EXISTS $leads_table (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            destination VARCHAR(255),
            travel_date VARCHAR(50),
            budget VARCHAR(100),
            service_type VARCHAR(100),
            travelers VARCHAR(50),
            preferences LONGTEXT,
            conversation_summary LONGTEXT,
            status VARCHAR(50) DEFAULT 'new',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY email (email),
            KEY created_at (created_at)
        ) $charset_collate;");
        
        // Conversations table
        $conversations_table = $wpdb->prefix . 'alex_conversations';
        $wpdb->query("CREATE TABLE IF NOT EXISTS $conversations_table (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(255) NOT NULL,
            user_message LONGTEXT,
            ai_response LONGTEXT,
            message_type VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id)
        ) $charset_collate;");
        
        // Sessions table
        $sessions_table = $wpdb->prefix . 'alex_sessions';
        $wpdb->query("CREATE TABLE IF NOT EXISTS $sessions_table (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(255) NOT NULL UNIQUE,
            user_email VARCHAR(255),
            ip_address VARCHAR(100),
            conversation_data LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id)
        ) $charset_collate;");
    }
    
    /**
     * Insert a new lead
     */
    public static function insert_lead($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'alex_leads';
        
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }
    
    /**
     * Get all leads
     */
    public static function get_leads($limit = 50, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'alex_leads';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
    }
    
    /**
     * Get lead by ID
     */
    public static function get_lead($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'alex_leads';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Update lead status
     */
    public static function update_lead_status($id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'alex_leads';
        
        $wpdb->update(
            $table,
            array('status' => $status, 'updated_at' => current_time('mysql')),
            array('id' => $id)
        );
    }
    
    /**
     * Insert conversation message
     */
    public static function insert_conversation($session_id, $user_message, $ai_response) {
        global $wpdb;
        $table = $wpdb->prefix . 'alex_conversations';
        
        $wpdb->insert($table, array(
            'session_id' => $session_id,
            'user_message' => $user_message,
            'ai_response' => $ai_response,
            'created_at' => current_time('mysql')
        ));
    }
    
    /**
     * Get conversation history
     */
    public static function get_conversation_history($session_id, $limit = 50) {
        global $wpdb;
        $table = $wpdb->prefix . 'alex_conversations';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE session_id = %s ORDER BY created_at DESC LIMIT %d",
            $session_id,
            $limit
        ));
    }
    
    /**
     * Create or get session
     */
    public static function create_session() {
        global $wpdb;
        $table = $wpdb->prefix . 'alex_sessions';
        
        $session_id = wp_generate_uuid4();
        $user_email = wp_get_current_user()->user_email ?? '';
        $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        
        $wpdb->insert($table, array(
            'session_id' => $session_id,
            'user_email' => $user_email,
            'ip_address' => $ip_address,
            'created_at' => current_time('mysql')
        ));
        
        return $session_id;
    }
}
