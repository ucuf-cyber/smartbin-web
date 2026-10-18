<div class="glass-card p-3 mb-3 no-print">
  <form method="get" action="<?= base_url('history') ?>" class="row g-2 align-items-end">
    <div class="col-6 col-md-3">
      <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
      <input type="date" name="start_date" class="form-control form-control-sm" value="<?= html_escape($filter['start'] ?? '') ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
      <input type="date" name="end_date" class="form-control form-control-sm" value="<?= html_escape($filter['end'] ?? '') ?>">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label small fw-semibold mb-1">Jenis Sampah</label>
      <select name="waste_type" class="form-select form-select-sm">
        <option value="">Semua Jenis</option>
        <?php foreach (['Logam', 'Non-Logam', 'Unknown'] as $t): ?>
          <option value="<?= $t ?>" <?= ($filter['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
      <a href="<?= base_url('history') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
    </div>
  </form>
  <div class="d-flex gap-2 mt-3">
    <button class="btn btn-outline-success btn-sm" id="btnExportExcel"><i class="bi bi-file-earmark-excel-fill"></i> Export Excel (CSV)</button>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer-fill"></i> Print / Simpan PDF</button>
  </div>
</div>

<div class="glass-card p-3">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="historyTable">
      <thead>
        <tr><th>Tanggal</th><th>Jam</th><th>Jenis Sampah</th><th>Kapasitas Logam</th><th>Kapasitas Non-Logam</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data riwayat.</td></tr>
        <?php else: foreach ($logs as $log):
          [$d, $t] = explode(' ', $log->created_at);
          $badgeClass = $log->waste_type === 'Logam' ? 'badge-logam' : ($log->waste_type === 'Non-Logam' ? 'badge-nonlogam' : 'badge-unknown');
          $statusClass = $log->status === 'Online' ? 'text-bg-success' : 'text-bg-danger';
        ?>
          <tr>
            <td><?= html_escape($d) ?></td>
            <td><?= html_escape($t) ?></td>
            <td><span class="badge-waste <?= $badgeClass ?>"><?= html_escape($log->waste_type) ?></span></td>
            <td><b><?= (int) $log->capacity_logam ?>%</b></td>
            <td><b><?= (int) $log->capacity_non_logam ?>%</b></td>
            <td><span class="badge rounded-pill <?= $statusClass ?>"><?= html_escape($log->status) ?></span></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
