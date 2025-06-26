document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('activityChart').getContext('2d');
  const activityChart = new Chart(ctx, {
    type: 'line',
    data: { labels: [], datasets: [{ label: 'Aktivitas Kereta', data: [], tension:0.3, fill:true }] },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero:true, title:{display:true,text:'Jumlah Aktivitas'} },
        x: { title:{display:true,text:'Waktu'} }
      }
    }
  });

  async function updateDashboard() {
    try {
      const res = await fetch('get_status.php', { cache:'no-store' });
      const d   = await res.json();

      // Last update
      document.getElementById('last-update').textContent =
        new Date().toLocaleString('id-ID');

      // Route
      document.getElementById('route-info').textContent = d.route;

      updateTrainStatus(d.trains);
      updateLights(d.lights);
      updateChart(activityChart, d.logs);

    } catch (e) {
      console.error('Error:', e);
    }
  }

  function updateTrainStatus(trains) {
    // reset
    document.querySelectorAll('.train-indicator').forEach(el => {
      el.classList.remove('running','parking');
    });
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
    if (trains.parking.length) {
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
    Object.entries(lights).forEach(([loc,sts]) => {
      const box = document.getElementById(`light-${loc.toLowerCase()}`);
      if (!box) return;
      ['red','yellow','green'].forEach(color => {
        const dot = document.createElement('div');
        dot.className = `light ${color}${sts[color] ? ' active' : ''}`;
        box.appendChild(dot);
      });
    });
  }

  function updateChart(chart, logs) {
    const now = new Date(), labels = [], data = [];
    // 12 titik tiap 5 menit = 1 jam
    for (let i=11;i>=0;i--) {
      const t = new Date(now - i*5*60000);
      labels.push(t.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}));
    }
    labels.forEach(label => {
      const [h,m] = label.split(':');
      const date = now.toISOString().split('T')[0];
      const prefix = `${date}T${h.padStart(2,'0')}:${m}`;
      const cnt = logs.filter(r => r.timestamp.startsWith(prefix)).length;
      data.push(cnt);
    });
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.update();
  }

  // loop
  updateDashboard();
  setInterval(updateDashboard, 5000);
});
