<?php
require_once __DIR__ . '/includes/config.php';
$msg = ''; $ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $department = trim($_POST['department'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $role       = $_POST['role'] ?? 'employee';

    if ($full_name === '' || $email === '' || $password === '') {
        $msg = 'Please fill all required fields.';
    } else {
        $chk = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $chk->bind_param('s', $email);
        $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) {
            $msg = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $status = ($role === 'employee') ? 'pending' : 'active';
            $ins = $mysqli->prepare("INSERT INTO users (full_name, email, password_hash, role, status, department, phone) VALUES (?,?,?,?,?,?,?)");
            $ins->bind_param("sssssss", $full_name, $email, $hash, $role, $status, $department, $phone);
            if ($ins->execute()) {
                if ($role === 'employee') {
                    $ok = 'Registration submitted. Your account is pending Admin approval.';
                } else {
                    $ok = 'Registration successful. You can log in now.';
                }
            } else { $msg = 'Registration failed.'; }
        }
    }
}
include __DIR__ . '/partials/header.php';
?>
<div class="grid">
  <div class="card">
    <h2>Sign Up</h2>
    <?php if($msg): ?><p style="color:#ffb3b3;"><?php echo esc($msg); ?></p><?php endif; ?>
    <?php if($ok): ?><p style="color:#b3ffcc;"><?php echo esc($ok); ?></p><?php endif; ?>
    <form method="post" class="form">
      <div class="form-row"><label>Full Name*</label><input type="text" name="full_name" required></div>
      <div class="form-row"><label>Email*</label><input type="email" name="email" required></div>
      <div class="form-row"><label>Password*</label><input type="password" name="password" required></div>
      <div class="form-row"><label>Department</label><input type="text" name="department"></div>
      <div class="form-row"><label>Phone</label><input type="text" name="phone"></div>
      <div class="form-row"><label>Role</label><select name="role"><option value="employee">Employee</option><option value="manager">Manager</option><option value="admin">Admin</option></select></div>
      <button class="btn" type="submit">Create account</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
