<?php
/**
 * Debug AI Query Generation
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing AI Query Generation</h2>";
echo "<pre>";

try {
    echo "1. Loading environment...\n";
    require_once __DIR__ . '/includes/env_loader.php';
    echo "   ✓ Environment loaded\n\n";
    
    echo "2. Loading database connection...\n";
    require_once __DIR__ . '/includes/db_connect.php';
    echo "   ✓ Database connected\n\n";
    
    echo "3. Loading AI helper...\n";
    require_once __DIR__ . '/includes/ai_helper.php';
    echo "   ✓ AI helper loaded\n\n";
    
    echo "4. Loading schema extractor...\n";
    require_once __DIR__ . '/includes/schema_extractor.php';
    echo "   ✓ Schema extractor loaded\n\n";
    
    echo "5. Getting AI instance...\n";
    $ai = AIHelper::getInstance();
    echo "   ✓ AI instance created\n";
    echo "   Provider: " . AIHelper::getProviderName() . "\n\n";
    
    echo "6. Extracting database schema...\n";
    $schemaExtractor = new SchemaExtractor($conn);
    $databaseSchema = $schemaExtractor->getDatabaseSchema();
    echo "   ✓ Schema extracted\n";
    echo "   Tables found: " . count($databaseSchema) . "\n\n";
    
    echo "7. Testing AI query generation...\n";
    $test_query = "Show all hotels";
    echo "   Query: '$test_query'\n";
    
    $result = $ai->naturalLanguageToSQL($test_query, $databaseSchema);
    
    echo "   Result:\n";
    print_r($result);
    
    if ($result['success']) {
        echo "\n✅ SUCCESS! AI is working correctly.\n\n";
        echo "Generated SQL:\n";
        echo $result['sql'] . "\n\n";
        echo "Explanation:\n";
        echo $result['explanation'] . "\n";
    } else {
        echo "\n❌ FAILED!\n";
        echo "Error: " . $result['error'] . "\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ EXCEPTION CAUGHT!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>
