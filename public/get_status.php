<?php
header('Content-Type: application/json');

// Supabase REST config
$anon_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlhanR5aHRmbmJ5YmVnaGZmbHhwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTA3NDYwNjcsImV4cCI6MjA2NjMyMjA2N30.9V0gkxmrrTkZxAXF2k3wLCfoBCVn4NkGADRFjEraLE8'; // Ganti dengan key dari Supabase
$supabase_url = 'https://yajtyhtfnbybeghfflxp.supabase.co';

// --- ENDPOINT UNTUK DATA KECEPATAN ---
if (isset($_GET['mode']) && $_GET['mode'] === 'speed') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1;
    $url = "$supabase_url/rest/v1/log_speed?select=*&order=created_at.desc&limit=$limit";
    
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                "apikey: $anon_key",
                "Authorization: Bearer $anon_key"
            ]
        ]
    ]);
    
    $json = file_get_contents($url, false, $ctx);
    $speedData = json_decode($json, true);
    
    echo json_encode($speedData);
    exit;
}

// 1) Ambil 50 log terbaru
$ctx  = stream_context_create([
  'http' => [
    'method' => 'GET',
    'header' => [
      "apikey: $anon_key",
      "Authorization: Bearer $anon_key"
    ]
  ]
]);
$url  = "$supabase_url/rest/v1/train_logs?select=checkpoint,status,timestamp&order=timestamp.desc&limit=50";
$json = file_get_contents($url, false, $ctx);
$logs = json_decode($json, true);

// 2) Inisialisasi struktur
$points = ['SU','SS','CP1','CP2','CP3','CP4','CP5'];
$status = [
  'lights' => array_fill_keys($points, ['red'=>false,'yellow'=>false,'green'=>false]),
  'trains' => ['running'=>null,'parking'=>[]],
  'route'  => '–',
  'logs'   => []    // nanti untuk grafik
];

// 3) Simpan logs (terbaru dulu)
foreach ($logs as $r) {
  $status['logs'][] = [
    'checkpoint'=> strtoupper($r['checkpoint']),
    'status'    => strtoupper($r['status']),
    'timestamp' => $r['timestamp']
  ];
}

// 4) Tentukan RUNNING: first occurrence of CP1–CP5 in logs
foreach ($status['logs'] as $r) {
  if (in_array($r['checkpoint'], ['CP1','CP2','CP3','CP4','CP5'], true)) {
    $status['trains']['running'] = $r['checkpoint'];
    break;
  }
}

// NEW: Hapus CP5 jika SU/SS sudah mendeteksi kereta masuk
$latestCP = null;
$latestStation = null;
foreach ($status['logs'] as $log) {
  if ($log['status'] === 'DETECTING') {
    if (in_array($log['checkpoint'], ['CP1','CP2','CP3','CP4','CP5'], true) && !$latestCP) {
      $latestCP = $log['checkpoint'];
    }
    if (in_array($log['checkpoint'], ['SU','SS'], true) && !$latestStation) {
      $latestStation = $log['checkpoint'];
    }
  }
}
if ($latestCP === 'CP5' && $latestStation !== null) {
  $status['trains']['running'] = null;
}

// 5) Tentukan PARKING: untuk SU/SS, cek status terbaru masing-masing
//    cari first log per stasiun
$latestStation = [];
foreach ($status['logs'] as $r) {
  if (in_array($r['checkpoint'], ['SU','SS'], true) && !isset($latestStation[$r['checkpoint']])) {
    $latestStation[$r['checkpoint']] = $r['status'];
  }
  if (count($latestStation) === 2) break;
}
foreach ($latestStation as $cp=>$st) {
  if ($st === 'DETECTING') {
    $status['trains']['parking'][] = $cp;
  }
}

// 6) Tentukan skenario lampu berdasarkan posisi kereta
$scenario = -1;

// Tentukan skenario berdasarkan posisi kereta
if ($status['trains']['running']) {
    switch ($status['trains']['running']) {
        case 'CP1': $scenario = 0; break;
        case 'CP2': $scenario = 1; break;
        case 'CP3': $scenario = 2; break;
        case 'CP4': $scenario = 3; break;
        case 'CP5': $scenario = 4; break;
    }
} else {
    if (in_array('SU', $status['trains']['parking'])) {
        $scenario = 5; // SU
    }
    if (in_array('SS', $status['trains']['parking'])) {
        $scenario = 6; // SS
    }
    if (in_array('SU', $status['trains']['parking']) && 
        in_array('SS', $status['trains']['parking']) && 
        $status['trains']['running'] === 'CP3') {
        $scenario = 7; // SS SU CP3
    }
}

// 7) Atur lampu berdasarkan skenario
switch ($scenario) {
    case 0: // CP1
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['yellow'] = true;
        $status['lights']['CP1']['green'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['yellow'] = true;
        $status['lights']['CP4']['green'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    case 1: // CP2
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['yellow'] = true;
        $status['lights']['CP1']['green'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['yellow'] = true;
        $status['lights']['CP4']['green'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    case 2: // CP3
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['yellow'] = true;
        $status['lights']['CP1']['green'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['yellow'] = true;
        $status['lights']['CP4']['green'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    case 3: // CP4
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['yellow'] = true;
        $status['lights']['CP1']['green'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['yellow'] = true;
        $status['lights']['CP4']['green'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    case 4: // CP5
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['yellow'] = true;
        $status['lights']['CP1']['green'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['yellow'] = true;
        $status['lights']['CP4']['green'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    case 5: // SU
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['yellow'] = true;
        $status['lights']['CP1']['green'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['yellow'] = true;
        $status['lights']['CP4']['green'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    case 6: // SS
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['yellow'] = true;
        $status['lights']['CP1']['green'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['yellow'] = true;
        $status['lights']['CP4']['green'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    case 7: // SS SU CP3
        $status['lights']['SU']['red'] = true;
        $status['lights']['SS']['red'] = true;
        $status['lights']['CP1']['red'] = true;
        $status['lights']['CP2']['red'] = true;
        $status['lights']['CP3']['red'] = true;
        $status['lights']['CP4']['red'] = true;
        $status['lights']['CP5']['red'] = true;
        break;
    default:
        // Default: semua lampu mati
        foreach ($status['lights'] as $key => &$light) {
            $light = ['red' => false, 'yellow' => false, 'green' => false];
        }
}

// 8) Tentukan ROUTE
if (count($status['trains']['parking']) === 1 && $status['trains']['running']) {
  $p = $status['trains']['parking'][0];
  $status['route'] = ($p === 'SU') ? 'Peron Sekunder (SS)' : 'Peron Utama (SU)';
} elseif (count($status['trains']['parking']) === 2 && $status['trains']['running'] === 'CP3') {
  $status['route'] = 'Jalur tertutup';
}

// 9) Kembalikan JSON
echo json_encode($status);