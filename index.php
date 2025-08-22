<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($role === 'manager') {
        header("Location: manager_dashboard.php");
    } else {
        header("Location: employee_dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $mysqli->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                if ($user['status'] === 'active') {
                    // Login successful
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($user['role'] === 'manager') {
                        header("Location: manager_dashboard.php");
                    } else {
                        header("Location: employee_dashboard.php");
                    }
                    exit;
                } else {
                    $error = 'Your account is not active. Please contact administrator.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>Welcome Back</h1>
        <p>Sign in to your account to continue</p>
      </div>
      
      <?php if($error): ?>
        <div class="alert alert-error"><?php echo esc($error); ?></div>
      <?php endif; ?>
      
      <?php if($success): ?>
        <div class="alert alert-success"><?php echo esc($success); ?></div>
      <?php endif; ?>
      
      <form method="post" class="login-form">
        <div class="form-row">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo esc($_POST['email'] ?? ''); ?>">
        </div>
        
        <div class="form-row">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter your password">
        </div>
        
        <div class="form-row">
          <button type="submit" class="btn btn-login">Login</button>
        </div>
        
       <!-- <div class="login-footer">
          <p>Don't have an account? <a href="signup.php">Create one here</a></p>
        </div>
      </form> -->
      
      <div class="demo-credentials">
        <h3>Demo Credentials</h3>
        <div class="credential-item">
          <strong>Admin:</strong> admin@example.com / admin123
        </div>
        <div class="credential-item">
          <strong>Manager:</strong> manager@example.com / manager123
        </div>
        <div class="credential-item">
          <strong>Employee:</strong> employee1@example.com / employee123
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
