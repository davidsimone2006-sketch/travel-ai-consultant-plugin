<?php
/**
 * Plugin uninstall script
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Drop custom tables
$tables = array(
    $wpdb->prefix . 'alex_leads',
    $wpdb->prefix . 'alex_conversations',
    $wpdb->prefix . 'alex_sessions'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Delete plugin options
$options = array(
    'alex_consultant_name',
    'alex_consultant_title',
    'alex_admin_email',
    'alex_openai_api_key',
    'alex_enable_widget',
    'alex_widget_position',
    'alex_color_primary',
    'alex_color_secondary'
);

foreach ($options as $option) {
    delete_option($option);
}

// Flush rewrite rules
flush_rewrite_rules();
