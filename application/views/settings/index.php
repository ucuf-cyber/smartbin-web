<div class="glass-card p-4" style="max-width:520px;">
  <h6 class="fw-bold mb-3 display-font" style="color:var(--text);">Konfigurasi Sistem</h6>

  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success py-2 small"><?= $this->session->flashdata('success') ?></div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger py-2 small"><?= $this->session->flashdata('error') ?></div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('settings/save') ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <div class="mb-3">
      <label class="form-label small fw-semibold">Batas Warning (%)</label>
      <input type="number" min="1" max="100" name="warning_limit" class="form-control" value="<?= (int) $settings->warning_limit ?>" required>
      <div class="form-text">Notifikasi peringatan akan muncul saat kapasitas mencapai nilai ini.</div>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Batas Kritis (%)</label>
      <input type="number" min="1" max="100" name="critical_limit" class="form-control" value="<?= (int) $settings->critical_limit ?>" required>
      <div class="form-text">Buzzer &amp; LED merah aktif saat kapasitas mencapai nilai ini.</div>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Delay Tutup (detik)</label>
      <input type="number" min="1" max="60" name="delay_close" class="form-control" value="<?= (int) $settings->delay_close ?>" required>
    </div>
    <div class="mb-4">
      <label class="form-label small fw-semibold">Interval Kirim Data (detik)</label>
      <input type="number" min="1" max="60" name="interval_send" class="form-control" value="<?= (int) $settings->interval_send ?>" required>
    </div>
    <button type="submit" class="btn btn-primary fw-semibold px-4"><i class="bi bi-check-lg"></i> Simpan Pengaturan</button>
  </form>
</div>
