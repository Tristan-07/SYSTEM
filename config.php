<?php
// Configuration & Database Connection Handler
// Connects to Supabase via REST API

$supabase_url = 'https://kfbolysjloxvauqrxtye.supabase.co';
$supabase_key = 'sb_publishable_QRRGM5APc7vR1tU4i_LZQA_VblMrHgV';


$pdo = null;
$db_type = 'supabase_api';

// Helper function for Supabase REST API calls
function supabase_request($endpoint, $method = 'GET', $data = null) {
    global $supabase_url, $supabase_key, $supabase_service_key;
    
    $url = $supabase_url . $endpoint;
    $headers = [
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'data' => json_decode($response, true),
        'status' => $httpCode
    ];
}
?>
