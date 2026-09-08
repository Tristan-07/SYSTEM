<?php
// Seed sample data into Supabase
require_once __DIR__ . '/config.php';

$seedData = [
    ['EQ-101', 'Digital Storage Oscilloscope 50MHz', 'Electronics', 15, 'Good', 'Electronics Lab 101', '2024-01-15'],
    ['EQ-102', 'Binocular Compound Microscope', 'Biology', 24, 'Good', 'Biology Lab 202', '2023-08-20'],
    ['EQ-103', 'Analytical Balance 0.1mg', 'Chemistry', 8, 'For Repair', 'Chemistry Lab 304', '2023-11-05'],
    ['EQ-104', 'High-Speed Centrifuge 4000 RPM', 'Biology', 5, 'Damaged', 'Biology Lab 202', '2022-04-12'],
    ['EQ-105', 'Function Generator 10MHz', 'Electronics', 12, 'Unserviceable', 'Electronics Lab 101', '2021-09-30'],
    ['EQ-106', 'UV-Vis Spectrophotometer', 'Chemistry', 4, 'Good', 'Chemistry Lab 304', '2024-03-10']
];

echo "Seeding sample data into Supabase...\n";

foreach ($seedData as $row) {
    $data = [
        'equipment_id' => $row[0],
        'equipment_name' => $row[1],
        'category' => $row[2],
        'quantity' => $row[3],
        'condition' => $row[4],
        'laboratory' => $row[5],
        'date_acquired' => $row[6]
    ];
    
    $result = supabase_request('/rest/v1/equipment', 'POST', $data);
    
    if ($result['status'] >= 200 && $result['status'] < 300) {
        echo "✓ Inserted: {$row[1]}\n";
    } else {
        echo "✗ Failed to insert: {$row[1]} - " . json_encode($result['data']) . "\n";
    }
}

echo "\nDone! Testing connection again...\n";
$result = supabase_request('/rest/v1/equipment?select=*', 'GET');
echo "Total records: " . count($result['data']) . "\n";
?>
