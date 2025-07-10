document.addEventListener('DOMContentLoaded', () => {
  // --- DOM Elements ---
  const speedPanel = document.getElementById('speed-status');
  const modePanel = document.getElementById('speed-mode');
  const lastUpdate = document.getElementById('last-update');
  const routeInfo = document.getElementById('route-info');
  const runningTrainStatus = document.getElementById('running-train-status');
  const parkingTrainStatus = document.getElementById('parking-train-status');

  async function updateDashboard() {
    // 1) Update train status & lights
    try {
      const res = await fetch('get_status.php', { cache: 'no-store' });
      const d = await res.json();
      
      // Last update timestamp
      lastUpdate.textContent = new Date().toLocaleString('id-ID');
      
      // Route info
      routeInfo.textContent = d.route;
      
      updateTrainStatus(d.trains);
      updateLights(d.lights);
    } catch (e) {
      console.error('Error fetching status:', e);
    }

    // 2) Update speed data
    try {
      // Ambil data kecepatan terbaru
      const speedRes = await fetch('get_status.php?mode=speed&limit=1', { cache: 'no-store' });
      const speedData = await speedRes.json();
      
      if (speedData.length > 0) {
        const entry = speedData[0];
        const rawSpeed = parseInt(entry.kecepatan, 10);
        
        // Tampilkan status kecepatan
        let speedText;
        switch (rawSpeed) {
          case 255: speedText = 'Kecepatan Penuh'; break;
          case 200: speedText = 'Kecepatan Tinggi'; break;
          case 128: speedText = 'Kecepatan Sedang'; break;
          case 100: speedText = 'Kecepatan Rendah'; break;
          case   0: speedText = 'Berhenti'; break;
          default:  speedText = `Kecepatan: ${rawSpeed}`; break;
        }
        speedPanel.textContent = speedText;
        
        // Tampilkan mode
        modePanel.textContent = entry.mode.charAt(0).toUpperCase() + entry.mode.slice(1);
      } else {
        speedPanel.textContent = 'Tidak ada data';
        modePanel.textContent = '-';
      }
    } catch (e) {
      console.error('Error fetching speed data:', e);
      speedPanel.textContent = 'Error';
      modePanel.textContent = '-';
    }
  }

  function updateTrainStatus(trains) {
    // reset indicators
    document.querySelectorAll('.train-indicator').forEach(el =>
      el.classList.remove('running','parking')
    );
    // running
    if (trains.running) {
      document.getElementById('running-train-status').innerHTML =
        `<strong>${trains.running}</strong>`;
      const el = document.getElementById(`train-${trains.running.toLowerCase()}`);
      if (el) el.classList.add('running');
    } else {
      document.getElementById('running-train-status').textContent = 'Tidak terdeteksi';
    }
    // parking
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
    // clear all light boxes
    document.querySelectorAll('.light-box').forEach(box => {
      box.innerHTML = '';
    });
    // render new lights
    Object.entries(lights).forEach(([loc, state]) => {
      const box = document.getElementById(`light-${loc.toLowerCase()}`);
      if (!box) return;
      ['red','yellow','green'].forEach(color => {
        const dot = document.createElement('div');
        dot.className = `light ${color}` + (state[color] ? ' active' : '');
        box.appendChild(dot);
      });
    });
  }

  // initial load & polling
  updateDashboard();
  setInterval(updateDashboard, 1000);
});
