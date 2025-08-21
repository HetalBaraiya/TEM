<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager','employee']); // all logged-in users can see notifications

$user_id = (int)$_SESSION['user']['id'];

// Mark notification as read
if (isset($_GET['mark_read'])) {
    $notif_id = (int)$_GET['mark_read'];
    $stmt = $mysqli->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit;
}

// Mark all notifications as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $mysqli->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: notifications.php");
    exit;
}

// fetch notifications
$res = $mysqli->prepare("SELECT id, message, created_at, is_read FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$res->bind_param("i", $user_id);
$res->execute();
$result = $res->get_result();

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Notifications</h1>
  <div style="margin-bottom: 1rem;">
    <a href="notifications.php?mark_all_read=1" class="btn">Mark All as Read</a>
  </div>
  <table>
    <thead>
      <tr>
        <th>Message</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row=$result->fetch_assoc()): ?>
        <tr style="<?php echo $row['is_read'] ? '' : 'background-color: #2d3a4d;'; ?>">
          <td><?php echo esc($row['message']); ?></td>
          <td><?php echo esc($row['created_at']); ?></td>
          <td><?php echo $row['is_read'] ? 'Read' : 'Unread'; ?></td>
          <td>
            <?php if (!$row['is_read']): ?>
              <a href="notifications.php?mark_read=<?php echo (int)$row['id']; ?>">Mark as Read</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
