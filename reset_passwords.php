<?php
require_once __DIR__ . '/includes/config.php';

// Only allow this script to run in development
if (!isset($_GET['dev']) || $_GET['dev'] !== 'reset') {
    die('Access denied. Use ?dev=reset to run this script.');
}

echo "<h2>Resetting Default Passwords</h2>";


$default_passwords = [
    'admin@example.com' => 'admin123',
    'manager@example.com' => 'manager123',
    'employee1@example.com' => 'employee123'
];

$success_count = 0;
$error_count = 0;

foreach ($default_passwords as $email => $password) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $password_hash, $email);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Updated password for $email</p>";
        $success_count++;
    } else {
        echo "<p style='color: red;'>✗ Failed to update password for $email: " . $mysqli->error . "</p>";
        $error_count++;
    }
}

echo "<hr>";
echo "<h3>Summary:</h3>";
echo "<p>Successfully updated: $success_count passwords</p>";
echo "<p>Failed updates: $error_count</p>";

echo "<hr>";
echo "<h3>Default Login Credentials:</h3>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@example.com / admin123</li>";
echo "<li><strong>Manager:</strong> manager@example.com / manager123</li>";
echo "<li><strong>Employee:</strong> employee1@example.com / employee123</li>";
echo "</ul>";

echo "<p><a href='login.php'>Go to Login</a></p>";
?>
