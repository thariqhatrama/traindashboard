<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Monitoring Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>🚆 Railway Monitoring System</h1>
        
        <!-- Peta Jalur Kereta -->
        <div class="track-map">
        <div class="station" id="station">
            <h3>STASIUN</h3>
            <div class="platforms">
            <div class="platform main" id="platform-main">
                <h4>Peron Utama (SU)</h4>
                <div class="light-box" id="light-su"></div>
                <div class="train-indicator" id="train-su"></div>
            </div>
            <div class="platform secondary" id="platform-secondary">
                <h4>Peron Sekunder (SS)</h4>
                <div class="light-box" id="light-ss"></div>
                <div class="train-indicator" id="train-ss"></div>
            </div>
            </div>
        </div>
        
        <div class="track">
            <?php for($i=1; $i<=5; $i++): ?>
            <div class="track-segment">
            <div class="checkpoint" id="cp<?= $i ?>">
                <h4>CP<?= $i ?></h4>
                <div class="light-box" id="light-cp<?= $i ?>"></div>
                <div class="train-indicator" id="train-cp<?= $i ?>"></div>
            </div>
            <div class="track-line"></div>
            </div>
            <?php endfor; ?>
        </div>
        </div>
        
        <!-- Panel Status -->
        <div class="status-panel">
        <div class="status-card running-train">
            <h3>KERETA BERJALAN</h3>
            <div id="running-train-status">Tidak terdeteksi</div>
        </div>
        <div class="status-card route-info">
            <h3>Jalur Kereta Menuju</h3>
            <div id="route-info">–</div>
        </div>
        <div class="status-card parking-train">
            <h3>KERETA PARKIR</h3>
            <div id="parking-train-status">Tidak terdeteksi</div>
        </div>
        <div class="status-card speed-status-card">
            <h3>KECEPATAN</h3>
            <div id="speed-status">-</div>
        </div>
        <div class="status-card speed-mode-card">
            <h3>MODE</h3>
            <div id="speed-mode">-</div>
        </div>
        <div class="status-card speed-color-card">
            <h3>WARNA</h3>
            <div id="speed-color">-</div>
        </div>
        <div class="status-card last-update">
            <h3>LAST UPDATE</h3>
            <div id="last-update">-</div>
        </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
