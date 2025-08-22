<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

// DELETE
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id !== (int)($_SESSION['user']['id'])) { // safety
        $mysqli->query("DELETE FROM users WHERE id=$id");
    }
    header("Location: employees.php"); 
    exit;
}

// Filters
$selectedDept = trim($_GET['dept'] ?? '');
$selectedJob = trim($_GET['job'] ?? '');

// Options for filters
$deptOptions = [];
$jobOptions = [];
$q1 = $mysqli->query("SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND department <> '' ORDER BY department");
while ($q1 && ($row = $q1->fetch_assoc())) { $deptOptions[] = $row['department']; }
$q2 = $mysqli->query("SELECT DISTINCT job_title FROM users WHERE job_title IS NOT NULL AND job_title <> '' ORDER BY job_title");
while ($q2 && ($row = $q2->fetch_assoc())) { $jobOptions[] = $row['job_title']; }

// FETCH employees with filters (prepared)
$sql = "SELECT id, full_name, email, department, job_title, phone, role FROM users";
$conds = [];
$types = '';
$params = [];
if ($selectedDept !== '') { $conds[] = "department = ?"; $types .= 's'; $params[] = $selectedDept; }
if ($selectedJob !== '') { $conds[] = "job_title = ?"; $types .= 's'; $params[] = $selectedJob; }
if ($conds) { $sql .= ' WHERE ' . implode(' AND ', $conds); }
$sql .= ' ORDER BY id DESC';

if ($params) {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $mysqli->query($sql);
}

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Employees</h1>
  <a class="btn-add" href="employee_add.php">Add Employee</a>

  <form method="get" class="form" style="margin-top:1rem;">
    <div class="form-row two">
      <div>
        <label>Filter by Department</label>
        <select name="dept">
          <option value="">-- Any --</option>
          <?php foreach($deptOptions as $dep): ?>
            <option value="<?php echo esc($dep); ?>" <?php if($selectedDept===$dep) echo 'selected'; ?>><?php echo esc($dep); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Filter by Job Title</label>
        <select name="job">
          <option value="">-- Any --</option>
          <?php foreach($jobOptions as $jt): ?>
            <option value="<?php echo esc($jt); ?>" <?php if($selectedJob===$jt) echo 'selected'; ?>><?php echo esc($jt); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div style="margin-top:0.5rem;">
      <button class="btn" type="submit">Apply Filters</button>
      <a class="btn btn-secondary" href="employees.php">Clear</a>
    </div>
  </form>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Job Title</th>
        <th>Phone</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($r = $res->fetch_assoc()): ?>
        <tr>
          <td><?php echo esc($r['id']); ?></td>
          <td><?php echo esc($r['full_name']); ?></td>
          <td><?php echo esc($r['email']); ?></td>
          <td><?php echo esc($r['department']); ?></td>
          <td><?php echo esc($r['job_title']); ?></td>
          <td><?php echo esc($r['phone']); ?></td>
          <td>
            <a href="employee_edit.php?id=<?php echo $r['id']; ?>">Edit</a> | 
            <a href="employees.php?del=<?php echo $r['id']; ?>" onclick="return confirm('Delete this user?')" style="color:#cc1f1a;">Delete</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
