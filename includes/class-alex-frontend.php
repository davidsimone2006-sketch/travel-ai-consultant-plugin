<?php
/**
 * Frontend class for Alex Travel Consultant
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlexFrontend {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Render the consultant interface
     */
    public static function render_interface() {
        $consultant_name = get_option('alex_consultant_name', 'Alex');
        $consultant_title = get_option('alex_consultant_title', 'Senior Travel Advisor');
        $session_id = AlexDatabase::create_session();
        ?>
        <div id="alex-consultant-container" class="alex-consultant-container" data-session-id="<?php echo esc_attr($session_id); ?>">
            <div class="alex-consultant-wrapper">
                <!-- 3D Background -->
                <div id="alex-3d-canvas" class="alex-3d-canvas"></div>
                
                <!-- Main Chat Interface -->
                <div class="alex-consultant-panel">
                    <!-- Header -->
                    <div class="alex-header">
                        <div class="alex-avatar-container">
                            <div class="alex-avatar">
                                <div class="avatar-icon">👨‍💼</div>
                            </div>
                        </div>
                        <div class="alex-header-info">
                            <h2 class="alex-name"><?php echo esc_html($consultant_name); ?></h2>
                            <p class="alex-title"><?php echo esc_html($consultant_title); ?></p>
                            <span class="alex-status online">● Online & Ready to Help</span>
                        </div>
                        <button class="alex-close-btn" aria-label="Close consultant">
                            <span>×</span>
                        </button>
                    </div>
                    
                    <!-- Welcome Message -->
                    <div class="alex-conversation">
                        <div class="alex-messages-container" id="alex-messages">
                            <div class="alex-message alex-message-welcome">
                                <div class="message-content">
                                    <h3>Welcome! 🌍</h3>
                                    <p>Hi there! I'm <strong><?php echo esc_html($consultant_name); ?></strong>, your personal travel advisor. I'm here to help you find the perfect trip within your budget.</p>
                                    <p>Whether you're dreaming of flights, luxury hotels, exciting cruises, vacation packages, or scenic train journeys, I've got you covered!</p>
                                    <p><strong>Let's get started! Tell me...</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Start Buttons -->
                    <div class="alex-quick-start" id="alex-quick-start">
                        <p class="quick-start-label">What brings you here today?</p>
                        <div class="quick-buttons">
                            <button class="quick-btn" data-action="flights">✈️ Find Flights</button>
                            <button class="quick-btn" data-action="hotels">🏨 Book Hotels</button>
                            <button class="quick-btn" data-action="cruises">🚢 Cruise Deals</button>
                            <button class="quick-btn" data-action="packages">📦 Vacation Packages</button>
                        </div>
                    </div>
                    
                    <!-- Input Area -->
                    <div class="alex-input-area">
                        <form id="alex-message-form" class="alex-message-form">
                            <input 
                                type="text" 
                                id="alex-input" 
                                class="alex-input" 
                                placeholder="Tell me about your ideal trip..." 
                                autocomplete="off"
                            />
                            <button type="submit" class="alex-send-btn" aria-label="Send message">
                                <span class="send-icon">→</span>
                            </button>
                        </form>
                        <p class="alex-disclaimer">Powered by AI • Your data is secure & private</p>
                    </div>
                </div>
                
                <!-- Details Panel (Side) -->
                <div class="alex-details-panel">
                    <div class="details-header">
                        <h3>📋 Your Travel Profile</h3>
                        <button class="details-close">×</button>
                    </div>
                    <div class="details-content" id="alex-details-content">
                        <p class="empty-state">Information will appear here as we chat...</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
