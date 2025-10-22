<?php
/**
 * Groq AI API Connection Test Script
 * Run this to verify your Groq API key and connection
 */

// Load environment
require_once __DIR__ . '/includes/env_loader.php';
require_once __DIR__ . '/includes/ai_helper.php';
require_once __DIR__ . '/includes/schema_extractor.php';
require_once __DIR__ . '/includes/db_connect.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Groq AI Connection Test - SmartStay</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f8fafc;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #334155;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #10b981;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #ef4444;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid #3b82f6;
        }
        .code {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            margin: 10px 0;
        }
        .check-item {
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #cbd5e1;
            background: white;
        }
        .check-item.pass {
            border-left-color: #10b981;
        }
        .check-item.fail {
            border-left-color: #ef4444;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge-success {
            background: #10b981;
            color: white;
        }
        .badge-error {
            background: #ef4444;
            color: white;
        }
        .button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Groq AI Connection Test</h1>
        <p>This script tests your Groq API configuration and connection for natural language to SQL conversion.</p>

        <!-- Test 1: Environment Variables -->
        <div class="test-section">
            <h3>📋 Test 1: Environment Configuration</h3>
            <?php
            $api_key = getenv('GROQ_API_KEY');
            $model = getenv('GROQ_MODEL') ?: 'mixtral-8x7b-32768';
            $has_env = !empty($api_key);
            ?>
            
            <div class="check-item <?= $has_env ? 'pass' : 'fail' ?>">
                <?php if ($has_env): ?>
                    ✅ <strong>.env file loaded successfully</strong>
                    <span class="badge badge-success">PASS</span>
                <?php else: ?>
                    ❌ <strong>.env file not found or empty</strong>
                    <span class="badge badge-error">FAIL</span>
                <?php endif; ?>
            </div>

            <?php if ($has_env): ?>
                <div class="info">
                    <strong>Configuration Details:</strong><br>
                    • AI Provider: <strong>GROQ</strong><br>
                    • API Key: <?= substr($api_key, 0, 10) ?>...<?= substr($api_key, -4) ?> (masked)<br>
                    • Model: <?= htmlspecialchars($model) ?><br>
                    • Key Length: <?= strlen($api_key) ?> characters
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>Fix Required:</strong><br>
                    1. Ensure .env file exists in the SmartStay root folder<br>
                    2. Add: GROQ_API_KEY=your_groq_api_key_here<br>
                    3. Restart Apache server
                </div>
            <?php endif; ?>
        </div>

        <!-- Test 2: PHP cURL Extension -->
        <div class="test-section">
            <h3>🔌 Test 2: PHP cURL Extension</h3>
            <?php $has_curl = function_exists('curl_init'); ?>
            
            <div class="check-item <?= $has_curl ? 'pass' : 'fail' ?>">
                <?php if ($has_curl): ?>
                    ✅ <strong>cURL extension is enabled</strong>
                    <span class="badge badge-success">PASS</span>
                <?php else: ?>
                    ❌ <strong>cURL extension is NOT enabled</strong>
                    <span class="badge badge-error">FAIL</span>
                <?php endif; ?>
            </div>

            <?php if (!$has_curl): ?>
                <div class="error">
                    <strong>Fix Required:</strong><br>
                    1. Open: C:\xampp\php\php.ini<br>
                    2. Find: ;extension=curl<br>
                    3. Remove semicolon: extension=curl<br>
                    4. Save and restart Apache
                </div>
            <?php endif; ?>
        </div>

        <!-- Test 3: Database Connection -->
        <div class="test-section">
            <h3>🗄️ Test 3: Database Connection</h3>
            <?php 
            $db_connected = isset($conn) && $conn->ping();
            ?>
            
            <div class="check-item <?= $db_connected ? 'pass' : 'fail' ?>">
                <?php if ($db_connected): ?>
                    ✅ <strong>Database connected successfully</strong>
                    <span class="badge badge-success">PASS</span>
                <?php else: ?>
                    ❌ <strong>Database connection failed</strong>
                    <span class="badge badge-error">FAIL</span>
                <?php endif; ?>
            </div>

            <?php if ($db_connected): ?>
                <?php
                $tables_exist = true;
                $required_tables = ['ai_query_history', 'ai_query_favorites', 'ai_usage_stats'];
                $existing_tables = [];
                
                foreach ($required_tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        $existing_tables[] = $table;
                    } else {
                        $tables_exist = false;
                    }
                }
                ?>
                
                <div class="check-item <?= $tables_exist ? 'pass' : 'fail' ?>">
                    <?php if ($tables_exist): ?>
                        ✅ <strong>All AI tables exist</strong>
                        <span class="badge badge-success">PASS</span>
                    <?php else: ?>
                        ⚠️ <strong>Some AI tables missing</strong>
                        <span class="badge badge-error">WARNING</span>
                    <?php endif; ?>
                </div>

                <?php if (!$tables_exist): ?>
                    <div class="error">
                        <strong>Fix Required:</strong><br>
                        Import the database schema:<br>
                        <div class="code">mysql -u root -p smart_stay < db/09_ai_assistant.sql</div>
                        Or use phpMyAdmin to import: <strong>db/09_ai_assistant.sql</strong>
                    </div>
                <?php else: ?>
                    <div class="info">
                        Found tables: <?= implode(', ', $existing_tables) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Test 4: AI API Connection -->
        <?php if ($has_env && $has_curl): ?>
        <div class="test-section">
            <h3>🌐 Test 4: Groq AI API Connection</h3>
            <?php
            try {
                $ai = AIHelper::getInstance();
                $schemaExtractor = new SchemaExtractor($conn);
                $schema = $schemaExtractor->getDatabaseSchema();
                
                // Simple test query
                $test_query = "Show all hotels";
                $result = $ai->naturalLanguageToSQL($test_query, $schema);
                
                if ($result['success']) {
                    ?>
                    <div class="check-item pass">
                        ✅ <strong>Groq AI API connection successful!</strong>
                        <span class="badge badge-success">PASS</span>
                    </div>
                    
                    <div class="success">
                        <strong>Test Query:</strong> "<?= htmlspecialchars($test_query) ?>"<br><br>
                        <strong>Generated SQL:</strong>
                        <div class="code"><?= htmlspecialchars($result['sql']) ?></div>
                        <strong>Explanation:</strong><br>
                        <?= htmlspecialchars($result['explanation']) ?><br><br>
                        <strong>Tokens Used:</strong> <?= $result['tokens_used'] ?? 'N/A' ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="check-item fail">
                        ❌ <strong>Groq AI API returned an error</strong>
                        <span class="badge badge-error">FAIL</span>
                    </div>
                    
                    <div class="error">
                        <strong>Error Message:</strong><br>
                        <?= htmlspecialchars($result['error']) ?>
                    </div>
                    <?php
                }
            } catch (Exception $e) {
                ?>
                <div class="check-item fail">
                    ❌ <strong>Failed to connect to Groq AI API</strong>
                    <span class="badge badge-error">FAIL</span>
                </div>
                
                <div class="error">
                    <strong>Exception:</strong><br>
                    <?= htmlspecialchars($e->getMessage()) ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php endif; ?>

        <!-- Summary -->
        <div class="test-section" style="border-left-color: #667eea; background: #f0f4ff;">
            <h3>📊 Test Summary</h3>
            <?php
            $all_pass = $has_env && $has_curl && $db_connected && ($tables_exist ?? false);
            ?>
            
            <?php if ($all_pass): ?>
                <div class="success">
                    <strong>🎉 All tests passed!</strong><br>
                    Your AI Query Assistant is ready to use!<br><br>
                    <a href="pages/admin/admin_ai_query.php" class="button">🚀 Go to AI Query Assistant</a>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>⚠️ Some tests failed</strong><br>
                    Please fix the issues above and refresh this page.<br><br>
                    <strong>Quick Fixes:</strong>
                    <ul style="margin-top: 10px;">
                        <?php if (!$has_env): ?>
                            <li>Configure .env file with your Groq API key</li>
                        <?php endif; ?>
                        <?php if (!$has_curl): ?>
                            <li>Enable cURL extension in php.ini</li>
                        <?php endif; ?>
                        <?php if (!$db_connected): ?>
                            <li>Check database connection settings</li>
                        <?php endif; ?>
                        <?php if (isset($tables_exist) && !$tables_exist): ?>
                            <li>Import db/09_ai_assistant.sql into database</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0; text-align: center; color: #64748b;">
            <p>📚 Need help? Check <a href="AI_QUERY_SETUP.md" style="color: #667eea;">AI_QUERY_SETUP.md</a> for detailed setup instructions.</p>
        </div>
    </div>
</body>
</html>
