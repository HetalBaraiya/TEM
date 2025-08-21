<?php
// Session timeout configuration (30 minutes)
$timeout_duration = 1800; // 30 minutes in seconds

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and handle session timeout
if (isset($_SESSION['user'])) {
    // Check if last activity time is set
    if (isset($_SESSION['last_activity'])) {
        // Calculate time since last activity
        $session_life = time() - $_SESSION['last_activity'];
        
        // If session has expired, destroy it and redirect to login
        if ($session_life > $timeout_duration) {
            session_destroy();
            header("Location: index.php?timeout=1");
            exit;
        }
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}
$mysqli = new mysqli("localhost", "root", "", "task_employee_core");
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function create_notification($user_id, $message) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO notifications(user_id, message) VALUES(?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

function create_notifications_for_role($role, $message) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($user = $result->fetch_assoc()) {
        create_notification((int)$user['id'], $message);
    }
    $stmt->close();
}
?>
