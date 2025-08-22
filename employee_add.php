<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

// Allowed dropdowns (same as edit_employee.php)
$allowedDepartments = ['Software Development / Engineering'];
$allowedJobTitles = [
    'Software Engineer / Developer',
    'Frontend / Backend Developer',
    'Full Stack Developer',
    'QA Engineer (Tester)'
];

// Validation + Create
$errors = [];
$old = ['full_name'=>'','email'=>'','department'=>'','job_title'=>'','phone'=>'','role'=>'employee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['department'] = trim($_POST['department'] ?? '');
    $old['job_title'] = trim($_POST['job_title'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['role'] = $_POST['role'] ?? 'employee';

    // Full name
    if ($old['full_name'] === '' || mb_strlen($old['full_name']) < 3 || mb_strlen($old['full_name']) > 120) {
        $errors[] = 'Full name must be between 3 and 120 characters.';
    }

    // Email format
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    } else {
        // Uniqueness
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email is already in use.';
        }
        $stmt->close();
    }

    // Department validation
    if ($old['department'] === '') {
        $errors[] = 'Department is required.';
    } elseif (!in_array($old['department'], $allowedDepartments, true)) {
        $errors[] = 'Invalid department selected.';
    }

    // Job title validation
    if ($old['job_title'] === '') {
        $errors[] = 'Job title is required.';
    } elseif (!in_array($old['job_title'], $allowedJobTitles, true)) {
        $errors[] = 'Invalid job title selected.';
    }

    // Phone validation (server-side)
    if ($old['phone'] !== '') {
        if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $old['phone'])) {
            $errors[] = 'Phone can contain digits, spaces, +, -, ().';
        }
        $digitsOnly = preg_replace('/\D+/', '', $old['phone']);
        if (strlen($digitsOnly) !== 10) {
            $errors[] = 'Phone must contain exactly 10 digits.';
        }
    } else {
        $errors[] = 'Phone is required.';
    }

    // Only Admin can create Admin users
    if ($old['role'] === 'admin' && ($_SESSION['user']['role'] ?? '') !== 'admin') {
        $errors[] = 'Only Admin users can create Admin accounts.';
    }

    // Create user if no validation errors
    if (empty($errors)) {
        $defaultPassword = 'password123';
        $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        $status = 'active';

        $stmt = $mysqli->prepare("INSERT INTO users (full_name, email, password_hash, department, job_title, phone, role, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            'ssssssss',
            $old['full_name'],
            $old['email'],
            $passwordHash,
            $old['department'],
            $old['job_title'],
            $old['phone'],
            $old['role'],
            $status
        );
        if ($stmt->execute()) {
            header('Location: employees.php');
            exit;
        } else {
            $errors[] = 'Failed to create user.';
        }
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Add Employee</h1>
  <?php if(!empty($errors)): ?>
    <div class="card" style="background:#2d1f1f;color:#ffb3b3;margin:1rem 0;">
      <ul>
        <?php foreach($errors as $e): ?>
          <li><?php echo esc($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" class="form card" style="max-width:800px;margin:auto;padding:2rem;">
    <div class="form-row two">
      <div>
        <label>Full Name</label>
        <input name="full_name" required value="<?php echo esc($old['full_name']); ?>">
      </div>
      <div>
        <label>Email</label>
        <input type="email" name="email" required value="<?php echo esc($old['email']); ?>">
      </div>
    </div>
    <div class="form-row two">
      <div>
        <label>Department</label>
        <select name="department" required>
          <option value="">-- Select Department --</option>
          <?php foreach ($allowedDepartments as $dep): ?>
            <option value="<?php echo esc($dep); ?>" <?php if($old['department']===$dep) echo 'selected'; ?>>
              <?php echo esc($dep); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Job Title</label>
        <select name="job_title" required>
          <option value="">-- Select Job Title --</option>
          <?php foreach ($allowedJobTitles as $jt): ?>
            <option value="<?php echo esc($jt); ?>" <?php if($old['job_title']===$jt) echo 'selected'; ?>>
              <?php echo esc($jt); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row two">
      <div>
        <label>Phone</label>
        <input name="phone" required value="<?php echo esc($old['phone']); ?>">
      </div>
      <div>
        <label>Role</label>
        <select name="role" required>
          <?php foreach(['employee','manager','admin'] as $r): ?>
            <option value="<?php echo $r; ?>" <?php if($old['role']===$r) echo 'selected'; ?>>
              <?php echo ucfirst($r); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="margin-top:1.5rem;">
      <button class="btn" type="submit">Create</button>
      <a href="employees.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
