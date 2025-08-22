<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin']);

// Escape function (only declare if not already declared)
if (!function_exists('esc')) {
    function esc($str){
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

// Filters
$status   = $_GET['status'] ?? '';
$employee = $_GET['employee'] ?? '';
$task     = $_GET['task'] ?? '';

// Build SQL dynamically with prepared statements
$where = [];
$params = [];
$types  = '';

if($status !== ''){
    $where[] = "t.status = ?";
    $params[] = $status;
    $types .= 's';
}
if($employee !== ''){
    $where[] = "u.full_name LIKE ?";
    $params[] = '%' . $employee . '%';
    $types .= 's';
}
if($task !== ''){
    $where[] = "t.title LIKE ?";
    $params[] = '%' . $task . '%';
    $types .= 's';
}

$where_sql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

// ---- Summary counts ----
$total_employees = $mysqli->query("SELECT COUNT(*) AS c FROM users WHERE role='employee'")->fetch_assoc()['c'];
$total_tasks     = $mysqli->query("SELECT COUNT(*) AS c FROM tasks")->fetch_assoc()['c'];

$status_counts = [];
$stq = $mysqli->query("SELECT status, COUNT(*) as c FROM tasks GROUP BY status");
while($row = $stq->fetch_assoc()){
    $status_counts[$row['status']] = $row['c'];
}

include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <h1>Reports Dashboard</h1>

  <!-- Summary Cards -->
  <div class="dashboard-cards">
    <div class="card">
      <h3>Total Employees</h3>
      <p><?php echo (int)$total_employees; ?></p>
    </div>
    <div class="card">
      <h3>Total Tasks</h3>
      <p><?php echo (int)$total_tasks; ?></p>
    </div>
    <div class="card">
      <h3>Pending</h3>
      <p><?php echo $status_counts['Pending'] ?? 0; ?></p>
    </div>
    <div class="card">
      <h3>In Progress</h3>
      <p><?php echo $status_counts['In Progress'] ?? 0; ?></p>
    </div>
    <div class="card">
      <h3>Completed</h3>
      <p><?php echo $status_counts['Completed'] ?? 0; ?></p>
    </div>
  </div>

  <!-- Filters -->
  <div class="card">
    <h2>Filter Reports</h2>
    <form method="get" class="form">
      <div class="reports-filters">
        <div class="form-row">
          <label>Status</label>
          <select name="status">
            <option value="">Any Status</option>
            <?php foreach(['Pending','In Progress','Completed','Cancelled'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo ($status === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>Employee Name</label>
          <input type="text" name="employee" value="<?php echo esc($employee); ?>" placeholder="Search by employee">
        </div>
        <div class="form-row">
          <label>Task Name</label>
          <input type="text" name="task" value="<?php echo esc($task); ?>" placeholder="Search by task">
        </div>
      </div>
      <button class="btn" type="submit">Apply Filters</button>
      <a href="reports.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>

  <!-- Task Overview -->
  <div class="card">
    <h2>Task-wise Employee Assignment Overview</h2>
    <table>
      <thead>
        <tr>
          <th>Task</th>
          <th>Status</th>
          <th>Employees</th>
          <th>Start Date</th>
          <th>Due Date</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Prepare SQL with placeholders
        $sql = "
          SELECT t.id, t.title, t.status, t.start_date, t.due_date,
                 GROUP_CONCAT(DISTINCT u.full_name SEPARATOR ', ') AS employees
          FROM tasks t
          LEFT JOIN task_assignments a ON a.task_id = t.id
          LEFT JOIN users u ON u.id = a.user_id
          $where_sql
          GROUP BY t.id
          ORDER BY t.due_date ASC
        ";

        $stmt = $mysqli->prepare($sql);
        if($params){
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while($r = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo esc($r['title']); ?></td>
            <td><span class="status st-<?php echo strtolower(str_replace(' ', '-', $r['status'])); ?>"><?php echo esc($r['status']); ?></span></td>
            <td><?php echo esc($r['employees']); ?></td>
            <td><?php echo esc($r['start_date']); ?></td>
            <td><?php echo esc($r['due_date']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
