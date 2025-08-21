<?php
require_once __DIR__ . '/includes/config.php';
$timeout = isset($_GET['timeout']) ? true : false;
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="grid">
  <div class="card">
    <h2>Welcome</h2>
    <?php if ($timeout): ?>
      <p style="color: #ffb3b3;">Your session has expired. Please log in again.</p>
    <?php endif; ?>
    <p>Please Log in</p>
    <div class="actions">
      <a class="btn" href="login.php">Login</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
