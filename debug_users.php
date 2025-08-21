<?php
// debug_users.php  (run once via browser, then DELETE)
require_once __DIR__ . '/includes/config.php';
header('Content-Type: text/plain; charset=utf-8');

echo "== DB CONNECTION ==\n";
echo "mysqli connect_errno: " . ($mysqli->connect_errno ? $mysqli->connect_errno : '0') . "\n";
echo "mysqli connect_error: " . ($mysqli->connect_error ?: 'none') . "\n";

$res = $mysqli->query("SELECT DATABASE() AS db");
$db = $res ? ($res->fetch_assoc()['db'] ?? 'N/A') : 'N/A';
echo "Using database: " . $db . "\n\n";

$emails = ['admin@example.com','manager@example.com','employee1@example.com'];
foreach ($emails as $email) {
    echo "----\nLooking up: $email\n";
    $stmt = $mysqli->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) { echo "Prepare error: " . $mysqli->error . "\n"; continue; }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo "Found user id: " . $row['id'] . "\n";
        echo "Full name: " . $row['full_name'] . "\n";
        echo "Role: " . $row['role'] . "\n";
        echo "Status: " . ($row['status'] ?? '[no status column]') . "\n";
        echo "Stored password_hash (first 60 chars): " . substr($row['password_hash'],0,60) . "\n";
        $plain = ($email === 'admin@example.com') ? 'admin123' : (($email === 'manager@example.com') ? 'manager123' : 'employee123');
        $ok = password_verify($plain, $row['password_hash']) ? 'YES' : 'NO';
        echo "password_verify('$plain', hash) => " . $ok . "\n";
    } else {
        echo "User not found.\n";
    }
    $stmt->close();
}
echo "\nDone. DELETE this file after use.\n";
?>
