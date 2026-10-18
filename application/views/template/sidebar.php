<aside id="sidebar">
  <div class="brand">
    <div class="brand-icon"><i class="bi bi-trash3-fill"></i></div>
    <div>
      <div class="fw-bold display-font" style="font-size:1rem; color:var(--text);">SmartBin</div>
      <div class="small" style="opacity:.55;">Monitoring System</div>
    </div>
  </div>
  <nav class="nav flex-column pt-2">
    <a class="nav-link <?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
      <i class="bi bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a class="nav-link <?= ($active_menu ?? '') === 'monitoring' ? 'active' : '' ?>" href="<?= base_url('monitoring') ?>">
      <i class="bi bi-broadcast"></i> Monitoring
    </a>
    <a class="nav-link <?= ($active_menu ?? '') === 'history' ? 'active' : '' ?>" href="<?= base_url('history') ?>">
      <i class="bi bi-clock-history"></i> Riwayat
    </a>
    <a class="nav-link <?= ($active_menu ?? '') === 'settings' ? 'active' : '' ?>" href="<?= base_url('settings') ?>">
      <i class="bi bi-gear-fill"></i> Pengaturan
    </a>
    <a class="nav-link" href="<?= base_url('logout') ?>">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </nav>
</aside>

<!-- Mobile bottom tab bar -->
<nav id="bottomNav">
  <a href="<?= base_url('dashboard') ?>" class="<?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>">
    <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
  </a>
  <a href="<?= base_url('monitoring') ?>" class="<?= ($active_menu ?? '') === 'monitoring' ? 'active' : '' ?>">
    <i class="bi bi-broadcast"></i><span>Monitor</span>
  </a>
  <a href="<?= base_url('history') ?>" class="<?= ($active_menu ?? '') === 'history' ? 'active' : '' ?>">
    <i class="bi bi-clock-history"></i><span>Riwayat</span>
  </a>
  <a href="<?= base_url('settings') ?>" class="<?= ($active_menu ?? '') === 'settings' ? 'active' : '' ?>">
    <i class="bi bi-gear-fill"></i><span>Setting</span>
  </a>
  <a href="<?= base_url('logout') ?>">
    <i class="bi bi-box-arrow-right"></i><span>Keluar</span>
  </a>
</nav>

<div id="mainContent">
  <div id="topbar">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-sm d-lg-none" id="btnToggleSidebar" style="background:var(--surface-3); color:var(--text); border-radius:12px;"><i class="bi bi-list"></i></button>
      <h6 class="mb-0 fw-bold display-font" style="color:var(--text); font-size:1.05rem;"><?= ucfirst($active_menu ?? '') === 'History' ? 'Riwayat' : (ucfirst($active_menu ?? '') === 'Settings' ? 'Pengaturan' : ucfirst($active_menu ?? '')) ?></h6>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span class="status-pill status-online" id="topbarDeviceStatus"><i class="bi bi-circle-fill" style="font-size:.5rem;"></i> Online</span>
      <div class="text-end d-none d-sm-block">
        <div class="fw-semibold small" style="color:var(--text);"><?= html_escape($this->session->userdata('username') ?? 'Admin') ?></div>
        <div class="text-muted" style="font-size:.72rem;" id="topbarClock">--:--:--</div>
      </div>
      <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;font-weight:700; background:linear-gradient(135deg,var(--lime),var(--orange)); color:#0A0A0B;">
        <?= strtoupper(substr($this->session->userdata('username') ?? 'A', 0, 1)) ?>
      </div>
    </div>
  </div>
  <div class="page-view">
