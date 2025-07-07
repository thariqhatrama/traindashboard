<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Supabase config
$supabase_url = 'https://yajtyhtfnbybeghfflxp.supabase.co';
$anon_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlhanR5aHRmbmJ5YmVnaGZmbHhwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTA3NDYwNjcsImV4cCI6MjA2NjMyMjA2N30.9V0gkxmrrTkZxAXF2k3wLCfoBCVn4NkGADRFjEraLE8';

// Read mode and optional limit
$mode  = $_GET['mode']  ?? '';
$limit = intval($_GET['limit'] ?? 1);

// Helper: create HTTP context with Supabase headers
function sb_context(string $method, $body = null) {
    global $anon_key;
    $headers = [
        "apikey: {$anon_key}",
        "Authorization: Bearer {$anon_key}"
    ];
    if ($body !== null) {
        $headers[] = "Content-Type: application/json";
    }
    return stream_context_create([
        'http' => [
            'method'  => strtoupper($method),
            'header'  => implode("\r\n", $headers),
            'content' => $body,
            'ignore_errors' => true,
        ]
    ]);
}

if ($mode === 'log_speed') {
    // GET latest speed entries
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $url = "{$supabase_url}/rest/v1/log_speed?"
             . "select=id,kecepatan,mode,warna,created_at"
             . "&order=created_at.desc"
             . "&limit={$limit}";

        $ctx  = sb_context('GET');
        $resp = file_get_contents($url, false, $ctx);
        if ($resp === false) {
            http_response_code(502);
            echo json_encode(['error' => 'Failed to fetch speed logs']);
        } else {
            echo $resp;
        }
        exit;
    }

    // POST a new speed entry
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        foreach (['kecepatan','mode','warna'] as $f) {
            if (!isset($input[$f])) {
                http_response_code(400);
                echo json_encode(['error' => "Missing required field: {$f}"]);
                exit;
            }
        }
        $payload = json_encode([
            'kecepatan'  => $input['kecepatan'],
            'mode'       => $input['mode'],
            'warna'      => $input['warna'],
            // 'created_at' will default to now() if table is set up
        ]);

        $url  = "{$supabase_url}/rest/v1/log_speed";
        $ctx  = sb_context('POST', $payload);
        $resp = file_get_contents($url, false, $ctx);
        if ($resp === false) {
            http_response_code(502);
            echo json_encode(['error' => 'Failed to insert speed log']);
        } else {
            echo $resp;
        }
        exit;
    }

    // Method not allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// If unknown mode
http_response_code(400);
echo json_encode(['error' => 'Invalid mode']);