
<?php
// Cek parameter dari ESP32 atau Web
$checkpoint = $_GET['checkpoint'] ?? null;
$status = $_GET['status'] ?? null;

if (!$checkpoint || !$status) {
    http_response_code(400);
    echo "Missing 'checkpoint' or 'status' parameter.";
    exit;
}

// Supabase config
$supabase_url = 'https://yajtyhtfnbybeghfflxp.supabase.co';
$anon_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlhanR5aHRmbmJ5YmVnaGZmbHhwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTA3NDYwNjcsImV4cCI6MjA2NjMyMjA2N30.9V0gkxmrrTkZxAXF2k3wLCfoBCVn4NkGADRFjEraLE8'; // Ganti dengan anon/public key Anda

// Data yang dikirim ke Supabase
$data = [
    'checkpoint' => $checkpoint,
    'status' => $status
];

// Setup HTTP request
$options = [
    'http' => [
        'header' => [
            "Content-Type: application/json",
            "apikey: $anon_key",
            "Authorization: Bearer $anon_key"
        ],
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

// Kirim ke REST endpoint Supabase
$context = stream_context_create($options);
$response = file_get_contents("$supabase_url/rest/v1/train_logs", false, $context);

// Cek hasil
if ($response === FALSE) {
    http_response_code(500);
    echo "Failed to send data to Supabase.";
} else {
    echo "✅ Data sent successfully to Supabase!";
}
?>
