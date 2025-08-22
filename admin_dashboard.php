<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin']);
include __DIR__ . '/partials/header.php';

// Fetch real-time stats
$totalUsers = $mysqli->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalEmployees = $mysqli->query("SELECT COUNT(*) as c FROM users WHERE role='employee'")->fetch_assoc()['c'];
$totalTasks = $mysqli->query("SELECT COUNT(*) as c FROM tasks")->fetch_assoc()['c'];
$pendingTasks = $mysqli->query("SELECT COUNT(*) as c FROM tasks WHERE status='Pending'")->fetch_assoc()['c'];
$activeUsers = $mysqli->query("SELECT COUNT(*) as c FROM users WHERE status='active'")->fetch_assoc()['c'];
$completedTasks = $mysqli->query("SELECT COUNT(*) as c FROM tasks WHERE status='Completed'")->fetch_assoc()['c'];

// Recent users
$recentUsers = $mysqli->query("SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 10");
?>

<div class="container">
  <h1>Admin Dashboard</h1>

  <div class="dashboard-cards">
    <div class="card">
      <h3>Total Users</h3>
      <p><?php echo $totalUsers; ?></p>
    </div>
    <div class="card">
      <h3>Active Users</h3>
      <p><?php echo $activeUsers; ?></p>
    </div>
    <div class="card">
      <h3>Employees</h3>
      <p><?php echo $totalEmployees; ?></p>
    </div>
    <div class="card">
      <h3>Total Tasks</h3>
      <p><?php echo $totalTasks; ?></p>
    </div>
    <div class="card">
      <h3>Pending Tasks</h3>
      <p><?php echo $pendingTasks; ?></p>
    </div>
    <div class="card">
      <h3>Completed Tasks</h3>
      <p><?php echo $completedTasks; ?></p>
    </div>
  </div>

  <div class="card card--dark" style="margin-top: 2rem;">
    <h2>Recent Users</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php while($r = $recentUsers->fetch_assoc()): ?>
          <tr>
            <td><?php echo esc($r['id']); ?></td>
            <td><?php echo esc($r['full_name']); ?></td>
            <td><?php echo esc($r['email']); ?></td>
            <td><?php echo esc($r['role']); ?></td>
            <td>
              <span class="status st-<?php echo $r['status']; ?>">
                <?php echo esc($r['status']); ?>
              </span>
            </td>
            <td><?php echo esc($r['created_at']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
