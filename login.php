<?php
require_once __DIR__ . '/includes/config.php';

$msg = '';
if (isset($_GET['expired'])) {
    $msg = 'Your session expired. Please sign in again.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $mysqli->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (!password_verify($pass, $row['password_hash'])) {
            $msg = 'Invalid credentials';
        } elseif ($row['status'] !== 'active') {
            $msg = 'Your account is not active yet. If you are an Employee, please wait for Admin approval.';
        } else {
            $_SESSION['user'] = [
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'role' => $row['role']
            ];
            if ($row['role'] === 'admin') {
                header("Location: " . BASE_PATH . "admin_dashboard.php");
            } elseif ($row['role'] === 'manager') {
                header("Location: " . BASE_PATH . "manager_dashboard.php");
            } else {
                header("Location: " . BASE_PATH . "employee_dashboard.php");
            }
            exit;
        }
    } else {
        $msg = 'Invalid credentials';
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="grid">
  <div class="card">
    <h2>Login</h2>
    <?php if($msg): ?>
      <p style="color:#ffb3b3;">&bull; <?php echo htmlspecialchars($msg, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <form method="post" class="form">
      <div class="form-row">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div class="form-row">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button class="btn" type="submit">Sign in</button>
    </form>
   <!-- <p style="margin-top:1rem;">
      Don't have an account? <a href="<?php echo BASE_PATH; ?>signup.php" style="color:#58a6ff;">Create an account</a>
    </p> -->
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
