<?php
// ==================== SESSION MANAGEMENT ====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session timeout: 30 minutes
$SESSION_TTL_SECONDS = 60 * 30;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $SESSION_TTL_SECONDS)) {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php?expired=1");
    exit;
}
$_SESSION['last_activity'] = time();

// ==================== DATABASE CONNECTION ====================
$mysqli = new mysqli("localhost", "root", "", "task_employee_core");
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: " . $mysqli->connect_error);
}

// ==================== BASE PATH ====================
if (!defined('BASE_PATH')) {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    define('BASE_PATH', $scriptDir === '' ? '/' : $scriptDir . '/');
}

// ==================== UTILITY FUNCTIONS ====================

/**
 * Escape output to prevent XSS
 */
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get current logged-in user
 */
function current_user() {
    return $_SESSION['user'] ?? null;
}

/**
 * Create a notification for a specific user
 */
function create_notification($user_id, $message) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO notifications(user_id, message) VALUES(?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

/**
 * Create notifications for all users with a specific role
 */
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

// ==================== DATABASE INIT / UPGRADE ====================
require_once __DIR__ . '/db_init.php';
?>
