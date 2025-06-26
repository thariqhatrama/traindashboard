<?php
header('Content-Type: application/json');

// Get light status and train positions
$status = [
  'lights' => array_fill_keys($points, ['red'=>false,'yellow'=>false,'green'=>false]),
  'trains' => ['running'=>null,'parking'=>[]],
  'route'  => '–',
  'logs'   => []    // nanti untuk grafik
];

// Get latest light status (simplified logic)
$checkpoints = ['SU', 'SS', 'CP1', 'CP2', 'CP3', 'CP4', 'CP5'];
foreach ($checkpoints as $cp) {
    $status['lights'][$cp] = [
        'red' => rand(0, 1) > 0.7, // Simulated status
        'yellow' => rand(0, 1) > 0.7,
        'green' => rand(0, 1) > 0.7
    ];
}

// Get train positions from database
$query = "SELECT * FROM train_logs 
          ORDER BY timestamp DESC 
          LIMIT 20";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status['logs'][] = [
            'checkpoint' => $row['checkpoint'],
            'status' => $row['status'],
            'timestamp' => $row['timestamp']
        ];
        
        // Determine train positions (simplified logic)
        if ($row['status'] === 'DETECTING') {
            if ($row['checkpoint'] === 'SU' || $row['checkpoint'] === 'SS') {
                $status['trains']['parking'] = "Peron " . 
                    ($row['checkpoint'] === 'SU' ? 'Utama' : 'Sekunder');
            } else {
                $status['trains']['running'] = "CP" . substr($row['checkpoint'], 2);
            }
        }
    }
}

echo json_encode($status);
