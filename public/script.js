document.addEventListener('DOMContentLoaded', () => {
  // --- DOM Elements ---
  const speedPanel = document.getElementById('speed-status');
  const modePanel = document.getElementById('speed-mode');
  const lastUpdate = document.getElementById('last-update');
  const routeInfo = document.getElementById('route-info');
  const runningTrainStatus = document.getElementById('running-train-status');
  const parkingTrainStatus = document.getElementById('parking-train-status');

  let lastSpeedData = null;
  let lastStatusData = null;

  async function updateDashboard() {
    try {
      const [statusRes, speedRes] = await Promise.all([
        fetch('get_status.php', { cache: 'no-store' }),
        fetch('get_status.php?mode=speed&limit=1', { cache: 'no-store' })
      ]);
      const d = await statusRes.json();
      const speedData = await speedRes.json();

      // Update status if changed
      if (JSON.stringify(d) !== JSON.stringify(lastStatusData)) {
        lastStatusData = d;
        lastUpdate.textContent = new Date().toLocaleString('id-ID');
        routeInfo.textContent = d.route;
        updateTrainStatus(d.trains);
        updateLights(d.lights);
      }

      // Update speed if changed
      if (speedData.length > 0 && JSON.stringify(speedData[0]) !== JSON.stringify(lastSpeedData)) {
        lastSpeedData = speedData[0];
        const entry = speedData[0];
        const rawSpeed = parseInt(entry.kecepatan, 10);
        speedPanel.textContent = getSpeedText(rawSpeed);
        modePanel.textContent = capitalize(entry.mode);
      }
    } catch (e) {
      console.error('Error updating dashboard:', e);
      speedPanel.textContent = 'Error';
      modePanel.textContent = '-';
    }
  }

  function getSpeedText(rawSpeed) {
    switch (rawSpeed) {
      case 255: return 'Kecepatan Penuh';
      case 200: return 'Kecepatan Tinggi';
      case 128: return 'Kecepatan Sedang';
      case 100: return 'Kecepatan Rendah';
      case 0:   return 'Berhenti';
      default:  return `Kecepatan: ${rawSpeed}`;
    }
  }

  function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '-';
  }

  function updateTrainStatus(trains) {
    // Reset all running indicators
    document.querySelectorAll('.train-indicator').forEach(el => el.classList.remove('running'));
    // Running train
    if (trains.running) {
      runningTrainStatus.innerHTML = `<strong>${trains.running}</strong>`;
      const runningEl = document.getElementById(`train-${trains.running.toLowerCase()}`);
      if (runningEl) runningEl.classList.add('running');
    } else {
      runningTrainStatus.textContent = 'Tidak terdeteksi';
    }
    // Reset parking indicators
    document.querySelectorAll('.train-indicator.parking').forEach(el => el.classList.remove('parking'));
    // Activate parking indicators
    if (trains.parking && trains.parking.length) {
      parkingTrainStatus.innerHTML = `<strong>${trains.parking.join(', ')}</strong>`;
      trains.parking.forEach(p => {
        const parkingEl = document.getElementById(`train-${p.toLowerCase()}`);
        if (parkingEl) {
          parkingEl.classList.add('parking');
          parkingEl.classList.remove('running');
        }
      });
    } else {
      parkingTrainStatus.textContent = 'Tidak terdeteksi';
    }
  }

  function updateLights(lights) {
    Object.entries(lights).forEach(([loc, state]) => {
      const box = document.getElementById(`light-${loc.toLowerCase()}`);
      if (!box) return;
      box.innerHTML = '';
      let activeColor = state.red ? 'red' : state.yellow ? 'yellow' : state.green ? 'green' : '';
      if (activeColor) {
        const dot = document.createElement('div');
        dot.className = `light ${activeColor} active`;
        box.appendChild(dot);
      }
    });
  }

  // Initial load & polling
  updateDashboard();
  setInterval(updateDashboard, 2000);
});