// script.js

document.addEventListener('DOMContentLoaded', () => {
  // --- Chart Kecepatan Kereta ---
  const speedCtx = document.getElementById('speedChart').getContext('2d');
  const speedChart = new Chart(speedCtx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'Kecepatan Kereta (km/h)',
        data: [],
        tension: 0.2,
        borderWidth: 2,
        fill: false
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
          title: { display: true, text: 'Kecepatan (km/h)' }
        }
      },
      plugins: {
        legend: { position: 'top' }
      }
    }
  });

  // --- Entry Point ---
  async function updateDashboard() {
    // (1) Update status lampu & train-indicator
    try {
      const res = await fetch('get_status.php', { cache: 'no-store' });
      const data = await res.json();

      // Last update time
      document.getElementById('last-update').textContent =
        new Date().toLocaleString('id-ID');

      // Route info
      document.getElementById('route-info').textContent = data.route;

      updateTrainStatus(data.trains);
      updateLights(data.lights);
    } catch (err) {
      console.error('Error fetching status:', err);
    }

    // (2) Update speed chart
    try {
      const resp = await fetch('api_supabase.php?mode=log_speed', { cache: 'no-store' });
      const speedLogs = await resp.json();

      const labels = speedLogs.map(entry => entry.timestamp);
      const speeds = speedLogs.map(entry => entry.speed);

      speedChart.data.labels = labels;
      speedChart.data.datasets[0].data = speeds;
      speedChart.update();
    } catch (err) {
      console.error('Error fetching speed data:', err);
    }
  }

  // --- Helpers ---
  function updateTrainStatus(trains) {
    // reset all indicators
    document.querySelectorAll('.train-indicator').forEach(el => {
      el.classList.remove('running', 'parking');
    });

    // running indicator
    if (trains.running) {
      document.getElementById('running-train-status').innerHTML =
        `<strong>${trains.running}</strong>`;
      const runEl = document.getElementById(`train-${trains.running.toLowerCase()}`);
      if (runEl) runEl.classList.add('running');
    } else {
      document.getElementById('running-train-status').textContent = 'Tidak terdeteksi';
    }

    // parking indicator(s)
    if (trains.parking && trains.parking.length) {
      document.getElementById('parking-train-status').innerHTML =
        `<strong>${trains.parking.join(', ')}</strong>`;
      trains.parking.forEach(p => {
        const parkEl = document.getElementById(`train-${p.toLowerCase()}`);
        if (parkEl) parkEl.classList.add('parking');
      });
    } else {
      document.getElementById('parking-train-status').textContent = 'Tidak terdeteksi';
    }
  }

  function updateLights(lights) {
    // clear all light-boxes
    document.querySelectorAll('.light-box').forEach(box => {
      box.innerHTML = '';
    });

    // render new lights
    Object.entries(lights).forEach(([loc, state]) => {
      const box = document.getElementById(`light-${loc.toLowerCase()}`);
      if (!box) return;

      ['red', 'yellow', 'green'].forEach(color => {
        const dot = document.createElement('div');
        dot.className = `light ${color}` + (state[color] ? ' active' : '');
        box.appendChild(dot);
      });
    });
  }

  // initial load and interval
  updateDashboard();
  setInterval(updateDashboard, 5000);
});
