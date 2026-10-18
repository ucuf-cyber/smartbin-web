<div class="d-flex align-items-center gap-2 mb-3">
  <span id="liveDot"></span>
  <span class="small text-muted">Data diperbarui otomatis (AJAX polling ke <code>/api/latest</code>) setiap <b style="color:var(--lime);"><?= (int) $settings->interval_send ?></b> detik</span>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="row g-3">
      <div class="col-6">
        <div class="glass-card p-3 text-center h-100 d-flex flex-column justify-content-center">
          <div class="gauge-wrap gauge-wrap-sm mb-2">
            <svg viewBox="0 0 200 200">
              <circle class="gauge-bg" cx="100" cy="100" r="82"></circle>
              <circle class="gauge-fill" id="gaugeFillLogam" cx="100" cy="100" r="82"
                      stroke="#FF8A3D" stroke-dasharray="515.2" stroke-dashoffset="515.2"></circle>
            </svg>
            <div class="gauge-center">
              <div class="gauge-value" id="monCapacityLogamText" style="color:var(--orange);"><?= (int) ($latest->capacity_logam ?? 0) ?>%</div>
              <div class="gauge-label" id="monCapacityLogamLabel">—</div>
            </div>
          </div>
          <div class="small fw-semibold" style="color:var(--text);"><i class="bi bi-magnet-fill"></i> Bin Logam</div>
        </div>
      </div>
      <div class="col-6">
        <div class="glass-card p-3 text-center h-100 d-flex flex-column justify-content-center">
          <div class="gauge-wrap gauge-wrap-sm mb-2">
            <svg viewBox="0 0 200 200">
              <circle class="gauge-bg" cx="100" cy="100" r="82"></circle>
              <circle class="gauge-fill" id="gaugeFillNonLogam" cx="100" cy="100" r="82"
                      stroke="#C6FF3E" stroke-dasharray="515.2" stroke-dashoffset="515.2"></circle>
            </svg>
            <div class="gauge-center">
              <div class="gauge-value" id="monCapacityNonLogamText" style="color:var(--lime);"><?= (int) ($latest->capacity_non_logam ?? 0) ?>%</div>
              <div class="gauge-label" id="monCapacityNonLogamLabel">—</div>
            </div>
          </div>
          <div class="small fw-semibold" style="color:var(--text);"><i class="bi bi-recycle"></i> Bin Non-Logam</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="row g-3">
      <div class="col-6">
        <div class="glass-card stat-card">
          <div class="stat-label mb-1">Status Online</div>
          <div class="stat-value" id="monStatus" style="font-size:1.25rem;"><?= html_escape($latest->status ?? 'Offline') ?></div>
        </div>
      </div>
      <div class="col-6">
        <div class="glass-card stat-card">
          <div class="stat-label mb-1">Status Tong</div>
          <div class="stat-value" id="monLid" style="font-size:1.25rem;"><?= ($latest->lid ?? 'Closed') === 'Open' ? 'Terbuka' : 'Tertutup' ?></div>
        </div>
      </div>
      <div class="col-6">
        <div class="glass-card stat-card">
          <div class="stat-label mb-1">Jenis Terakhir</div>
          <div class="stat-value" id="monJenis" style="font-size:1.25rem;"><?= html_escape($latest->waste_type ?? '-') ?></div>
        </div>
      </div>
      <div class="col-6">
        <div class="glass-card stat-card">
          <div class="stat-label mb-1">Update Terakhir</div>
          <div class="stat-value" id="monUpdate" style="font-size:1rem;"><?= html_escape($latest->created_at ?? '-') ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="glass-card p-3 mt-3">
  <div class="fw-semibold mb-2 d-flex align-items-center gap-2" style="color:var(--text);">
    <i class="bi bi-info-circle" style="color:var(--lime);"></i> Sumber Data
  </div>
  <p class="text-muted small mb-0">
    Halaman ini menampilkan data terbaru dari tabel <code>sensor_logs</code> yang dikirim NodeMCU
    melalui endpoint <code>POST /api/send_data</code>. Gauge di atas otomatis diperbarui via AJAX
    ke <code>GET /api/latest</code> tanpa reload halaman.
  </p>
</div>

<script>
  window.__SMARTBIN_INIT__ = {
    page: "monitoring",
    interval_send: <?= (int) $settings->interval_send ?>,
    warning_limit: <?= (int) $settings->warning_limit ?>,
    critical_limit: <?= (int) $settings->critical_limit ?>
  };
</script>
