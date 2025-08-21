<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

// DELETE
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $mysqli->query("DELETE FROM tasks WHERE id=$id");
    header("Location: tasks.php");
    exit;
}

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Tasks</h1>
  <a class="btn" href="task_add.php">+ Create Task</a>

  <div class="card card--dark" style="margin-top: 2rem;">
    <h2>All Tasks</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Dates</th>
          <th>Status</th>
          <th>Created By</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $q = $mysqli->query("SELECT t.*, u.full_name as creator_name FROM tasks t LEFT JOIN users u ON t.created_by = u.id ORDER BY t.created_at DESC");
        while ($r = $q->fetch_assoc()): 
        ?>
          <tr>
            <td><?php echo esc($r['id']); ?></td>
            <td><?php echo esc($r['title']); ?></td>
            <td><?php echo esc(substr($r['description'] ?? '', 0, 50)) . (strlen($r['description'] ?? '') > 50 ? '...' : ''); ?></td>
            <td>
              <?php if ($r['start_date'] && $r['due_date']): ?>
                <?php echo esc($r['start_date']); ?> → <?php echo esc($r['due_date']); ?>
              <?php elseif ($r['start_date']): ?>
                From: <?php echo esc($r['start_date']); ?>
              <?php elseif ($r['due_date']): ?>
                Due: <?php echo esc($r['due_date']); ?>
              <?php else: ?>
                No dates set
              <?php endif; ?>
            </td>
            <td>
              <span class="status st-<?php echo str_replace(' ', '-', strtolower($r['status'])); ?>">
                <?php echo esc($r['status']); ?>
              </span>
            </td>
            <td><?php echo esc($r['creator_name'] ?? 'Unknown'); ?></td>
            <td>
              <a href="task_edit.php?id=<?php echo (int)$r['id']; ?>" style="color:#58a6ff;">Edit</a> | 
              <a href="tasks.php?del=<?php echo (int)$r['id']; ?>" onclick="return confirm('Delete this task?')" style="color:#ff6b6b;">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
