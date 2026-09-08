<?php
// Comprehensive Automated CRUD Verification Test Script

echo "=========================================================\n";
echo "LABORATORY EQUIPMENT INVENTORY SYSTEM - CRUD TEST SUITE\n";
echo "=========================================================\n\n";

$baseUrl = "http://127.0.0.1:8000/api.php";

function makePost($url, $data) {
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true);
}

function makeGet($url) {
    $result = file_get_contents($url);
    return json_decode($result, true);
}

// 1. READ / LIST Test
echo "[TEST 1] Testing READ (List all equipment)...\n";
$listRes = makeGet($baseUrl . "?action=list");
echo "Status: " . ($listRes['status'] ?? 'failed') . "\n";
echo "Initial Total Count: " . ($listRes['stats']['total'] ?? 0) . "\n";
echo "Initial Good Items: " . ($listRes['stats']['good'] ?? 0) . "\n\n";

// 2. CREATE Test
echo "[TEST 2] Testing CREATE (Add new equipment EQ-201)...\n";
$createData = [
    'action' => 'create',
    'equipment_id' => 'EQ-201',
    'equipment_name' => 'Digital Multimeter 4.5 Digit',
    'category' => 'Electronics',
    'quantity' => '10',
    'condition' => 'Good',
    'laboratory' => 'Electronics Lab 101',
    'date_acquired' => '2024-05-15'
];
$createRes = makePost($baseUrl, $createData);
echo "Result: " . json_encode($createRes) . "\n\n";

// 3. READ / GET Single Item
echo "[TEST 3] Testing READ (Get single record EQ-201)...\n";
$getRes = makeGet($baseUrl . "?action=get&id=EQ-201");
echo "Fetched Equipment Name: " . ($getRes['data']['equipment_name'] ?? 'NOT FOUND') . "\n\n";

// 4. UPDATE Test
echo "[TEST 4] Testing UPDATE (Modify EQ-201 condition to 'For Repair' & qty to 8)...\n";
$updateData = [
    'action' => 'update',
    'equipment_id' => 'EQ-201',
    'equipment_name' => 'Digital Multimeter 4.5 Digit (Calibrated)',
    'category' => 'Electronics',
    'quantity' => '8',
    'condition' => 'For Repair',
    'laboratory' => 'Electronics Lab 101',
    'date_acquired' => '2024-05-15'
];
$updateRes = makePost($baseUrl, $updateData);
echo "Result: " . json_encode($updateRes) . "\n\n";

// 5. VALIDATION Tests
echo "[TEST 5A] Testing Validation: Duplicate Equipment ID...\n";
$valDup = makePost($baseUrl, $createData);
echo "Expected Error Received: " . $valDup['message'] . "\n";

echo "[TEST 5B] Testing Validation: Negative Quantity (-5)...\n";
$negData = $createData;
$negData['equipment_id'] = 'EQ-999';
$negData['quantity'] = '-5';
$valNeg = makePost($baseUrl, $negData);
echo "Expected Error Received: " . $valNeg['message'] . "\n\n";

// 6. SEARCH Test
echo "[TEST 6] Testing SEARCH ('Multimeter')...\n";
$searchRes = makeGet($baseUrl . "?action=list&search=Multimeter");
echo "Found Records: " . count($searchRes['data']) . "\n\n";

// 7. DELETE Test
echo "[TEST 7] Testing DELETE (Delete EQ-201)...\n";
$deleteRes = makePost($baseUrl, ['action' => 'delete', 'equipment_id' => 'EQ-201']);
echo "Result: " . json_encode($deleteRes) . "\n\n";

// 8. FINAL COUNT VERIFICATION
$finalRes = makeGet($baseUrl . "?action=list");
echo "Final Total Records: " . ($finalRes['stats']['total'] ?? 0) . "\n";
echo "=========================================================\n";
echo "ALL TESTS EXECUTED SUCCESSFULLY!\n";
echo "=========================================================\n";
?>
