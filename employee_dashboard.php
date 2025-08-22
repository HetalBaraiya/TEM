<?php require_once __DIR__ . '/includes/auth.php'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>
<?php
$uid = (int)$_SESSION['user']['id'];
$q = $mysqli->query("SELECT t.id, t.title, t.status, t.start_date, t.due_date
                     FROM task_assignments a
                     JOIN tasks t ON t.id=a.task_id
                     WHERE a.user_id=$uid
                     ORDER BY t.due_date ASC, t.title ASC");
?>
<div class='container'>
  <h1>My Tasks</h1>
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Status</th>
        <th>Start</th>
        <th>Due</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while($t=$q->fetch_assoc()): ?>
      <tr>
        <td><?php echo esc($t['title']); ?></td>
        <td><?php echo esc($t['status']); ?></td>
        <td><?php echo esc($t['start_date']); ?></td>
        <td><?php echo esc($t['due_date']); ?></td>
        <td><a class="btn" href="<?php echo BASE_PATH; ?>task_update_status.php?id=<?php echo (int)$t['id']; ?>">Update Status</a></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>