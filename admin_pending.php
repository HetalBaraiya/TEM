<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin']);
include __DIR__ . '/partials/header.php';

if (isset($_GET['approve'])) {
    $uid = (int)$_GET['approve'];
    $stmt = $mysqli->prepare("UPDATE users SET status='active' WHERE id=? AND status='pending'");
    $stmt->bind_param('i', $uid); $stmt->execute();
}
if (isset($_GET['disable'])) {
    $uid = (int)$_GET['disable'];
    $stmt = $mysqli->prepare("UPDATE users SET status='disabled' WHERE id=? AND status<>'active'");
    $stmt->bind_param('i', $uid); $stmt->execute();
}
$pend = $mysqli->query("SELECT id, full_name, email, role, department, phone, created_at FROM users WHERE status='pending' ORDER BY created_at ASC");
?>
<div class="grid">
  <div class="card">
    <h2>Pending Approvals</h2>
    <table>
      <tr><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Phone</th><th>Requested</th><th>Action</th></tr>
      <?php while($u = $pend->fetch_assoc()): ?>
      <tr>
        <td><?php echo esc($u['full_name']); ?></td>
        <td><?php echo esc($u['email']); ?></td>
        <td><?php echo esc($u['role']); ?></td>
        <td><?php echo esc($u['department']); ?></td>
        <td><?php echo esc($u['phone']); ?></td>
        <td><?php echo esc($u['created_at']); ?></td>
        <td><a class="btn" href="admin_pending.php?approve=<?php echo (int)$u['id']; ?>">Approve</a> <a class="btn btn-secondary" href="admin_pending.php?disable=<?php echo (int)$u['id']; ?>">Reject</a></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
