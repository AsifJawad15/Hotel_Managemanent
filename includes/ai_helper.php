<?php
/**
 * AI Helper Factory
 * Returns Groq helper for natural language to SQL conversion
 */

require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/groq_helper.php';

class AIHelper {
    private static $instance = null;
    private $helper;
    
    private function __construct() {
        $this->helper = new GroqHelper();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->helper;
    }
    
    /**
     * Get provider name
     */
    public static function getProviderName() {
        return 'Groq AI (Llama 3.3 70B)';
    }
}
?>
