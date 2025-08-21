<?php
// Simple test script to verify assignment functionality
require_once __DIR__ . '/includes/auth.php';

// Only allow admin or manager to access this test
require_role(['admin', 'manager']);

include __DIR__ . '/partials/header.php';
?>

<div class="container">
    <h1>Assignment Test</h1>
    
    <div class="card">
        <h2>Test Assignment Functionality</h2>
        <p>This page helps verify that task assignment is working correctly.</p>
        
        <h3>Steps to Test:</h3>
        <ol>
            <li>Go to <a href="assign.php">Assign Task</a> page</li>
            <li>Select a task and assign it to an employee</li>
            <li>Login as that employee and check their <a href="employee_dashboard.php">dashboard</a></li>
            <li>Verify the task appears in their assigned tasks list</li>
            <li>Click "Update Status" and change the task status</li>
            <li>Verify the status updates correctly</li>
        </ol>
        
        <h3>Current Assignments:</h3>
        <?php
        $q = $mysqli->query("
            SELECT 
                a.id, 
                t.title as task_title, 
                u.full_name as employee_name, 
                assigner.full_name as assigned_by_name, 
                a.assigned_at,
                t.status
            FROM task_assignments a
            JOIN tasks t ON t.id = a.task_id
            JOIN users u ON u.id = a.user_id
            JOIN users assigner ON assigner.id = a.assigned_by
            ORDER BY a.assigned_at DESC
        ");
        
        if ($q->num_rows > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Employee</th>
                    <th>Assigned By</th>
                    <th>Status</th>
                    <th>Assigned At</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = $q->fetch_assoc()): ?>
                <tr>
                    <td><?php echo esc($r['task_title']); ?></td>
                    <td><?php echo esc($r['employee_name']); ?></td>
                    <td><?php echo esc($r['assigned_by_name']); ?></td>
                    <td><?php echo esc($r['status']); ?></td>
                    <td><?php echo esc($r['assigned_at']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p>No tasks have been assigned yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>