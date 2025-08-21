<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Task & Employee Management</title>
  <link rel="stylesheet" href="<?php echo "/task_employee_management_core_php/assets/css/styles.css"; ?>">
</head>
<body>
<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="brand">TEM System</div>
    <nav>
      <?php if(isset($_SESSION['user'])): ?>
        <?php if($_SESSION['user']['role'] === 'admin'): ?>
          <a href="admin_dashboard.php">Dashboard</a>
          <a href="employees.php">Employees</a>
          <a href="tasks.php">Tasks</a>
          <a href="assign.php">Assign</a>
          <a href="teams.php">Teams</a>
          <a href="reports.php">Reports</a>
         
          <a href="notifications.php">Notifications</a>
        <?php elseif($_SESSION['user']['role'] === 'manager'): ?>
          <a href="manager_dashboard.php">Dashboard</a>
          <a href="teams.php">Manage Teams</a>
          <a href="tasks.php">Tasks</a>
          <a href="assign.php">Assign</a>
          <a href="notifications.php">Notifications</a>
        <?php else: ?>
          <a href="employee_dashboard.php">Dashboard</a>
          <a href="notifications.php">Notifications</a>
        <?php endif; ?>
        <a href="logout.php" class="logout">Logout</a>
      <?php else: ?>
        <a href="index.php">Home</a>
      <?php endif; ?>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
