<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

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

    // Phone validation (server-side)
    if ($old['phone'] !== '') {
        if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $old['phone'])) {
            $errors[] = 'Phone can contain digits, spaces, +, -, ().';
        }
        $digitsOnly = preg_replace('/\D+/', '', $old['phone']);
        $digitsLen = strlen($digitsOnly);
        if ($digitsLen < 7 || $digitsLen > 15) {
            $errors[] = 'Phone must contain 7 to 15 digits.';
        }
    }

    // Optional field lengths
    foreach ([['department',120], ['job_title',120], ['phone',30]] as [$field,$max]) {
        if ($old[$field] !== '' && mb_strlen($old[$field]) > $max) {
            $errors[] = ucfirst(str_replace('_',' ',$field)) . " must be at most {$max} characters.";
        }
    }


    // If no errors, create the user
    if (!$errors) {
        // Hash the default password
        $default_password = 'password123';
        $password_hash = password_hash($default_password, PASSWORD_DEFAULT);
        
        // Insert the user into the database
        $stmt = $mysqli->prepare("INSERT INTO users (full_name, email, department, job_title, phone, role, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $old['full_name'], $old['email'], $old['department'], $old['job_title'], $old['phone'], $old['role'], $password_hash);
        
        if ($stmt->execute()) {
            // Success - redirect to employees list
            header("Location: employees.php");
            exit;
        } else {
            $errors[] = "Failed to create employee. Please try again.";
        }
        $stmt->close();
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
  <form id="employee-form" method="post" class="form card" style="max-width:800px;margin:auto;padding:2rem;">
    <div class="form-row two">
      <div><label>Full Name</label><input name="full_name" required value="<?php echo esc($old['full_name']); ?>"></div>
      <div><label>Email</label><input type="email" name="email" required value="<?php echo esc($old['email']); ?>"></div>
    </div>
    <div class="form-row two">
      <div><label>Department</label><input name="department" value="<?php echo esc($old['department']); ?>"></div>
      <div><label>Job Title</label><input name="job_title" value="<?php echo esc($old['job_title']); ?>"></div>
    </div>
    <div class="form-row two">
      <div><label>Phone</label><input name="phone" value="<?php echo esc($old['phone']); ?>"></div>
      <div><label>Role</label>
        <select name="role">
          <?php foreach(['employee'=>'Employee','manager'=>'Manager','admin'=>'Admin'] as $val=>$label): ?>
            <option value="<?php echo $val; ?>" <?php if($old['role']===$val) echo 'selected'; ?>><?php echo $label; ?></option>
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
