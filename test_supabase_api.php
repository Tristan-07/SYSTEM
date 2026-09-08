<?php
// Test Supabase REST API Connection
require_once __DIR__ . '/config.php';

echo "Testing Supabase REST API Connection...\n\n";

// Test 1: List all equipment
echo "[TEST 1] Fetching equipment list...\n";
$result = supabase_request('/rest/v1/equipment?select=*', 'GET');
echo "HTTP Status: " . $result['status'] . "\n";
echo "Data: " . json_encode($result['data']) . "\n\n";

if ($result['status'] >= 200 && $result['status'] < 300) {
    echo "✓ Connection successful!\n";
} else {
    echo "✗ Connection failed. Please ensure:\n";
    echo "  1. You've run the SQL script in Supabase SQL Editor\n";
    echo "  2. The table 'equipment' exists in your Supabase database\n";
    echo "  3. RLS policies allow public access (or adjust the API key)\n";
}
?>
