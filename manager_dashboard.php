<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['manager']);
include __DIR__ . '/partials/header.php';

// Fetch stats for cards
$uid = (int)$_SESSION['user']['id'];

// Count employees in manager’s team
$teamEmployees = $mysqli->query("
    SELECT COUNT(*) AS c 
    FROM users 
    WHERE role='employee'
")->fetch_assoc()['c'];

// Count tasks created by this manager
$tasks = $mysqli->query("
    SELECT COUNT(*) AS c 
    FROM tasks 
    WHERE created_by=$uid
")->fetch_assoc()['c'];

// Count teams created by this manager (if teams table exists)
$teams = 0;
if ($mysqli->query("SHOW TABLES LIKE 'teams'")->num_rows > 0) {
    $teams = $mysqli->query("
        SELECT COUNT(*) AS c 
        FROM teams 
        WHERE created_by=$uid
    ")->fetch_assoc()['c'];
}
?>

<div class="container">
  <h1>Manager Dashboard</h1>

  <div class="dashboard-cards">
    <div class="card">
      <h3>Teams</h3>
      <p><?php echo $teams; ?></p>
    </div>
    <div class="card">
      <h3>Employees</h3>
      <p><?php echo $teamEmployees; ?></p>
    </div>
    <div class="card">
      <h3>Tasks</h3>
      <p><?php echo $tasks; ?></p>
    </div>
  </div>

  <h2>My Tasks</h2>
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Status</th>
        <th>Start</th>
        <th>Due</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $q = $mysqli->query("SELECT title, status, start_date, due_date
                           FROM tasks WHERE created_by=$uid ORDER BY created_at DESC");
      while ($r = $q->fetch_assoc()): ?>
        <tr>
          <td><?php echo esc($r['title']); ?></td>
          <td><?php echo esc($r['status']); ?></td>
          <td><?php echo esc($r['start_date']); ?></td>
          <td><?php echo esc($r['due_date']); ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  
  <h2 style="margin-top: 2rem;">Assigned Tasks</h2>
  <table>
    <thead>
      <tr>
        <th>Task Title</th>
        <th>Employee</th>
        <th>Status</th>
        <th>Assigned By</th>
        <th>Assigned At</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $assignedTasks = $mysqli->query("
        SELECT
          t.title as task_title,
          u.full_name as employee_name,
          t.status,
          assigner.full_name as assigned_by_name,
          ta.assigned_at
        FROM task_assignments ta
        JOIN tasks t ON t.id = ta.task_id
        JOIN users u ON u.id = ta.user_id
        JOIN users assigner ON assigner.id = ta.assigned_by
        WHERE ta.assigned_by = $uid
        ORDER BY ta.assigned_at DESC
      ");
      
      if ($assignedTasks->num_rows > 0):
        while ($task = $assignedTasks->fetch_assoc()): ?>
          <tr>
            <td><?php echo esc($task['task_title']); ?></td>
            <td><?php echo esc($task['employee_name']); ?></td>
            <td>
              <span class="status st-<?php echo str_replace(' ', '-', strtolower($task['status'])); ?>">
                <?php echo esc($task['status']); ?>
              </span>
            </td>
            <td><?php echo esc($task['assigned_by_name']); ?></td>
            <td><?php echo esc($task['assigned_at']); ?></td>
          </tr>
        <?php endwhile;
      else: ?>
        <tr>
          <td colspan="5">No tasks assigned yet.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
