document.addEventListener('DOMContentLoaded', () => {
  // --- DOM Elements ---
  const speedPanel = document.getElementById('speed-status');
  const modePanel = document.getElementById('speed-mode');
  const lastUpdate = document.getElementById('last-update');
  const routeInfo = document.getElementById('route-info');
  const runningTrainStatus = document.getElementById('running-train-status');
  const parkingTrainStatus = document.getElementById('parking-train-status');

  // Cache untuk optimasi
  let lastSpeedData = null;
  let lastStatusData = null;
  let activeParkingStations = new Set();

  async function updateDashboard() {
    // 1) Update train status & lights
    try {
      const res = await fetch('get_status.php', { cache: 'no-store' });
      const d = await res.json();
      
      // Perbarui hanya jika ada perubahan
      if (JSON.stringify(d) !== JSON.stringify(lastStatusData)) {
        lastStatusData = d;
        
        // Last update timestamp
        lastUpdate.textContent = new Date().toLocaleString('id-ID');
        
        // Route info
        routeInfo.textContent = d.route;
        
        updateTrainStatus(d.trains);
        updateLights(d.lights);
      }
    } catch (e) {
      console.error('Error fetching status:', e);
    }

    // 2) Update speed data
    try {
      // Ambil data kecepatan terbaru
      const speedRes = await fetch('get_status.php?mode=speed&limit=1', { cache: 'no-store' });
      const speedData = await speedRes.json();
      
      // Perbarui hanya jika ada data baru
      if (speedData.length > 0 && JSON.stringify(speedData[0]) !== JSON.stringify(lastSpeedData)) {
        lastSpeedData = speedData[0];
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
      }
    } catch (e) {
      console.error('Error fetching speed data:', e);
      speedPanel.textContent = 'Error';
      modePanel.textContent = '-';
    }
  }

function updateTrainStatus(trains) {
  // 1. Reset semua indikator running
  document.querySelectorAll('.train-indicator').forEach(el => {
    el.classList.remove('running');
  });
  
  // 2. Kereta berjalan
  if (trains.running) {
    runningTrainStatus.innerHTML = `<strong>${trains.running}</strong>`;
    const runningEl = document.getElementById(`train-${trains.running.toLowerCase()}`);
    if (runningEl) runningEl.classList.add('running');
  } else {
    runningTrainStatus.textContent = 'Tidak terdeteksi';
  }
  
  // 3. Kereta parkir - reset hanya indikator parkir
  const parkingIndicators = document.querySelectorAll('.train-indicator.parking');
  parkingIndicators.forEach(indicator => {
    indicator.classList.remove('parking');
  });
  
  // 4. Aktifkan indikator parkir untuk stasiun yang aktif
  if (trains.parking && trains.parking.length) {
    parkingTrainStatus.innerHTML = `<strong>${trains.parking.join(', ')}</strong>`;
    
    trains.parking.forEach(p => {
      const parkingEl = document.getElementById(`train-${p.toLowerCase()}`);
      if (parkingEl) {
        parkingEl.classList.add('parking');
        
        // Nonaktifkan running jika ada di stasiun yang sama
        if (parkingEl.classList.contains('running')) {
          parkingEl.classList.remove('running');
        }
      }
    });
  } else {
    parkingTrainStatus.textContent = 'Tidak terdeteksi';
  }
}

  function updateLights(lights) {
    // Render lampu untuk setiap lokasi
    Object.entries(lights).forEach(([loc, state]) => {
      const box = document.getElementById(`light-${loc.toLowerCase()}`);
      if (!box) return;
      
      // Kosongkan box sebelum menambahkan lampu baru
      box.innerHTML = '';
      
      // Tentukan warna lampu yang aktif
      let activeColor = '';
      if (state.red) activeColor = 'red';
      else if (state.yellow) activeColor = 'yellow';
      else if (state.green) activeColor = 'green';
      
      // Buat elemen lampu jika ada warna aktif
      if (activeColor) {
        const dot = document.createElement('div');
        dot.className = `light ${activeColor} active`;
        box.appendChild(dot);
      }
    });
  }

  // initial load & polling
  updateDashboard();
  setInterval(updateDashboard, 1000); // Pembaruan setiap 500ms
});