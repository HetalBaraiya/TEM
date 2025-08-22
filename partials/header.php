<?php require_once __DIR__ . '/../includes/config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Task & Employee Management</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<?php
// Get current page name for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="profile">
      <?php if(isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
        <div class="profile-avatar">
          <?php echo strtoupper(substr($_SESSION['user']['full_name'], 0, 1)); ?>
        </div>
        <div class="profile-name"><?php echo esc($_SESSION['user']['full_name']); ?></div>
        <div class="profile-role"><?php echo esc($_SESSION['user']['role']); ?></div>
      <?php else: ?>
        <div class="profile-avatar">
          G
        </div>
        <div class="profile-name">Guest</div>
        <div class="profile-role">Visitor</div>
      <?php endif; ?>
    </div>
    
    <nav>
      <?php if(isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
        <?php if($_SESSION['user']['role'] === 'admin'): ?>
          <a href="<?php echo BASE_PATH; ?>admin_dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
          <a href="<?php echo BASE_PATH; ?>employees.php" <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'class="active"' : ''; ?>>Employees</a>
          <a href="<?php echo BASE_PATH; ?>tasks.php" <?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'class="active"' : ''; ?>>Tasks</a>
          <a href="<?php echo BASE_PATH; ?>assign.php" <?php echo basename($_SERVER['PHP_SELF']) == 'assign.php' ? 'class="active"' : ''; ?>>Assign</a>
          <a href="<?php echo BASE_PATH; ?>teams.php" <?php echo basename($_SERVER['PHP_SELF']) == 'teams.php' ? 'class="active"' : ''; ?>>Teams</a>
          <a href="<?php echo BASE_PATH; ?>reports.php" <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'class="active"' : ''; ?>>Reports</a>
      
          <a href="<?php echo BASE_PATH; ?>notifications.php" <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'class="active"' : ''; ?>>Notifications</a>
        <?php elseif($_SESSION['user']['role'] === 'manager'): ?>
          <a href="<?php echo BASE_PATH; ?>manager_dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manager_dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
          <a href="<?php echo BASE_PATH; ?>employees.php" <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'class="active"' : ''; ?>>Employees</a>
          <a href="<?php echo BASE_PATH; ?>tasks.php" <?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'class="active"' : ''; ?>>Tasks</a>
          <a href="<?php echo BASE_PATH; ?>assign.php" <?php echo basename($_SERVER['PHP_SELF']) == 'assign.php' ? 'class="active"' : ''; ?>>Assign</a>
          <a href="<?php echo BASE_PATH; ?>teams.php" <?php echo basename($_SERVER['PHP_SELF']) == 'teams.php' ? 'class="active"' : ''; ?>>Teams</a>
          <a href="<?php echo BASE_PATH; ?>notifications.php" <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'class="active"' : ''; ?>>Notifications</a>
        <?php else: ?>
          <a href="<?php echo BASE_PATH; ?>employee_dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'employee_dashboard.php' ? 'class="active"' : ''; ?>>Dashboard</a>
          <a href="<?php echo BASE_PATH; ?>notifications.php" <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'class="active"' : ''; ?>>Notifications</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
    
    <?php if(isset($_SESSION['user'])): ?>
      <div class="logout">
        <?php if(isset($_SESSION['user']) && !empty($_SESSION['user'])): ?>
          <a href="<?php echo BASE_PATH; ?>logout.php">Logout</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <!-- Top Bar (Mobile) -->
    <header class="topbar">
      <div class="nav-inner">
        <div class="brand">TEM System</div>
        <nav>
          <a href="#" onclick="toggleSidebar()">☰ Menu</a>
        </nav>
      </div>
    </header>
