<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['employee']);

// Get current user ID
$uid = (int)$_SESSION['user']['id'];

include __DIR__ . '/partials/header.php';

// Fetch tasks assigned to this employee
$q = $mysqli->query("
    SELECT
        t.id,
        t.title,
        t.description,
        t.status,
        t.start_date,
        t.due_date,
        u.full_name as assigned_by_name,
        ta.assigned_at
    FROM task_assignments ta
    JOIN tasks t ON t.id = ta.task_id
    JOIN users u ON u.id = ta.assigned_by
    WHERE ta.user_id = $uid
    ORDER BY ta.assigned_at DESC
");
?>
<div class="container">
    <h1>Employee Dashboard</h1>
    <p>Welcome, <?php echo esc($_SESSION['user']['full_name']); ?>!</p>
    
    <div class="card card--dark" style="margin-top: 2rem;">
        <h2>My Assigned Tasks</h2>
        <?php if ($q->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Dates</th>
                    <th>Assigned By</th>
                    <th>Assigned At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $q->fetch_assoc()): ?>
                <tr>
                    <td><?php echo esc($r['id']); ?></td>
                    <td><?php echo esc($r['title']); ?></td>
                    <td><?php echo esc(substr($r['description'] ?? '', 0, 50)) . (strlen($r['description'] ?? '') > 50 ? '...' : ''); ?></td>
                    <td>
                        <span class="status st-<?php echo str_replace(' ', '-', strtolower($r['status'])); ?>">
                            <?php echo esc($r['status']); ?>
                        </span>
                    </td>
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
                    <td><?php echo esc($r['assigned_by_name']); ?></td>
                    <td><?php echo esc($r['assigned_at']); ?></td>
                    <td>
                        <a href="task_update_status.php?id=<?php echo (int)$r['id']; ?>" class="btn">Update Status</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>You have no assigned tasks at this time.</p>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>