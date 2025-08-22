<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
$res = $mysqli->query("SELECT * FROM users WHERE id=$id");
$employee = $res ? $res->fetch_assoc() : null;
if (!$employee) { die('Employee not found'); }

$errors = [];
// Allowed dropdowns
$allowedDepartments = ['Software Development / Engineering'];
$allowedJobTitles = [
    'Software Engineer / Developer',
    'Frontend / Backend Developer',
    'Full Stack Developer',
    'QA Engineer (Tester)'
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $job_title = trim($_POST['job_title'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? $employee['role'];
  

    if ($full_name === '' || mb_strlen($full_name) < 3 || mb_strlen($full_name) > 120) {
        $errors[] = 'Full name must be between 3 and 120 characters.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } else {
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) { $errors[] = 'Email is already in use.'; }
        $stmt->close();
    }
    // Department required and must be allowed
    if ($department === '') { $errors[] = 'Department is required.'; }
    elseif (!in_array($department, $allowedDepartments, true)) { $errors[] = 'Invalid department selected.'; }

    // Job title required and must be allowed
    if ($job_title === '') { $errors[] = 'Job title is required.'; }
    elseif (!in_array($job_title, $allowedJobTitles, true)) { $errors[] = 'Invalid job title selected.'; }

    // Phone validation (exactly 10 digits, formatting allowed)
    if ($phone === '') { $errors[] = 'Phone is required.'; }
    else {
        if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $phone)) { $errors[] = 'Phone can contain digits, spaces, +, -, ().'; }
        $digitsOnly = preg_replace('/\D+/', '', $phone);
        if (strlen($digitsOnly) !== 10) { $errors[] = 'Phone must contain exactly 10 digits.'; }
    }
    foreach ([['department',120,$department], ['job_title',120,$job_title], ['phone',30,$phone]] as [$label,$max,$val]) {
        if ($val !== '' && mb_strlen($val) > $max) { $errors[] = ucfirst($label) . " must be at most {$max} characters."; }
    }

   

    if (!$errors) {
        $stmt = $mysqli->prepare('UPDATE users SET full_name=?, email=?, department=?, job_title=?, phone=?, role=? WHERE id=?');
        $stmt->bind_param('ssssssi', $full_name,$email,$department,$job_title,$phone,$role,$id);
        $stmt->execute();
        header('Location: employees.php');
        exit;
    } else {
        // Overwrite form with latest attempt
        $employee['full_name'] = $full_name;
        $employee['email'] = $email;
        $employee['department'] = $department;
        $employee['job_title'] = $job_title;
        $employee['phone'] = $phone;
        $employee['role'] = $role;
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Edit Employee</h1>
  <?php if(!empty($errors)): ?>
    <div class="card" style="background:#2d1f1f;color:#ffb3b3;margin:1rem 0;">
      <ul>
        <?php foreach($errors as $e): ?><li><?php echo esc($e); ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" class="form card" style="max-width:800px;margin:auto;padding:2rem;">
    <div class="form-row two">
      <div><label>Full Name</label><input name="full_name" required value="<?php echo esc($employee['full_name']); ?>"></div>
      <div><label>Email</label><input type="email" name="email" required value="<?php echo esc($employee['email']); ?>"></div>
    </div>
    <div class="form-row two">
      <div><label>Department</label>
        <select name="department" required>
          <?php foreach($allowedDepartments as $dep): ?>
            <option value="<?php echo esc($dep); ?>" <?php if(($employee['department'] ?? '')===$dep) echo 'selected'; ?>><?php echo esc($dep); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label>Job Title</label>
        <select name="job_title" required>
          <?php foreach($allowedJobTitles as $jt): ?>
            <option value="<?php echo esc($jt); ?>" <?php if(($employee['job_title'] ?? '')===$jt) echo 'selected'; ?>><?php echo esc($jt); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-row two">
      <div><label>Phone</label><input name="phone" required value="<?php echo esc($employee['phone']); ?>"></div>
      <div><label>Role</label>
        <select name="role" required>
          <?php foreach(['employee','manager','admin'] as $r): ?>
            <option value="<?php echo $r; ?>" <?php if($employee['role']===$r) echo 'selected'; ?>><?php echo ucfirst($r); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    
    <div style="margin-top:1.5rem;">
      <button class="btn" type="submit">Update</button>
      <a href="employees.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>


