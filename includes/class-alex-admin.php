<?php
/**
 * Admin class for Alex Travel Consultant
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlexAdmin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Render admin dashboard
     */
    public function render_dashboard() {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'alex_leads';
        
        // Get statistics
        $total_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table");
        $new_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table WHERE status = 'new'");
        $contacted_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table WHERE status = 'contacted'");
        $converted_leads = $wpdb->get_var("SELECT COUNT(*) FROM $leads_table WHERE status = 'converted'");
        
        // Get recent leads
        $recent_leads = $wpdb->get_results("SELECT * FROM $leads_table ORDER BY created_at DESC LIMIT 5");
        
        ?>
        <div class="wrap alex-admin-wrap">
            <h1>🌍 Alex Travel Consultant - Dashboard</h1>
            
            <div class="alex-dashboard-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo esc_html($total_leads); ?></div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-card highlight-new">
                    <div class="stat-number"><?php echo esc_html($new_leads); ?></div>
                    <div class="stat-label">New Leads</div>
                </div>
                <div class="stat-card highlight-contacted">
                    <div class="stat-number"><?php echo esc_html($contacted_leads); ?></div>
                    <div class="stat-label">Contacted</div>
                </div>
                <div class="stat-card highlight-converted">
                    <div class="stat-number"><?php echo esc_html($converted_leads); ?></div>
                    <div class="stat-label">Converted</div>
                </div>
            </div>
            
            <h2>Recent Leads</h2>
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Destination</th>
                        <th>Service Type</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_leads): ?>
                        <?php foreach ($recent_leads as $lead): ?>
                            <tr>
                                <td><strong><?php echo esc_html($lead->name); ?></strong></td>
                                <td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td>
                                <td><?php echo esc_html($lead->destination); ?></td>
                                <td><?php echo esc_html($lead->service_type); ?></td>
                                <td><?php echo esc_html($lead->budget); ?></td>
                                <td><span class="lead-status status-<?php echo esc_attr($lead->status); ?>"><?php echo esc_html(ucfirst($lead->status)); ?></span></td>
                                <td><?php echo esc_html(date('M d, Y', strtotime($lead->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=alex-leads&lead_id=' . $lead->id); ?>" class="button button-small">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                <em>No leads yet. Your first leads will appear here!</em>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="alex-admin-info">
                <h3>📌 Quick Setup</h3>
                <ol>
                    <li>Go to <a href="<?php echo admin_url('admin.php?page=alex-settings'); ?>">Settings</a> and configure your OpenAI API key</li>
                    <li>Use shortcode <code>[alex_travel_consultant]</code> on any page or post</li>
                    <li>Leads will be sent to your configured email</li>
                    <li>Manage leads and their status from this dashboard</li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render leads page
     */
    public function render_leads() {
        global $wpdb;
        
        $leads_table = $wpdb->prefix . 'alex_leads';
        $lead_id = isset($_GET['lead_id']) ? intval($_GET['lead_id']) : 0;
        
        if ($lead_id > 0) {
            $lead = AlexDatabase::get_lead($lead_id);
            
            if (!$lead) {
                echo '<div class="notice notice-error"><p>Lead not found.</p></div>';
                return;
            }
            ?>
            <div class="wrap alex-admin-wrap">
                <a href="<?php echo admin_url('admin.php?page=alex-leads'); ?>" class="button">← Back to Leads</a>
                <h1>Lead Details: <?php echo esc_html($lead->name); ?></h1>
                
                <div class="alex-lead-details">
                    <div class="detail-section">
                        <h2>Contact Information</h2>
                        <table class="alex-detail-table">
                            <tr>
                                <td class="label">Name:</td>
                                <td><?php echo esc_html($lead->name); ?></td>
                            </tr>
                            <tr>
                                <td class="label">Email:</td>
                                <td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td>
                            </tr>
                            <tr>
                                <td class="label">Phone:</td>
                                <td><?php echo esc_html($lead->phone); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="detail-section">
                        <h2>Travel Preferences</h2>
                        <table class="alex-detail-table">
                            <tr>
                                <td class="label">Destination:</td>
                                <td><?php echo esc_html($lead->destination); ?></td>
                            </tr>
                            <tr>
                                <td class="label">Travel Date:</td>
                                <td><?php echo esc_html($lead->travel_date); ?></td>
                            </tr>
                            <tr>
                                <td class="label">Budget:</td>
                                <td><?php echo esc_html($lead->budget); ?></td>
                            </tr>
                            <tr>
                                <td class="label">Service Type:</td>
                                <td><?php echo esc_html($lead->service_type); ?></td>
                            </tr>
                            <tr>
                                <td class="label">Travelers:</td>
                                <td><?php echo esc_html($lead->travelers); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="detail-section">
                        <h2>Conversation Summary</h2>
                        <div class="alex-summary">
                            <?php echo wp_kses_post(nl2br($lead->preferences)); ?>
                        </div>
                    </div>
                    
                    <div class="detail-section">
                        <h2>Status</h2>
                        <form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
                            <?php wp_nonce_field('alex_update_lead_status'); ?>
                            <input type="hidden" name="action" value="alex_update_lead_status" />
                            <input type="hidden" name="lead_id" value="<?php echo esc_attr($lead_id); ?>" />
                            <select name="status" id="lead-status">
                                <option value="new" <?php selected($lead->status, 'new'); ?>>🆕 New</option>
                                <option value="contacted" <?php selected($lead->status, 'contacted'); ?>>📞 Contacted</option>
                                <option value="converted" <?php selected($lead->status, 'converted'); ?>>✅ Converted</option>
                                <option value="lost" <?php selected($lead->status, 'lost'); ?>>❌ Lost</option>
                            </select>
                            <button type="submit" class="button button-primary">Update Status</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        } else {
            // List all leads
            $leads = AlexDatabase::get_leads(50);
            ?>
            <div class="wrap alex-admin-wrap">
                <h1>📋 Travel Leads</h1>
                
                <table class="wp-list-table widefat striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Destination</th>
                            <th>Service Type</th>
                            <th>Budget</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($leads): ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($lead->name); ?></strong></td>
                                    <td><a href="mailto:<?php echo esc_attr($lead->email); ?>"><?php echo esc_html($lead->email); ?></a></td>
                                    <td><?php echo esc_html($lead->destination); ?></td>
                                    <td><?php echo esc_html($lead->service_type); ?></td>
                                    <td><?php echo esc_html($lead->budget); ?></td>
                                    <td><span class="lead-status status-<?php echo esc_attr($lead->status); ?>"><?php echo esc_html(ucfirst($lead->status)); ?></span></td>
                                    <td><?php echo esc_html(date('M d, Y', strtotime($lead->created_at))); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=alex-leads&lead_id=' . $lead->id); ?>" class="button button-small">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px;">
                                    <em>No leads yet.</em>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }
    
    /**
     * Render conversations page
     */
    public function render_conversations() {
        global $wpdb;
        $conversations_table = $wpdb->prefix . 'alex_conversations';
        
        $conversations = $wpdb->get_results(
            "SELECT DISTINCT session_id, COUNT(*) as message_count, MAX(created_at) as last_message 
            FROM $conversations_table 
            GROUP BY session_id 
            ORDER BY last_message DESC 
            LIMIT 50"
        );
        ?>
        <div class="wrap alex-admin-wrap">
            <h1>💬 Conversations</h1>
            
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Messages</th>
                        <th>Last Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($conversations): ?>
                        <?php foreach ($conversations as $conv): ?>
                            <tr>
                                <td><code><?php echo esc_html(substr($conv->session_id, 0, 8)); ?>...</code></td>
                                <td><?php echo esc_html($conv->message_count); ?></td>
                                <td><?php echo esc_html(date('M d, Y H:i', strtotime($conv->last_message))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px;">
                                <em>No conversations yet.</em>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
