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
<div class="container">
  <div class="signup-container">
    <div class="signup-card">
      <div class="signup-header">
        <h1>Create Account</h1>
        <p>Join our team and start managing tasks efficiently</p>
      </div>
      
      <?php if($msg): ?>
        <div class="alert alert-error"><?php echo esc($msg); ?></div>
      <?php endif; ?>
      
      <?php if($ok): ?>
        <div class="alert alert-success"><?php echo esc($ok); ?></div>
      <?php endif; ?>
      
      <form method="post" class="signup-form">
        <div class="form-row">
          <label for="full_name">Full Name*</label>
          <input type="text" id="full_name" name="full_name" required placeholder="Enter your full name">
        </div>
        
        <div class="form-row">
          <label for="email">Email Address*</label>
          <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>
        
        <div class="form-row">
          <label for="password">Password*</label>
          <input type="password" id="password" name="password" required placeholder="Create a password">
        </div>
        
        <div class="form-row">
          <label for="department">Department</label>
          <input type="text" id="department" name="department" placeholder="Enter your department">
        </div>
        
        <div class="form-row">
          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" placeholder="Enter your phone number">
        </div>
        
        <div class="form-row">
          <label for="role">Role</label>
          <select id="role" name="role">
            <option value="employee">Employee</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        
        <div class="button-group">
          <button type="submit" class="btn btn-primary">Create Account</button>
          <a href="index.php" class="btn btn-secondary">Back to Login</a>
        </div>
      </form>
      
      <div class="signup-footer">
        <p>Already have an account? <a href="index.php">Sign in here</a></p>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
