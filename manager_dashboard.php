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
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
