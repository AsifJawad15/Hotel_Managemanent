<?php
/**
 * Simple AI Form Test
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>AI Form Test</h1>";

// Check if form was submitted
if (isset($_POST['generate_ai_query'])) {
    echo "<div style='background: yellow; padding: 20px; margin: 20px;'>";
    echo "<h2>✅ FORM SUBMITTED!</h2>";
    echo "<p>Natural Query: <strong>" . htmlspecialchars($_POST['natural_query'] ?? 'EMPTY') . "</strong></p>";
    echo "</div>";
    
    // Try to generate AI response
    try {
        require_once __DIR__ . '/includes/db_connect.php';
        require_once __DIR__ . '/includes/ai_helper.php';
        require_once __DIR__ . '/includes/schema_extractor.php';
        
        echo "<div style='background: lightblue; padding: 20px; margin: 20px;'>";
        echo "<h2>Testing AI Generation...</h2>";
        
        $ai = AIHelper::getInstance();
        $schemaExtractor = new SchemaExtractor($conn);
        $schema = $schemaExtractor->getDatabaseSchema();
        
        $naturalQuery = trim($_POST['natural_query']);
        echo "<p>Calling Groq API with query: <strong>$naturalQuery</strong></p>";
        
        $result = $ai->naturalLanguageToSQL($naturalQuery, $schema);
        
        echo "<h3>Result:</h3>";
        echo "<pre>" . print_r($result, true) . "</pre>";
        
        if ($result['success']) {
            echo "<div style='background: lightgreen; padding: 15px; margin-top: 10px;'>";
            echo "<h3>✅ SUCCESS!</h3>";
            echo "<p><strong>SQL:</strong></p>";
            echo "<pre style='background: black; color: lime; padding: 10px;'>" . htmlspecialchars($result['sql']) . "</pre>";
            echo "<p><strong>Explanation:</strong> " . htmlspecialchars($result['explanation']) . "</p>";
            echo "<p><strong>Tokens Used:</strong> " . $result['tokens_used'] . "</p>";
            echo "</div>";
        } else {
            echo "<div style='background: pink; padding: 15px; margin-top: 10px;'>";
            echo "<h3>❌ FAILED!</h3>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($result['error']) . "</p>";
            echo "</div>";
        }
        
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div style='background: red; color: white; padding: 20px; margin: 20px;'>";
        echo "<h2>❌ EXCEPTION!</h2>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<div style='background: lightgray; padding: 20px; margin: 20px;'>";
    echo "<p>Form not submitted yet. Fill out the form below:</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Form Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: blue;
            color: white;
            padding: 15px 30px;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: darkblue;
        }
    </style>
</head>
<body>
    <h2>📝 Enter Your Question:</h2>
    <form method="POST" action="">
        <textarea name="natural_query" rows="4" placeholder="Type your question here... (e.g., Show all hotels)" required></textarea>
        <br>
        <button type="submit" name="generate_ai_query">🚀 Generate SQL Query</button>
    </form>
    
    <div style="margin-top: 30px; padding: 15px; background: #f0f0f0; border-left: 4px solid #666;">
        <h3>📖 Instructions:</h3>
        <ol>
            <li>Type a question in the box above</li>
            <li>Click "Generate SQL Query" button</li>
            <li>You should see a YELLOW box if form submitted</li>
            <li>Then you should see results (green = success, pink = failed)</li>
        </ol>
    </div>
</body>
</html>
