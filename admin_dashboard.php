<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin']);

// Get summary statistics
$totalUsers = $mysqli->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalEmployees = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE role='employee'")->fetch_assoc()['count'];
$totalTasks = $mysqli->query("SELECT COUNT(*) as count FROM tasks")->fetch_assoc()['count'];

// Get task status breakdown
$taskStatusBreakdown = $mysqli->query("
    SELECT status, COUNT(*) as count
    FROM tasks
    GROUP BY status
    ORDER BY status
");

include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <h1>Admin Dashboard</h1>
  
  <div class="dashboard-cards">
    <div class="card">
      <h3>Users</h3>
      <p><?php echo $totalUsers; ?></p>
    </div>
    <div class="card">
      <h3>Employees</h3>
      <p><?php echo $totalEmployees; ?></p>
    </div>
    <div class="card">
      <h3>Tasks</h3>
      <p><?php echo $totalTasks; ?></p>
    </div>
  </div>
  
  <div class="card card--dark" style="margin-top: 2rem;">
    <h2>Task Status Breakdown</h2>
    <table>
      <thead>
        <tr>
          <th>Status</th>
          <th>Count</th>
        </tr>
      </thead>
      <tbody>
        <?php while($status = $taskStatusBreakdown->fetch_assoc()): ?>
        <tr>
          <td><?php echo esc($status['status']); ?></td>
          <td><?php echo (int)$status['count']; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <h2>All Users</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>4</td>
        <td>Employee One</td>
        <td>employee@example.com</td>
        <td>employee</td>
      </tr>
      <tr>
        <td>3</td>
        <td>Manager One</td>
        <td>manager@example.com</td>
        <td>manager</td>
      </tr>
      <tr>
        <td>1</td>
        <td>Admin</td>
        <td>admin@example.com</td>
        <td>admin</td>
      </tr>
    </tbody>
  </table>
</div>



<?php include 'partials/footer.php'; ?>
