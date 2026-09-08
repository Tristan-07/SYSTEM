<?php
// Quick Supabase Connection Test
require_once __DIR__ . '/config.php';

echo "Testing Supabase Connection...\n";
echo "Database Type: " . $db_type . "\n";
echo "Connection Status: " . ($pdo ? "SUCCESS" : "FAILED") . "\n";

if ($pdo) {
    try {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM equipment");
        $count = $countStmt->fetchColumn();
        echo "Equipment Records: " . $count . "\n";
        
        $sample = $pdo->query("SELECT * FROM equipment LIMIT 1")->fetch();
        if ($sample) {
            echo "Sample Record: " . json_encode($sample) . "\n";
        }
    } catch (Exception $e) {
        echo "Query Error: " . $e->getMessage() . "\n";
    }
}
?>
