<?php
require_once __DIR__ . '/config.php';

// ==================== TABLE CREATION ====================
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(120) NOT NULL,
        email VARCHAR(160) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin','manager','employee') NOT NULL DEFAULT 'employee',
        status ENUM('active','pending','disabled') NOT NULL DEFAULT 'active',
        department VARCHAR(120) DEFAULT NULL,
        job_title VARCHAR(120) DEFAULT NULL,
        phone VARCHAR(30) DEFAULT NULL,
        profile_picture VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        status ENUM('Pending','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
        priority ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
        start_date DATE DEFAULT NULL,
        due_date DATE DEFAULT NULL,
        created_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS task_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        user_id INT NOT NULL,
        assigned_by INT DEFAULT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_assignment (task_id, user_id),
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_id INT NOT NULL,
        user_id INT NOT NULL,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_member (team_id, user_id),
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

// Execute table creation
foreach ($tables as $sql) {
    if (!$mysqli->query($sql)) {
        echo "Error creating table: " . $mysqli->error . "<br>";
    }
}

// ==================== SEED DEFAULT USERS ====================
$default_users = [
    ['admin@example.com', 'Admin User', 'admin', 'admin123'],
    ['manager@example.com', 'Manager User', 'manager', 'manager123'],
    ['employee1@example.com', 'Employee One', 'employee', 'employee123']
];

foreach ($default_users as $user) {
    list($email, $name, $role, $password) = $user;
    $res = $mysqli->prepare("SELECT id FROM users WHERE email=?");
    $res->bind_param("s", $email);
    $res->execute();
    $result = $res->get_result();
    if ($result->num_rows === 0) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (email, full_name, role, password_hash, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssss", $email, $name, $role, $password_hash);
        $stmt->execute();
        $stmt->close();
    }
    $res->close();
}

// ==================== RESET ADMIN PASSWORD FOR DEV ====================
if (isset($_GET['init']) && $_GET['init'] == '1') {
    $admin_email = 'admin@example.com';
    $admin_password = 'admin123';
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("UPDATE users SET password_hash = ?, status='active' WHERE email = ?");
    $stmt->bind_param("ss", $password_hash, $admin_email);
    $stmt->execute();
    $stmt->close();

    echo "Database initialized successfully!<br>";
    echo "Admin credentials: $admin_email / $admin_password<br>";
    echo "<a href='../login.php'>Go to Login</a>";
}
?>
