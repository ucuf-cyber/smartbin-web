<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#0A0A0B">
<title>Login — SmartBin IoT</title>
<link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/fonts/fonts.css') ?>" rel="stylesheet">
<link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div id="loginScreen">
  <div class="login-box">
    <div class="text-center">
      <div class="login-logo"><i class="bi bi-trash3-fill"></i></div>
      <h4 class="fw-bold mb-0 display-font" style="color:var(--text);">SmartBin IoT</h4>
      <p class="text-muted small mb-4">Website Monitoring — Admin Panel</p>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
      <div class="alert alert-danger py-2 small"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('login/process') ?>" method="post">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold small">Username</label>
        <input type="text" name="username" class="form-control" placeholder="admin" autocomplete="username" required>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold small">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mt-2">Masuk</button>
      
    </form>
  </div>
</div>
</body>
</html>
