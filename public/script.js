document.addEventListener('DOMContentLoaded', () => {
  // --- Speed Chart Setup ---
  const speedCtx = document.getElementById('speedChart').getContext('2d');
  const speedChart = new Chart(speedCtx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'Kecepatan Kereta',
        data: [],
        tension: 0.2,
        borderWidth: 2,
        fill: false,
        pointRadius: 4,
        pointBackgroundColor: []
      }]
    },
    options: {
      responsive: true,
      scales: {
        x: {
          type: 'time',
          time: {
            parser: 'YYYY-MM-DDTHH:mm:ss',
            unit: 'second',
            displayFormats: { second: 'HH:mm:ss' }
          },
          title: { display: true, text: 'Waktu' }
        },
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Kecepatan' }
        }
      },
      plugins: {
        legend: { position: 'top' }
      }
    }
  });

  // --- Main update loop ---
  async function updateDashboard() {
    // 1) Update lampu & positions
    try {
      const res = await fetch('get_status.php', { cache: 'no-store' });
      const data = await res.json();

      document.getElementById('last-update').textContent =
        new Date().toLocaleString('id-ID');
      document.getElementById('route-info').textContent = data.route;

      updateTrainStatus(data.trains);
      updateLights(data.lights);
    } catch (err) {
      console.error('Status fetch error:', err);
    }

    // 2) Fetch speed logs
    try {
      const resp = await fetch('api_supabase.php?mode=log_speed', { cache: 'no-store' });
      const speedLogs = await resp.json();

      // Prepare arrays
      const labels = speedLogs.map(e => e.created_at);
      const speeds = speedLogs.map(e => parseFloat(e.kecepatan));
      const colors = speedLogs.map(e => {
        switch (e.warna) {
          case 'hijau':  return 'green';
          case 'kuning': return 'gold';
          case 'merah':  return 'red';
          default:       return 'gray';
        }
      });

      // Update chart
      speedChart.data.labels = labels;
      speedChart.data.datasets[0].data = speeds;
      speedChart.data.datasets[0].pointBackgroundColor = colors;
      speedChart.update();
    } catch (err) {
      console.error('Speed fetch error:', err);
    }
  }

  // --- Helper functions ---
  function updateTrainStatus(trains) {
    document.querySelectorAll('.train-indicator').forEach(el =>
      el.classList.remove('running', 'parking')
    );
    if (trains.running) {
      document.getElementById('running-train-status').innerHTML =
        `<strong>${trains.running}</strong>`;
      const el = document.getElementById(`train-${trains.running.toLowerCase()}`);
      if (el) el.classList.add('running');
    } else {
      document.getElementById('running-train-status').textContent = 'Tidak terdeteksi';
    }
    if (trains.parking && trains.parking.length) {
      document.getElementById('parking-train-status').innerHTML =
        `<strong>${trains.parking.join(', ')}</strong>`;
      trains.parking.forEach(p => {
        const el = document.getElementById(`train-${p.toLowerCase()}`);
        if (el) el.classList.add('parking');
      });
    } else {
      document.getElementById('parking-train-status').textContent = 'Tidak terdeteksi';
    }
  }

  function updateLights(lights) {
    document.querySelectorAll('.light-box').forEach(box => box.innerHTML = '');
    Object.entries(lights).forEach(([loc, state]) => {
      const box = document.getElementById(`light-${loc.toLowerCase()}`);
      if (!box) return;
      ['red', 'yellow', 'green'].forEach(color => {
        const d = document.createElement('div');
        d.className = `light ${color}` + (state[color] ? ' active' : '');
        box.appendChild(d);
      });
    });
  }

  // Initial and polling
  updateDashboard();
  setInterval(updateDashboard, 5000);
});
