const SUPABASE_URL = "https://yajtyhtfnbybeghfflxp.supabase.co";
const SUPABASE_API_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlhanR5aHRmbmJ5YmVnaGZmbHhwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTA3NDYwNjcsImV4cCI6MjA2NjMyMjA2N30.9V0gkxmrrTkZxAXF2k3wLCfoBCVn4NkGADRFjEraLE8"; // Ganti dengan anon key Anda

const checkpoints = ["CP1", "CP2", "CP3", "CP4", "CP5", "SU", "SS"];

async function fetchLatestLogs() {
  try {
    const response = await fetch(`${SUPABASE_URL}/rest/v1/train_logs?select=*&order=timestamp.desc&limit=50`, {
      headers: {
        apikey: SUPABASE_API_KEY,
        Authorization: `Bearer ${SUPABASE_API_KEY}`
      }
    });

    const logs = await response.json();
    const latestStatus = {};

    // Ambil status terakhir dari masing-masing checkpoint
    for (const log of logs) {
      const cp = log.checkpoint.toUpperCase();
      if (!latestStatus[cp] && checkpoints.includes(cp)) {
        latestStatus[cp] = log.status.toLowerCase(); // merah / kuning / hijau
      }
    }

    // Reset warna
    checkpoints.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.className = "checkpoint";
    });

    // Terapkan warna lampu sesuai status
    Object.entries(latestStatus).forEach(([cp, status]) => {
      const el = document.getElementById(cp);
      if (el && ["merah", "kuning", "hijau"].includes(status)) {
        el.classList.add(status === "merah" ? "red" : status === "kuning" ? "yellow" : "green");
      }
    });

    // Tampilkan waktu update
    if (logs[0]) {
      document.getElementById("last-update").textContent =
        "Last update: " + new Date(logs[0].timestamp).toLocaleString();
    }

  } catch (err) {
    console.error("Gagal ambil data Supabase:", err);
  }
}

// Jalankan saat halaman dibuka, lalu auto-refresh
fetchLatestLogs();
setInterval(fetchLatestLogs, 5000);
