document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Aktivitas Kereta',
                data: [],
                borderColor: '#3498db',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Aktivitas'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Waktu'
                    }
                }
            }
        }
    });

    // Update dashboard setiap 5 detik
    setInterval(updateDashboard, 5000);
    updateDashboard();

    async function updateDashboard() {
        try {
            const response = await fetch('get_status.php');
            const data = await response.json();

            // Update waktu
            document.getElementById('last-update').textContent =
                new Date().toLocaleString('id-ID');

            // Update status kereta
            updateTrainStatus(data.trains);

            // Update lampu per checkpoint
            updateLights(data.lights);

            // Update grafik aktivitas
            updateChart(activityChart, data.logs);

        } catch (error) {
            console.error('Gagal mengambil data dari get_status.php:', error);
        }
    }

    function updateTrainStatus(trains) {
        const runningEl = document.getElementById('running-train-status');
        const parkingEl = document.getElementById('parking-train-status');

        // Reset indikator kereta
        document.querySelectorAll('.train-indicator').forEach(el => {
            el.className = 'train-indicator';
        });

        if (trains.running) {
            runningEl.innerHTML = `<strong>${trains.running}</strong>`;
            const id = `train-${trains.running.toLowerCase()}`;
            const el = document.getElementById(id);
            if (el) el.classList.add('running');
        } else {
            runningEl.textContent = 'Tidak terdeteksi';
        }

        if (trains.parking) {
            parkingEl.innerHTML = `<strong>${trains.parking}</strong>`;
            const id = `train-${trains.parking.toLowerCase().includes('utama') ? 'su' : 'ss'}`;
            const el = document.getElementById(id);
            if (el) el.classList.add('parking');
        } else {
            parkingEl.textContent = 'Tidak terdeteksi';
        }
    }

    function updateLights(lights) {
        document.querySelectorAll('.light-box').forEach(box => {
            box.innerHTML = ''; // reset isi
        });

        for (const [location, status] of Object.entries(lights)) {
            const id = `light-${location.toLowerCase()}`;
            const box = document.getElementById(id);
            if (!box) continue;

            ['red', 'yellow', 'green'].forEach(color => {
                const div = document.createElement('div');
                div.className = `light ${color} ${status[color] ? 'active' : ''}`;
                box.appendChild(div);
            });
        }
    }

    function updateChart(chart, logs) {
        const now = new Date();
        const labels = [];
        const data = [];

        for (let i = 11; i >= 0; i--) {
            const time = new Date(now - i * 5 * 60000);
            const label = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            labels.push(label);

            const target = time.toISOString().slice(0, 16); // format: yyyy-MM-ddTHH:mm
            const count = logs.filter(log => log.timestamp.startsWith(target)).length;
            data.push(count);
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        chart.update();
    }
});
