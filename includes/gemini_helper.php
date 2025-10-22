<?php
/**
 * Google Gemini API Helper
 * Handles communication with Google Gemini API for natural language to SQL conversion
 */

require_once __DIR__ . '/env_loader.php';

class GeminiHelper {
    private $apiKey;
    private $model;
    private $maxTokens;
    private $temperature;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1/models/';
    
    public function __construct() {
        $this->apiKey = getenv('GEMINI_API_KEY');
        $this->model = getenv('GEMINI_MODEL') ?: 'gemini-1.5-pro-latest';
        $this->maxTokens = (int)(getenv('AI_MAX_TOKENS') ?: 2000);
        $this->temperature = (float)(getenv('AI_TEMPERATURE') ?: 0.1);
        
        if (empty($this->apiKey)) {
            throw new Exception('Gemini API key not configured. Please set GEMINI_API_KEY in .env file.');
        }
    }
    
    /**
     * Convert natural language to SQL query
     * @param string $naturalLanguageQuery User's question in plain English
     * @param array $databaseSchema Database schema information
     * @return array ['success' => bool, 'sql' => string, 'explanation' => string, 'error' => string]
     */
    public function naturalLanguageToSQL($naturalLanguageQuery, $databaseSchema) {
        try {
            $systemPrompt = $this->buildSystemPrompt($databaseSchema);
            $userPrompt = $this->buildUserPrompt($naturalLanguageQuery);
            $fullPrompt = $systemPrompt . "\n\n" . $userPrompt;
            
            $response = $this->callGemini($fullPrompt);
            
            return $this->parseResponse($response);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'sql' => '',
                'explanation' => '',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Build system prompt with database schema
     */
    private function buildSystemPrompt($databaseSchema) {
        $schemaText = $this->formatSchemaForPrompt($databaseSchema);
        
        return "You are an expert MySQL database query generator for the SmartStay Hotel Management System.

DATABASE SCHEMA:
$schemaText

IMPORTANT RULES:
1. Generate ONLY valid MySQL queries for the 'smart_stay' database
2. Use proper JOIN statements when querying multiple tables
3. Always use table aliases for better readability
4. Include appropriate WHERE clauses for filtering
5. Use ORDER BY and LIMIT when appropriate
6. For date comparisons, use MySQL date functions
7. Return ONLY SELECT queries (READ-ONLY, no INSERT/UPDATE/DELETE)
8. Use aggregate functions (COUNT, SUM, AVG, MAX, MIN) when needed
9. Format queries with proper indentation
10. Include helpful column aliases with 'AS' keyword

RESPONSE FORMAT:
You must respond with a JSON object containing:
{
    \"sql\": \"the complete SQL query\",
    \"explanation\": \"brief explanation of what the query does and which tables/columns are used\"
}

EXAMPLES:
User: \"Show me all hotels in New York\"
Response: {
    \"sql\": \"SELECT hotel_id, hotel_name, address, city, star_rating\\nFROM hotels\\nWHERE city = 'New York' AND is_active = 1\\nORDER BY hotel_name\",
    \"explanation\": \"This query selects all active hotels from the hotels table where the city is New York, ordered alphabetically by hotel name.\"
}

User: \"What's the total revenue last month?\"
Response: {
    \"sql\": \"SELECT SUM(final_amount) AS total_revenue, COUNT(booking_id) AS total_bookings\\nFROM bookings\\nWHERE booking_status = 'Completed'\\n  AND MONTH(check_in) = MONTH(CURDATE() - INTERVAL 1 MONTH)\\n  AND YEAR(check_in) = YEAR(CURDATE() - INTERVAL 1 MONTH)\",
    \"explanation\": \"This query calculates the total revenue and number of completed bookings from last month using the bookings table.\"
}

Generate accurate, efficient SQL queries based on the user's natural language input.";
    }
    
    /**
     * Build user prompt
     */
    private function buildUserPrompt($naturalLanguageQuery) {
        return "Convert this natural language query to SQL:\n\n\"$naturalLanguageQuery\"\n\nRemember to respond with JSON format containing 'sql' and 'explanation' fields.";
    }
    
    /**
     * Format database schema for prompt
     */
    private function formatSchemaForPrompt($databaseSchema) {
        $formatted = "";
        
        foreach ($databaseSchema as $tableName => $tableInfo) {
            $formatted .= "\nTable: $tableName\n";
            $formatted .= "Description: " . ($tableInfo['description'] ?? 'N/A') . "\n";
            $formatted .= "Columns:\n";
            
            foreach ($tableInfo['columns'] as $column) {
                $formatted .= "  - {$column['name']} ({$column['type']})";
                if (!empty($column['description'])) {
                    $formatted .= " - {$column['description']}";
                }
                $formatted .= "\n";
            }
            
            if (!empty($tableInfo['relationships'])) {
                $formatted .= "Relationships:\n";
                foreach ($tableInfo['relationships'] as $rel) {
                    $formatted .= "  - $rel\n";
                }
            }
            $formatted .= "\n";
        }
        
        return $formatted;
    }
    
    /**
     * Call Gemini API
     */
    private function callGemini($prompt) {
        $url = $this->apiUrl . $this->model . ':generateContent?key=' . $this->apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $this->temperature,
                'maxOutputTokens' => $this->maxTokens
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error']['message'] ?? 'Unknown API error';
            throw new Exception("Gemini API Error (HTTP $httpCode): $errorMsg");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Parse Gemini response
     */
    private function parseResponse($response) {
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid response from Gemini API');
        }
        
        $content = $response['candidates'][0]['content']['parts'][0]['text'];
        $parsed = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse Gemini response as JSON: ' . json_last_error_msg());
        }
        
        if (empty($parsed['sql'])) {
            throw new Exception('No SQL query generated');
        }
        
        // Calculate approximate token usage (Gemini doesn't return it directly)
        $tokens = str_word_count($content) * 1.3; // Approximate
        
        return [
            'success' => true,
            'sql' => $parsed['sql'],
            'explanation' => $parsed['explanation'] ?? '',
            'error' => '',
            'tokens_used' => (int)$tokens
        ];
    }
    
    /**
     * Validate SQL query for safety
     * Only allows SELECT statements
     */
    public function validateSQLSafety($sql) {
        $sql = trim(strtoupper($sql));
        
        // Remove comments
        $sql = preg_replace('/--.*$|\/\*.*?\*\//s', '', $sql);
        
        // Check if it's a SELECT query
        if (!preg_match('/^SELECT\s+/i', $sql)) {
            return [
                'safe' => false,
                'message' => 'Only SELECT queries are allowed for security reasons.'
            ];
        }
        
        // Check for dangerous keywords
        $dangerousKeywords = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE', 'REPLACE', 'GRANT', 'REVOKE'];
        foreach ($dangerousKeywords as $keyword) {
            if (stripos($sql, $keyword) !== false) {
                return [
                    'safe' => false,
                    'message' => "Query contains forbidden keyword: $keyword"
                ];
            }
        }
        
        return [
            'safe' => true,
            'message' => 'Query passed safety validation'
        ];
    }
}
?>
