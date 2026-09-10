<?php
/**
 * API Handler for Alex Travel Consultant
 */

if (!defined('ABSPATH')) {
    exit;
}

class AlexAPI {
    
    private static $instance = null;
    private $openai_key = '';
    private $api_base = 'https://api.openai.com/v1';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->openai_key = get_option('alex_openai_api_key', '');
    }
    
    /**
     * Process user consultation and get AI response
     */
    public function process_consultation($user_message, $session_id) {
        // Get conversation history
        $history = AlexDatabase::get_conversation_history($session_id, 10);
        
        // Build conversation context
        $conversation_context = $this->build_context($history);
        
        // Get AI response
        $ai_response = $this->get_ai_response($user_message, $conversation_context);
        
        // Save to database
        AlexDatabase::insert_conversation($session_id, $user_message, $ai_response);
        
        // Extract data from response
        $extracted_data = $this->extract_travel_data($user_message, $ai_response);
        
        return array(
            'response' => $ai_response,
            'session_id' => $session_id,
            'extracted_data' => $extracted_data
        );
    }
    
    /**
     * Get AI response from OpenAI
     */
    private function get_ai_response($user_message, $conversation_context) {
        if (empty($this->openai_key)) {
            return $this->get_fallback_response($user_message);
        }
        
        $system_prompt = $this->get_system_prompt();
        
        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_prompt
            )
        );
        
        // Add conversation history
        if (!empty($conversation_context)) {
            $messages = array_merge($messages, $conversation_context);
        }
        
        // Add current message
        $messages[] = array(
            'role' => 'user',
            'content' => $user_message
        );
        
        $response = wp_remote_post(
            $this->api_base . '/chat/completions',
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->openai_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => wp_json_encode(array(
                    'model' => 'gpt-3.5-turbo',
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 500
                )),
                'timeout' => 30
            )
        );
        
        if (is_wp_error($response)) {
            return 'I apologize, but I couldn\'t process your request at this moment. Please try again.';
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['choices'][0]['message']['content'])) {
            return $body['choices'][0]['message']['content'];
        }
        
        return $this->get_fallback_response($user_message);
    }
    
    /**
     * System prompt for Alex AI
     */
    private function get_system_prompt() {
        $consultant_name = get_option('alex_consultant_name', 'Alex');
        $consultant_title = get_option('alex_consultant_title', 'Senior Travel Advisor');
        
        return "You are {$consultant_name}, a friendly and highly knowledgeable {$consultant_title} at a premium travel agency. 
        
Your personality:
- Professional yet warm and personable
- Budget-conscious but quality-focused
- Expert in flights, hotels, cruises, vacation packages, and train travel (Amtrak)
- Always seeking to find the BEST VALUE for customers within their budget
- Ask clarifying questions to understand their needs better
- Provide specific recommendations when possible

Your approach:
1. Ask about their destination preferences
2. Understand their travel dates and flexibility
3. Determine their budget constraints
4. Learn about group size and special requirements
5. Recommend the best options from multiple categories
6. Always emphasize value and savings

When suggesting travel options, always:
- Be specific with rough estimates
- Mention cost-saving strategies
- Highlight exclusive deals available through our agency
- Ask about preferences to narrow down options

Response guidelines:
- Keep responses conversational and friendly
- Use emojis occasionally for warmth
- Ask one or two follow-up questions per response
- Be concise but informative (2-3 sentences per topic)
- Never make up specific prices, but give realistic ranges
- Always prioritize budget-friendly options first";
    }
    
    /**
     * Fallback response when API fails
     */
    private function get_fallback_response($user_message) {
        $responses = array(
            'That sounds wonderful! To help you find the best deals, could you tell me more about your destination and travel dates?',
            'Great choice! What\'s your budget range and how many people will be traveling?',
            'I love your travel idea! When are you planning to travel, and which service interests you most - flights, hotels, or a complete package?',
            'Perfect! To find you the best value, I need to know: what\'s your destination, when do you want to travel, and what\'s your budget?',
        );
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Build conversation context from history
     */
    private function build_context($history) {
        $context = array();
        
        // Limit to last 5 exchanges to keep context manageable
        $recent_history = array_slice($history, 0, 5);
        
        foreach (array_reverse($recent_history) as $message) {
            if (!empty($message->user_message)) {
                $context[] = array(
                    'role' => 'user',
                    'content' => $message->user_message
                );
            }
            if (!empty($message->ai_response)) {
                $context[] = array(
                    'role' => 'assistant',
                    'content' => $message->ai_response
                );
            }
        }
        
        return array_reverse($context);
    }
    
    /**
     * Extract travel data from conversation
     */
    private function extract_travel_data($user_message, $ai_response) {
        $data = array();
        
        // Look for destination
        if (preg_match('/(?:to|visit|going to|destination[s]?[\s:]*)[\s]*(\w+(?:\s\w+)*)/i', $user_message, $matches)) {
            $data['destination'] = trim($matches[1]);
        }
        
        // Look for budget
        if (preg_match('/\$([0-9,]+)|budget[\s:]*\$?([0-9,]+)/i', $user_message, $matches)) {
            $amount = $matches[1] ?: $matches[2];
            $data['budget'] = '$' . $amount;
        }
        
        // Look for number of travelers
        if (preg_match('/(\d+)\s*(?:people|travelers|persons|of us)/i', $user_message, $matches)) {
            $data['travelers'] = $matches[1];
        }
        
        // Look for travel type
        $service_keywords = array(
            'flights' => array('flight', 'fly', 'plane', 'air'),
            'hotels' => array('hotel', 'accommodation', 'stay', 'lodging'),
            'cruises' => array('cruise', 'ship', 'sailing'),
            'packages' => array('package', 'vacation', 'tour'),
            'amtrak' => array('train', 'amtrak', 'rail')
        );
        
        $combined_text = strtolower($user_message . ' ' . $ai_response);
        foreach ($service_keywords as $service => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($combined_text, $keyword) !== false) {
                    $data['service_type'] = $service;
                    break 2;
                }
            }
        }
        
        return $data;
    }
}
