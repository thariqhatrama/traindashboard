<?php
header('Content-Type: application/json');

$anon_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlhanR5aHRmbmJ5YmVnaGZmbHhwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTA3NDYwNjcsImV4cCI6MjA2NjMyMjA2N30.9V0gkxmrrTkZxAXF2k3wLCfoBCVn4NkGADRFjEraLE8'; // Ganti dengan key dari Supabase
$supabase_url = 'https://yajtyhtfnbybeghfflxp.supabase.co';

// Inisialisasi data status
$status = [
    'lights' => [],
    'trains' => [
        'running' => null,
        'parking' => null
    ],
    'logs' => []
];

// Ambil log terakhir dari Supabase
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            "apikey: $anon_key",
            "Authorization: Bearer $anon_key"
        ]
    ]
]);

$response = file_get_contents("$supabase_url/rest/v1/train_logs?select=*&order=timestamp.desc&limit=20", false, $context);
$data = json_decode($response, true);

// Update log dan status
if ($data) {
    foreach ($data as $row) {
        $cp = strtoupper($row['checkpoint']);
        $st = strtoupper($row['status']);

        $status['logs'][] = [
            'checkpoint' => $cp,
            'status' => $st,
            'timestamp' => $row['timestamp']
        ];

        // Simulasi status lampu (jika ingin tetap dummy)
        $status['lights'][$cp] = [
            'red' => $st === 'MERAH',
            'yellow' => $st === 'KUNING',
            'green' => $st === 'HIJAU'
        ];

        // Deteksi posisi kereta (optional)
        if ($st === 'DETECTING') {
            if ($cp === 'SU' || $cp === 'SS') {
                $status['trains']['parking'] = 'Peron ' . ($cp === 'SU' ? 'Utama' : 'Sekunder');
            } else {
                $status['trains']['running'] = $cp;
            }
        }
    }
}

// Tambahkan titik yang belum ada (agar tidak error)
$all_points = ['SU', 'SS', 'CP1', 'CP2', 'CP3', 'CP4', 'CP5'];
foreach ($all_points as $cp) {
    if (!isset($status['lights'][$cp])) {
        $status['lights'][$cp] = [
            'red' => false,
            'yellow' => false,
            'green' => false
        ];
    }
}

echo json_encode($status);
