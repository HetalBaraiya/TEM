<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['employee','manager','admin']); // anyone can update their own tasks

$id = (int)($_GET['id'] ?? 0);
$u = $_SESSION['user'];

// ensure the task belongs to the user (unless admin/manager)
if($u['role']==='employee'){
  $uid = (int)$u['id'];
  $own = $mysqli->query("SELECT 1 FROM task_assignments WHERE task_id=$id AND user_id=$uid")->num_rows;
  if(!$own){ header("Location: forbidden.php"); exit; }
 }

if($_SERVER['REQUEST_METHOD']==='POST'){
  $status = $_POST['status'] ?? 'Pending';
  $stmt = $mysqli->prepare("UPDATE tasks SET status=? WHERE id=?");
  $stmt->bind_param("si",$status,$id);
  $stmt->execute();
  // notify managers/admins
  $msg = "Task #$id status updated to $status by " . $_SESSION['user']['full_name'];
  create_notifications_for_role('admin', $msg);
  create_notifications_for_role('manager', $msg);
  
  // If task is completed or cancelled, notify all assigned employees
  if ($status === 'Completed' || $status === 'Cancelled') {
    $assigned_employees = $mysqli->query("SELECT user_id FROM task_assignments WHERE task_id = $id");
    while ($emp = $assigned_employees->fetch_assoc()) {
      $emp_id = (int)$emp['user_id'];
      $msg = "Task #$id has been $status";
      create_notification($emp_id, $msg);
    }
  }
  
  header("Location: " . $u['role'] . "_dashboard.php"); exit;
 }

$task = $mysqli->query("SELECT * FROM tasks WHERE id=$id")->fetch_assoc();
include __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Update Task Status: <?php echo esc($task['title'] ?? ''); ?></h2>
  <form method="post">
    <label>Status</label>
    <select name="status">
      <?php foreach(['Pending','In Progress','Completed','Cancelled'] as $s): ?>
        <option value="<?php echo $s; ?>" <?php echo (($task['status'] ?? '')===$s)?'selected':''; ?>><?php echo $s; ?></option>
      <?php endforeach; ?>
    </select>
    <div style="margin-top: .75rem;">
      <button class="btn" type="submit">Save</button>
    </div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
