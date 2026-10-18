(function () {
  "use strict";

  if (typeof Chart === "undefined") {
    console.error("[SmartBin] Chart.js gagal dimuat dari CDN — cek koneksi internet atau link CDN di template/footer.php. Grafik tidak akan muncul.");
  }
  if (typeof Swal === "undefined") {
    console.error("[SmartBin] SweetAlert2 gagal dimuat dari CDN — notifikasi warning/critical tidak akan muncul.");
  }

  const pad = n => String(n).padStart(2, "0");
  function fmtTime(d) { return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`; }

  /* ---------- Sidebar toggle (mobile) ---------- */
  const btnToggle = document.getElementById("btnToggleSidebar");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("sidebarOverlay");
  if (btnToggle && sidebar && overlay) {
    btnToggle.addEventListener("click", () => { sidebar.classList.toggle("show"); overlay.classList.toggle("show"); });
    overlay.addEventListener("click", () => { sidebar.classList.remove("show"); overlay.classList.remove("show"); });
  }

  /* ---------- Jam realtime di topbar ---------- */
  const clockEl = document.getElementById("topbarClock");
  if (clockEl) setInterval(() => { clockEl.textContent = fmtTime(new Date()); }, 1000);

  /* ---------- Count-up animation untuk angka besar ---------- */
  function countUp(el, target, duration = 900) {
    if (!el) return;
    const start = 0;
    const startTime = performance.now();
    function tick(now) {
      const progress = Math.min(1, (now - startTime) / duration);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(start + (target - start) * eased);
      if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  document.querySelectorAll("[data-countup]").forEach(el => {
    countUp(el, parseInt(el.dataset.countup || "0", 10));
  });

  /* ---------- Util kode warna kapasitas (sesuai BAB 7.7 PRD) ---------- */
  function capacityMeta(pct) {
    if (pct <= 0) return { color: "#C6FF3E", label: "Kosong" };
    if (pct <= 25) return { color: "#C6FF3E", label: "Normal" };
    if (pct <= 50) return { color: "#FFC93D", label: "Sedang" };
    if (pct <= 75) return { color: "#FF8A3D", label: "Hampir Penuh" };
    if (pct <= 99) return { color: "#FF6B3D", label: "Waspada" };
    return { color: "#FF4949", label: "Penuh" };
  }

  function setDeviceStatusPill(status) {
    const pill = document.getElementById("topbarDeviceStatus");
    if (!pill) return;
    const isOnline = status === "Online";
    pill.className = "status-pill " + (isOnline ? "status-online" : "status-offline");
    pill.innerHTML = `<i class="bi bi-circle-fill" style="font-size:.5rem;"></i> ${status}`;
  }

  /* ---------- Chart.js dark theme defaults ---------- */
  if (typeof Chart !== "undefined") {
    Chart.defaults.color = "#93959B";
    Chart.defaults.borderColor = "rgba(255,255,255,0.06)";
    Chart.defaults.font.family = "Inter, sans-serif";
  }

  const init = window.__SMARTBIN_INIT__ || {};

  /* =========================================================
     DASHBOARD — polling GET /api/summary setiap 5 detik
  ========================================================= */
  if (init.page === "dashboard") {
    let capacityChart, activityChart;
    let notifiedWarning = false, notifiedCritical = false;

    function initCharts(trend, activity) {
      const ctx1 = document.getElementById("chartCapacity").getContext("2d");
      const gradient = ctx1.createLinearGradient(0, 0, 0, 200);
      gradient.addColorStop(0, "rgba(198,255,62,0.35)");
      gradient.addColorStop(1, "rgba(198,255,62,0)");

      const gradient2 = ctx1.createLinearGradient(0, 0, 0, 200);
      gradient2.addColorStop(0, "rgba(255,138,61,0.35)");
      gradient2.addColorStop(1, "rgba(255,138,61,0)");

      capacityChart = new Chart(ctx1, {
        type: "line",
        data: {
          labels: trend.map(t => (t.created_at || "").split(" ")[1] || ""),
          datasets: [
            {
              label: "Logam (%)", data: trend.map(t => Number(t.capacity_logam)),
              borderColor: "#FF8A3D", backgroundColor: gradient2,
              fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 5,
              pointBackgroundColor: "#FF8A3D", borderWidth: 3
            },
            {
              label: "Non-Logam (%)", data: trend.map(t => Number(t.capacity_non_logam)),
              borderColor: "#C6FF3E", backgroundColor: gradient,
              fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 5,
              pointBackgroundColor: "#C6FF3E", borderWidth: 3
            }
          ]
        },
        options: {
          responsive: true, animation: { duration: 600, easing: "easeOutCubic" },
          scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + "%" }, grid: { color: "rgba(255,255,255,0.05)" } },
            x: { grid: { display: false } }
          },
          plugins: {
            legend: { labels: { color: "#93959B", usePointStyle: true, pointStyle: "circle" } },
            tooltip: { backgroundColor: "#1D1D20", borderColor: "rgba(255,255,255,0.1)", borderWidth: 1, padding: 10, cornerRadius: 10 }
          }
        }
      });

      const ctx2 = document.getElementById("chartActivity").getContext("2d");
      activityChart = new Chart(ctx2, {
        type: "bar",
        data: {
          labels: activity.length ? activity.map(a => a.tanggal) : ["Hari Ini"],
          datasets: [
            { label: "Logam", data: activity.length ? activity.map(a => Number(a.logam)) : [0], backgroundColor: "#FF8A3D", borderRadius: 8, maxBarThickness: 26 },
            { label: "Non-Logam", data: activity.length ? activity.map(a => Number(a.non_logam)) : [0], backgroundColor: "#C6FF3E", borderRadius: 8, maxBarThickness: 26 }
          ]
        },
        options: {
          responsive: true, animation: { duration: 600, easing: "easeOutCubic" },
          scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: "rgba(255,255,255,0.05)" } },
            x: { grid: { display: false } }
          },
          plugins: {
            legend: { labels: { color: "#93959B", usePointStyle: true, pointStyle: "circle" } },
            tooltip: { backgroundColor: "#1D1D20", borderColor: "rgba(255,255,255,0.1)", borderWidth: 1, padding: 10, cornerRadius: 10 }
          }
        }
      });
    }

    function renderTimeline(trend) {
      const wrap = document.getElementById("timelineList");
      if (!wrap) return;
      const items = trend.slice(-8).reverse();
      if (!items.length) {
        wrap.innerHTML = '<div class="text-muted small text-center py-3">Belum ada data.</div>';
        return;
      }
      wrap.innerHTML = items.map((t, i) => {
        const isLogam = t.waste_type === "Logam";
        const cap = Number(isLogam ? t.capacity_logam : t.capacity_non_logam);
        const color = isLogam ? "#FF8A3D" : (t.waste_type === "Non-Logam" ? "#C6FF3E" : "#56575C");
        const icon = isLogam ? "bi-magnet-fill" : (t.waste_type === "Non-Logam" ? "bi-recycle" : "bi-question-lg");
        const time = (t.created_at || "").split(" ")[1] || "";
        return `
          <div class="timeline-row" style="animation-delay:${i * 0.05}s;">
            <div class="timeline-icon" style="background:${color};"><i class="bi ${icon}"></i></div>
            <div class="timeline-track">
              <div class="timeline-fill" style="width:${Math.max(cap, 8)}%; background:${color};">${cap}%</div>
            </div>
            <div class="timeline-time">${time}</div>
          </div>`;
      }).join("");
    }

    function applyLatest(latest) {
      if (!latest) return;
      const capLogam = Number(latest.capacity_logam);
      const capNonLogam = Number(latest.capacity_non_logam);
      const metaLogam = capacityMeta(capLogam);
      const metaNonLogam = capacityMeta(capNonLogam);

      document.getElementById("dashStatus").textContent = latest.status;
      document.getElementById("dashCapacityLogam").textContent = capLogam;
      document.getElementById("dashCapacityNonLogam").textContent = capNonLogam;
      document.getElementById("dashJenis").textContent = latest.waste_type;
      document.getElementById("dashUpdate").textContent = latest.created_at;

      document.getElementById("dashCapacityLogamLabel").textContent = metaLogam.label;
      document.getElementById("dashCapacityLogamLabel").style.color = metaLogam.color;
      const barLogam = document.getElementById("dashProgressBarLogam");
      barLogam.style.width = capLogam + "%";
      barLogam.style.background = metaLogam.color;

      document.getElementById("dashCapacityNonLogamLabel").textContent = metaNonLogam.label;
      document.getElementById("dashCapacityNonLogamLabel").style.color = metaNonLogam.color;
      const barNonLogam = document.getElementById("dashProgressBarNonLogam");
      barNonLogam.style.width = capNonLogam + "%";
      barNonLogam.style.background = metaNonLogam.color;

      setDeviceStatusPill(latest.status);

      const warningLimit = init.warning_limit || 80;
      const criticalLimit = init.critical_limit || 100;
      const bins = [{ label: "Logam", cap: capLogam }, { label: "Non-Logam", cap: capNonLogam }];
      const fullest = bins.reduce((a, b) => (b.cap > a.cap ? b : a));
      if (fullest.cap >= criticalLimit) {
        if (!notifiedCritical) {
          notifiedCritical = true;
          Swal.fire({ icon: "error", title: "Tong Penuh!", text: `Kapasitas bin ${fullest.label} mencapai ${fullest.cap}%. Segera lakukan pengangkutan.`, confirmButtonColor: "#FF4949", background: "#151517", color: "#F3F4F1" });
        }
      } else if (fullest.cap >= warningLimit) {
        if (!notifiedWarning) {
          notifiedWarning = true;
          Swal.fire({ icon: "warning", title: "Kapasitas Hampir Penuh", text: `Kapasitas bin ${fullest.label} sudah mencapai ${fullest.cap}%.`, confirmButtonColor: "#FF8A3D", background: "#151517", color: "#F3F4F1" });
        }
      } else { notifiedWarning = false; notifiedCritical = false; }
    }

    function pollSummary() {
      fetch(BASE_URL + "api/summary")
        .then(r => r.json())
        .then(res => {
          if (!res.success) return;
          applyLatest(res.latest);
          renderTimeline(res.trend || []);
          if (capacityChart) {
            capacityChart.data.labels = res.trend.map(t => (t.created_at || "").split(" ")[1] || "");
            capacityChart.data.datasets[0].data = res.trend.map(t => Number(t.capacity_logam));
            capacityChart.data.datasets[1].data = res.trend.map(t => Number(t.capacity_non_logam));
            capacityChart.update();
          }
          if (activityChart && res.activity.length) {
            activityChart.data.labels = res.activity.map(a => a.tanggal);
            activityChart.data.datasets[0].data = res.activity.map(a => Number(a.logam));
            activityChart.data.datasets[1].data = res.activity.map(a => Number(a.non_logam));
            activityChart.update();
          }
        })
        .catch(() => setDeviceStatusPill("Offline"));
    }

    if (typeof Chart !== "undefined") {
      initCharts(init.trend || [], init.activity || []);
    }
    renderTimeline(init.trend || []);
    setInterval(pollSummary, 5000);
  }

  /* =========================================================
     MONITORING — polling GET /api/latest setiap interval_send detik
  ========================================================= */
  if (init.page === "monitoring") {
    let notifiedWarning = false, notifiedCritical = false;
    const GAUGE_CIRCUMFERENCE = 2 * Math.PI * 82; // r=82

    function setGauge(gaugeId, textId, pct) {
      const fill = document.getElementById(gaugeId);
      if (!fill) return;
      const meta = capacityMeta(pct);
      const offset = GAUGE_CIRCUMFERENCE - (pct / 100) * GAUGE_CIRCUMFERENCE;
      fill.style.strokeDashoffset = offset;
      fill.style.stroke = meta.color;
      const valueEl = document.getElementById(textId);
      if (valueEl) valueEl.style.color = meta.color;
    }

    function applyLatest(latest) {
      if (!latest) return;
      const capLogam = Number(latest.capacity_logam);
      const capNonLogam = Number(latest.capacity_non_logam);
      const metaLogam = capacityMeta(capLogam);
      const metaNonLogam = capacityMeta(capNonLogam);

      document.getElementById("monStatus").textContent = latest.status;
      document.getElementById("monLid").textContent = latest.lid === "Open" ? "Terbuka" : "Tertutup";
      document.getElementById("monJenis").textContent = latest.waste_type;
      document.getElementById("monUpdate").textContent = latest.created_at;

      document.getElementById("monCapacityLogamText").textContent = capLogam + "%";
      document.getElementById("monCapacityLogamLabel").textContent = metaLogam.label;
      setGauge("gaugeFillLogam", "monCapacityLogamText", capLogam);

      document.getElementById("monCapacityNonLogamText").textContent = capNonLogam + "%";
      document.getElementById("monCapacityNonLogamLabel").textContent = metaNonLogam.label;
      setGauge("gaugeFillNonLogam", "monCapacityNonLogamText", capNonLogam);

      setDeviceStatusPill(latest.status);

      const warningLimit = init.warning_limit || 80;
      const criticalLimit = init.critical_limit || 100;
      const bins = [{ label: "Logam", cap: capLogam }, { label: "Non-Logam", cap: capNonLogam }];
      const fullest = bins.reduce((a, b) => (b.cap > a.cap ? b : a));
      if (fullest.cap >= criticalLimit) {
        if (!notifiedCritical) { notifiedCritical = true; Swal.fire({ icon: "error", title: "Tong Penuh!", text: `Kapasitas bin ${fullest.label} ${fullest.cap}%.`, confirmButtonColor: "#FF4949", background: "#151517", color: "#F3F4F1" }); }
      } else if (fullest.cap >= warningLimit) {
        if (!notifiedWarning) { notifiedWarning = true; Swal.fire({ icon: "warning", title: "Kapasitas Hampir Penuh", text: `Kapasitas bin ${fullest.label} ${fullest.cap}%.`, confirmButtonColor: "#FF8A3D", background: "#151517", color: "#F3F4F1" }); }
      } else { notifiedWarning = false; notifiedCritical = false; }
    }

    function pollLatest() {
      fetch(BASE_URL + "api/latest")
        .then(r => r.json())
        .then(res => { if (res.success) applyLatest(res.data); })
        .catch(() => setDeviceStatusPill("Offline"));
    }

    // Set gauge awal (dari server-render) begitu halaman dimuat
    const initialCapLogam = parseInt((document.getElementById("monCapacityLogamText")?.textContent || "0").replace("%", ""), 10) || 0;
    const initialCapNonLogam = parseInt((document.getElementById("monCapacityNonLogamText")?.textContent || "0").replace("%", ""), 10) || 0;
    setTimeout(() => {
      setGauge("gaugeFillLogam", "monCapacityLogamText", initialCapLogam);
      setGauge("gaugeFillNonLogam", "monCapacityNonLogamText", initialCapNonLogam);
    }, 200);

    const ms = Math.max(1, init.interval_send || 3) * 1000;
    setInterval(pollLatest, ms);
  }

  /* =========================================================
     RIWAYAT — export tabel ke CSV (dibuka Excel)
  ========================================================= */
  const btnExportExcel = document.getElementById("btnExportExcel");
  if (btnExportExcel) {
    btnExportExcel.addEventListener("click", () => {
      const rows = document.querySelectorAll("#historyTable tbody tr");
      let csv = "Tanggal,Jam,Jenis Sampah,Kapasitas Logam,Kapasitas Non-Logam,Status\n";
      rows.forEach(tr => {
        const cells = tr.querySelectorAll("td");
        if (cells.length < 6) return;
        const vals = Array.from(cells).map(td => td.innerText.replace(/,/g, " ").trim());
        csv += vals.join(",") + "\n";
      });
      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = "riwayat_smartbin.csv";
      link.click();
    });
  }
})();
