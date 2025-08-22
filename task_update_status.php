<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['employee','manager','admin']); // anyone can update their own tasks

// Ensure esc() exists
if (!function_exists('esc')) {
    function esc($str){
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

// Ensure create_notification() exists
if (!function_exists('create_notification')) {
    function create_notification($user_id, $message) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Helper function to notify all users of a role
if (!function_exists('create_notifications_for_role')) {
    function create_notifications_for_role($role, $message) {
        global $mysqli;
        $res = $mysqli->query("SELECT id FROM users WHERE role='" . $mysqli->real_escape_string($role) . "'");
        while ($u = $res->fetch_assoc()) {
            create_notification((int)$u['id'], $message);
        }
    }
}

$id = (int)($_GET['id'] ?? 0);
$u = $_SESSION['user'];

// Ensure the task belongs to the user (unless admin/manager)
if($u['role']==='employee'){
    $uid = (int)$u['id'];
    $own = $mysqli->query("SELECT 1 FROM task_assignments WHERE task_id=$id AND user_id=$uid")->num_rows;
    if(!$own){ 
        header("Location: forbidden.php"); 
        exit; 
    }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $status = $_POST['status'] ?? 'Pending';
    
    // Update task status
    $stmt = $mysqli->prepare("UPDATE tasks SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    // Notify admins and managers
    $msg_admin_mgr = "Task #$id status updated to $status by " . $u['full_name'];
    create_notifications_for_role('admin', $msg_admin_mgr);
    create_notifications_for_role('manager', $msg_admin_mgr);

    // Notify assigned employees if Completed or Cancelled
    if ($status === 'Completed' || $status === 'Cancelled') {
        $assigned_employees = $mysqli->query("SELECT user_id FROM task_assignments WHERE task_id = $id");
        while ($emp = $assigned_employees->fetch_assoc()) {
            $emp_id = (int)$emp['user_id'];
            $msg_emp = "Task #$id has been $status";
            create_notification($emp_id, $msg_emp);
        }
    }

    // Redirect users to their dashboards
    header("Location: " . $u['role'] . "_dashboard.php");
    exit;
}

// Fetch task for display
$task = $mysqli->query("SELECT * FROM tasks WHERE id=$id")->fetch_assoc();

include __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Update Task Status: <?php echo esc($task['title'] ?? ''); ?></h2>
  <form method="post">
    <label>Status</label>
    <select name="status">
      <?php foreach(['Pending','In Progress','Completed','Cancelled'] as $s): ?>
        <option value="<?php echo $s; ?>" <?php echo (($task['status'] ?? '')===$s)?'selected':''; ?>>
          <?php echo $s; ?>
        </option>
      <?php endforeach; ?>
    </select>
    <div style="margin-top: .75rem;">
      <button class="btn" type="submit">Save</button>
    </div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
