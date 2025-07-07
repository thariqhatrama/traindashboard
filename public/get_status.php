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

// 6) Bangun lampu untuk RUNNING
$run = $status['trains']['running'];
if ($run) {
  // reset CP lamp
  foreach (['CP1','CP2','CP3','CP4','CP5'] as $cp) {
    $status['lights'][$cp] = ['red'=>false,'yellow'=>false,'green'=>false];
  }
  // red di current
  $status['lights'][$run]['red'] = true;
  // yellow di previous (circular)
  $seq = ['CP1','CP2','CP3','CP4','CP5'];
  $i   = array_search($run, $seq, true);
  $prev = ($i === 0 ? 'CP5' : $seq[$i-1]);
  $status['lights'][$prev]['yellow'] = true;
}

// 7) Bangun lampu untuk PARKING (SU/SS)
foreach ($status['trains']['parking'] as $cp) {
  $status['lights'][$cp] = ['red'=>false,'yellow'=>false,'green'=>true];
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
