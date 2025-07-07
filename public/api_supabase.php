<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Supabase config
$supabase_url = 'https://yajtyhtfnbybeghfflxp.supabase.co/rest/v1/log_speed';
$anon_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlhanR5aHRmbmJ5YmVnaGZmbHhwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTA3NDYwNjcsImV4cCI6MjA2NjMyMjA2N30.9V0gkxmrrTkZxAXF2k3wLCfoBCVn4NkGADRFjEraLE8';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['kecepatan']) || !isset($data['mode'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Prepare Supabase request
$options = [
    'http' => [
        'header'  => 
            "Content-Type: application/json\r\n" .
            "apikey: $anon_key\r\n" .
            "Prefer: return=minimal",
        'method'  => 'POST',
        'content' => json_encode([
            'kecepatan' => $data['kecepatan'],
            'mode' => $data['mode'],
            'warna' => $data['warna'] ?? null
        ])
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($supabase_url, false, $context);

if ($response === FALSE) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Supabase connection failed']);
} else {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Data logged successfully']);
}
?>