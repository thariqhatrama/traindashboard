document.addEventListener('DOMContentLoaded', function() {
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
            
            // Update last update time
            document.getElementById('last-update').textContent = 
                new Date().toLocaleTimeString();
            
            // Update train status
            updateTrainStatus(data.trains);
            
            // Update light signals
            updateLights(data.lights);
            
            // Update chart
            updateChart(activityChart, data.logs);
            
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    }

    function updateTrainStatus(trains) {
        const runningEl = document.getElementById('running-train-status');
        const parkingEl = document.getElementById('parking-train-status');
        
        // Reset all indicators
        document.querySelectorAll('.train-indicator').forEach(el => {
            el.className = 'train-indicator';
        });
        
        if (trains.running) {
            runningEl.innerHTML = `<strong>${trains.running}</strong>`;
            const runningLocation = trains.running.replace(/\s+/g, '-').toLowerCase();
            document.getElementById(`train-${runningLocation}`).classList.add('running');
        } else {
            runningEl.textContent = 'Tidak terdeteksi';
        }
        
        if (trains.parking) {
            parkingEl.innerHTML = `<strong>${trains.parking}</strong>`;
            const parkingLocation = trains.parking.replace(/\s+/g, '-').toLowerCase();
            document.getElementById(`train-${parkingLocation}`).classList.add('parking');
        } else {
            parkingEl.textContent = 'Tidak terdeteksi';
        }
    }

    function updateLights(lights) {
        // Reset all lights
        document.querySelectorAll('.light').forEach(light => {
            light.classList.remove('active');
        });
        
        // Activate current lights
        for (const [location, status] of Object.entries(lights)) {
            const prefix = location.startsWith('CP') ? 'cp' : location.toLowerCase();
            const lightBox = document.getElementById(`light-${prefix}`);
            
            // Clear previous lights
            lightBox.innerHTML = '';
            
            // Create new lights based on status
            ['red', 'yellow', 'green'].forEach(color => {
                const light = document.createElement('div');
                light.className = `light ${color} ${status[color] ? 'active' : ''}`;
                lightBox.appendChild(light);
            });
        }
    }

    function updateChart(chart, logs) {
        const now = new Date();
        const labels = [];
        const data = [];
        
        // Generate last 12 time points (5 min intervals)
        for (let i = 11; i >= 0; i--) {
            const time = new Date(now - i * 5 * 60000);
            labels.push(time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
            
            const timeStr = time.toISOString().slice(0, 16).replace('T', ' ');
            const count = logs.filter(log => log.timestamp.startsWith(timeStr)).length;
            data.push(count);
        }
        
        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        chart.update();
    }
});