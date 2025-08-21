<?php
require_once __DIR__ . '/config.php';

/* Create tables */
$schema = <<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','manager','employee') NOT NULL DEFAULT 'employee',
  status ENUM('active','pending','disabled') NOT NULL DEFAULT 'active',
  department VARCHAR(120) DEFAULT NULL,
  job_title VARCHAR(120) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL UNIQUE,
  description TEXT,
  start_date DATE,
  due_date DATE,
  status ENUM('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  created_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS task_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id INT NOT NULL,
  user_id INT NOT NULL,
  assigned_by INT DEFAULT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_assignment (task_id, user_id),
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
SQL;

if (!$mysqli->multi_query($schema)) {
    die('Error creating tables: ' . $mysqli->error);
}
while ($mysqli->more_results() && $mysqli->next_result()) { /* flush results */ }

/* Ensure users.status column exists for older databases */
$colCheck = $mysqli->query("SHOW COLUMNS FROM users LIKE 'status'");
if (!$colCheck || $colCheck->num_rows === 0) {
    $alter = "ALTER TABLE users ADD COLUMN status ENUM('active','pending','disabled') NOT NULL DEFAULT 'active' AFTER role";
    if (!$mysqli->query($alter)) {
        die('Failed adding users.status column: ' . $mysqli->error);
    }
}

// Seed an admin if none exists
$adminEmail = 'admin@example.com';
$res = $mysqli->query("SELECT id FROM users WHERE email='$adminEmail' LIMIT 1");
if ($res && $res->num_rows === 0) {
    $name = "Admin User";
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $role = "admin";
    $stmt = $mysqli->prepare("INSERT INTO users(full_name,email,password_hash,role,department,job_title,phone) VALUES(?,?,?,?,?,?,?)");
    $dept = "Administration"; $jt="Super Admin"; $ph="0000000000";
    $stmt->bind_param("sssssss",$name,$adminEmail,$pass,$role,$dept,$jt,$ph);
    $stmt->execute();
}
// If admin exists but password does not verify, reset to known default for local/dev
else if ($res && $res->num_rows === 1) {
    $row = $mysqli->query("SELECT id, password_hash FROM users WHERE email='$adminEmail' LIMIT 1")->fetch_assoc();
    if (!$row || !password_verify('admin123', $row['password_hash'])) {
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $upd = $mysqli->prepare("UPDATE users SET password_hash=?, status='active' WHERE email=?");
        $upd->bind_param('ss', $newHash, $adminEmail);
        $upd->execute();
    }
}
echo "Database initialized. Default admin -> email: admin@example.com, password: admin123";
?>
