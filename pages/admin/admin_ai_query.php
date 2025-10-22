<?php
/**
 * AI Query Assistant - Simple Version
 * Natural Language to SQL using Groq AI
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../includes/db_connect.php");
require_once("../../includes/auth_admin.php");
require_once("../../includes/ai_helper.php");
require_once("../../includes/schema_extractor.php");

$admin_id = $_SESSION['admin_id'] ?? 1;
$ai_response = null;
$query_result = null;
$execution_time = 0;

// Initialize schema
$schemaExtractor = new SchemaExtractor($conn);
$databaseSchema = $schemaExtractor->getDatabaseSchema();

// Handle AI Query Generation
if (isset($_POST['generate_query']) && !empty($_POST['natural_query'])) {
    try {
        $naturalQuery = trim($_POST['natural_query']);
        $ai = AIHelper::getInstance();
        
        $ai_response = $ai->naturalLanguageToSQL($naturalQuery, $databaseSchema);
        
        if ($ai_response['success']) {
            // Save to history (optional - fail silently if table doesn't exist)
            try {
                $stmt = $conn->prepare("INSERT INTO ai_query_history (admin_id, natural_query, generated_sql, explanation, tokens_used) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isssi", $admin_id, $naturalQuery, $ai_response['sql'], $ai_response['explanation'], $ai_response['tokens_used']);
                $stmt->execute();
            } catch (Exception $e) {
                // Ignore history errors
            }
        }
    } catch (Exception $e) {
        $ai_response = [
            'success' => false,
            'error' => $e->getMessage(),
            'sql' => '',
            'explanation' => ''
        ];
    }
}

// Handle Query Execution
if (isset($_POST['execute_query']) && !empty($_POST['sql_query'])) {
    try {
        $sql = trim($_POST['sql_query']);
        
        // Validate safety (only SELECT queries)
        $ai = AIHelper::getInstance();
        $validation = $ai->validateSQLSafety($sql);
        
        if (!$validation['safe']) {
            throw new Exception($validation['message']);
        }
        
        $start = microtime(true);
        $query_result = $conn->query($sql);
        $execution_time = round((microtime(true) - $start) * 1000, 2);
        
        if ($query_result === FALSE) {
            throw new Exception($conn->error);
        }
        
        // Store the SQL for display
        $ai_response = [
            'success' => true,
            'sql' => $sql,
            'explanation' => 'Query executed successfully',
            'executed' => true
        ];
        
    } catch (Exception $e) {
        $ai_response = [
            'success' => false,
            'error' => $e->getMessage(),
            'sql' => $sql ?? '',
            'explanation' => ''
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Query Assistant - SmartStay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #334155;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav a {
            color: #667eea;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav a:hover {
            color: #764ba2;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #334155;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #475569;
            font-weight: 600;
        }
        
        textarea, input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        textarea:focus, input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #10b981;
        }
        
        .btn-success:hover {
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .code-box {
            background: #1e293b;
            color: #e2e8f0;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .explanation {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            color: #334155;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th {
            background: #f1f5f9;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        
        table tr:hover {
            background: #f8fafc;
        }
        
        .example-queries {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .example-btn {
            background: #e0e7ff;
            color: #4338ca;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .example-btn:hover {
            background: #c7d2fe;
            transform: translateY(-1px);
        }
        
        .stats {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            font-size: 13px;
            color: #64748b;
        }
        
        .stats span {
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <h1>🤖 AI Query Assistant</h1>
        <div class="nav">
            <a href="admin_home.php">🏠 Dashboard</a>
            <a href="admin_database.php">🗄️ Database</a>
            <a href="admin_logout.php">🚪 Logout</a>
        </div>
    </div>

    <!-- Main Query Form -->
    <div class="card">
        <h2>💬 Ask a Question in Plain English</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>What would you like to know?</label>
                <textarea 
                    name="natural_query" 
                    rows="4" 
                    placeholder="Example: Show me all hotels with 4+ star rating in New York"
                    required
                ><?= htmlspecialchars($_POST['natural_query'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" name="generate_query" class="btn">
                ✨ Generate SQL Query
            </button>
        </form>
        
        <div style="margin-top: 20px;">
            <strong style="color: #64748b; font-size: 13px;">💡 Quick Examples:</strong>
            <div class="example-queries">
                <button class="example-btn" onclick="setQuery('Show all hotels')">All Hotels</button>
                <button class="example-btn" onclick="setQuery('List top 10 guests by loyalty points')">Top Guests</button>
                <button class="example-btn" onclick="setQuery('What is the total revenue for last month?')">Revenue</button>
                <button class="example-btn" onclick="setQuery('Show upcoming events this week')">Events</button>
                <button class="example-btn" onclick="setQuery('Which rooms are available today?')">Available Rooms</button>
            </div>
        </div>
    </div>

    <!-- Generated Query Result -->
    <?php if ($ai_response): ?>
        <?php if ($ai_response['success']): ?>
            <div class="alert alert-success">
                ✅ <?= isset($ai_response['executed']) ? 'Query executed successfully!' : 'SQL query generated successfully!' ?>
            </div>
            
            <div class="card">
                <h2>📝 Generated SQL Query</h2>
                
                <?php if (!empty($ai_response['explanation'])): ?>
                <div class="explanation">
                    <strong>💡 Explanation:</strong> <?= htmlspecialchars($ai_response['explanation']) ?>
                </div>
                <?php endif; ?>
                
                <div class="code-box"><?= htmlspecialchars($ai_response['sql']) ?></div>
                
                <?php if (isset($ai_response['tokens_used'])): ?>
                <div class="stats">
                    <span>🔢 Tokens Used: <?= $ai_response['tokens_used'] ?></span>
                    <span>⚡ Powered by: <?= AIHelper::getProviderName() ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!isset($ai_response['executed'])): ?>
                <form method="POST" action="" style="margin-top: 20px;">
                    <input type="hidden" name="sql_query" value="<?= htmlspecialchars($ai_response['sql']) ?>">
                    <button type="submit" name="execute_query" class="btn btn-success">
                        ▶ Execute Query
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- Query Results Table -->
            <?php if ($query_result && is_object($query_result) && $query_result->num_rows > 0): ?>
            <div class="card">
                <h2>📊 Query Results</h2>
                <div class="stats">
                    <span>📝 Rows: <?= $query_result->num_rows ?></span>
                    <span>⏱️ Execution Time: <?= $execution_time ?>ms</span>
                </div>
                
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <?php
                                $fields = $query_result->fetch_fields();
                                foreach ($fields as $field) {
                                    echo "<th>" . htmlspecialchars($field->name) . "</th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $query_result->fetch_assoc()): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td><?= htmlspecialchars($value ?? 'NULL') ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php elseif ($query_result && is_object($query_result)): ?>
            <div class="alert alert-success">
                ✅ Query executed successfully (0 rows returned)
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-error">
                ❌ <strong>Error:</strong> <?= htmlspecialchars($ai_response['error'] ?? 'Unknown error occurred') ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function setQuery(text) {
    document.querySelector('textarea[name="natural_query"]').value = text;
    document.querySelector('textarea[name="natural_query"]').focus();
}
</script>
</body>
</html>
