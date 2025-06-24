<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Monitoring Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <div class="status-card parking-train">
                <h3>KERETA PARKIR</h3>
                <div id="parking-train-status">Tidak terdeteksi</div>
            </div>
            <div class="status-card last-update">
                <h3>LAST UPDATE</h3>
                <div id="last-update">-</div>
            </div>
        </div>
        
        <!-- Grafik Log Aktivitas -->
        <div class="chart-container">
            <canvas id="activityChart"></canvas>
        </div>
    </div>
<script>
const SUPABASE_URL = 'https://yajtyhtfnbybeghfflxp.supabase.co';
const SUPABASE_API_KEY = 'YOUR_SUPABASE_ANON_KEY'; // Ganti dengan anon key Anda

// Ambil log terakhir
async function fetchLastTrainLog() {
    try {
        const response = await fetch(`${SUPABASE_URL}/rest/v1/train_logs?select=*&order=timestamp.desc&limit=1`, {
            headers: {
                apikey: SUPABASE_API_KEY,
                Authorization: `Bearer ${SUPABASE_API_KEY}`
            }
        });

        const data = await response.json();

        if (data.length > 0) {
            const log = data[0];
            document.getElementById('last-update').textContent = new Date(log.timestamp).toLocaleString();

            // Update indikator di CP atau STASIUN
            const targetId = `light-${log.checkpoint.toLowerCase()}`;
            const lightElement = document.getElementById(targetId);
            if (lightElement) {
                lightElement.style.backgroundColor = (log.status === 'merah') ? 'red' : 'green';
            }

            // Contoh tambahan: update status panel
            if (log.checkpoint.toLowerCase().includes('su') || log.checkpoint.toLowerCase().includes('ss')) {
                document.getElementById('parking-train-status').textContent = `Parkir di ${log.checkpoint}`;
            } else {
                document.getElementById('running-train-status').textContent = `Berjalan di ${log.checkpoint}`;
            }
        }
    } catch (error) {
        console.error('Gagal ambil log:', error);
    }
}

// Panggil saat halaman dimuat
fetchLastTrainLog();
setInterval(fetchLastTrainLog, 5000); // auto-refresh tiap 5 detik
</script>

    <script src="script.js"></script>
</body>
</html>
