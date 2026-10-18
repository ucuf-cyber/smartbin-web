<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3">
    <div class="glass-card stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:var(--lime);"><i class="bi bi-wifi"></i></div>
      <div>
        <div class="stat-value" id="dashStatus"><?= html_escape($summary['status']) ?></div>
        <div class="stat-label">Status Perangkat</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="glass-card stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:var(--orange);"><i class="bi bi-magnet-fill"></i></div>
      <div>
        <div class="stat-value"><span id="dashCapacityLogam" data-countup="<?= (int) $summary['capacity_logam'] ?>">0</span>%</div>
        <div class="stat-label">Kapasitas Bin Logam</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="glass-card stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:var(--lime); color:#0A0A0B;"><i class="bi bi-recycle"></i></div>
      <div>
        <div class="stat-value"><span id="dashCapacityNonLogam" data-countup="<?= (int) $summary['capacity_non_logam'] ?>">0</span>%</div>
        <div class="stat-label">Kapasitas Bin Non-Logam</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="glass-card stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:var(--amber); color:#0A0A0B;"><i class="bi bi-recycle"></i></div>
      <div>
        <div class="stat-value" style="font-size:1.1rem;" id="dashJenis"><?= html_escape($summary['waste_type']) ?></div>
        <div class="stat-label">Jenis Terakhir</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="glass-card stat-card d-flex align-items-center gap-3">
      <div class="stat-icon" style="background:var(--surface-3); color:var(--text);"><i class="bi bi-clock"></i></div>
      <div>
        <div class="stat-value" style="font-size:1.05rem;" id="dashUpdate"><?= html_escape($summary['last_update'] ?? '-') ?></div>
        <div class="stat-label">Update Terakhir</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="glass-card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:var(--text);"><i class="bi bi-magnet-fill" style="color:var(--orange);"></i> Bin Logam</span>
        <span class="fw-bold" id="dashCapacityLogamLabel" style="color:var(--lime);">—</span>
      </div>
      <div class="progress">
        <div class="progress-bar" id="dashProgressBarLogam" role="progressbar" style="width:0%;"></div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="glass-card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:var(--text);"><i class="bi bi-recycle" style="color:var(--lime);"></i> Bin Non-Logam</span>
        <span class="fw-bold" id="dashCapacityNonLogamLabel" style="color:var(--lime);">—</span>
      </div>
      <div class="progress">
        <div class="progress-bar" id="dashProgressBarNonLogam" role="progressbar" style="width:0%;"></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-7">
    <div class="glass-card p-3 h-100">
      <div class="fw-semibold small mb-2 d-flex align-items-center gap-2" style="color:var(--text);">
        <i class="bi bi-graph-up-arrow" style="color:var(--lime);"></i> Tren Kapasitas Tong (Real-Time)
      </div>
      <canvas id="chartCapacity" height="180"></canvas>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="glass-card p-3 h-100">
      <div class="fw-semibold small mb-2 d-flex align-items-center gap-2" style="color:var(--text);">
        <i class="bi bi-bar-chart-fill" style="color:var(--orange);"></i> Logam vs Non-Logam
      </div>
      <canvas id="chartActivity" height="180"></canvas>
    </div>
  </div>
</div>

<div class="glass-card p-3">
  <div class="fw-semibold small mb-3 d-flex align-items-center gap-2" style="color:var(--text);">
    <i class="bi bi-activity" style="color:var(--lime);"></i> Aktivitas Terbaru
  </div>
  <div id="timelineList">
    <div class="text-muted small text-center py-3">Memuat data...</div>
  </div>
</div>

<script>
  window.__SMARTBIN_INIT__ = {
    page: "dashboard",
    trend: <?= json_encode($summary['trend']) ?>,
    activity: <?= json_encode($summary['activity']) ?>,
    warning_limit: <?= (int) $settings->warning_limit ?>,
    critical_limit: <?= (int) $settings->critical_limit ?>
  };
</script>
