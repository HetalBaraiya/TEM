<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

// assign
if($_SERVER['REQUEST_METHOD']==='POST'){
  $task_id = (int)($_POST['task_id'] ?? 0);
  $by = (int)$_SESSION['user']['id'];

  // Case 1: Assign to employees
  if (!empty($_POST['user_ids'])) {
    foreach($_POST['user_ids'] as $uid){
      $uid = (int)$uid;
      $stmt = $mysqli->prepare("INSERT IGNORE INTO task_assignments(task_id,user_id,assigned_by) VALUES(?,?,?)");
      $stmt->bind_param("iii",$task_id,$uid,$by);
      $stmt->execute();

      // notify employee
      $task_info = $mysqli->query("SELECT title FROM tasks WHERE id = $task_id")->fetch_assoc();
      $msg = "You have been assigned to task '" . ($task_info['title'] ?? "Task #$task_id") . "'";
      create_notification($uid, $msg);
    }
  }

  // Case 2: Assign to team
  if (!empty($_POST['team_id'])) {
    $team_id = (int)$_POST['team_id'];
    $members = $mysqli->query("SELECT user_id FROM team_members WHERE team_id=$team_id");
    while($m = $members->fetch_assoc()){
      $uid = (int)$m['user_id'];
      $stmt = $mysqli->prepare("INSERT IGNORE INTO task_assignments(task_id,user_id,assigned_by) VALUES(?,?,?)");
      $stmt->bind_param("iii",$task_id,$uid,$by);
      $stmt->execute();

      // notify member
      $task_info = $mysqli->query("SELECT title FROM tasks WHERE id = $task_id")->fetch_assoc();
      $msg = "Your team has been assigned to task '" . ($task_info['title'] ?? "Task #$task_id") . "'";
      create_notification($uid, $msg);
    }
  }

  header("Location: assign.php"); exit;
 }

// unassign
if(isset($_GET['unassign'])){
  $id = (int)$_GET['unassign'];
  // Get task info before deleting
  $assignment_info = $mysqli->query("SELECT ta.task_id, ta.user_id, t.title FROM task_assignments ta JOIN tasks t ON ta.task_id = t.id WHERE ta.id = $id")->fetch_assoc();
  if ($assignment_info) {
    $task_title = $assignment_info['title'] ?? "Task #" . $assignment_info['task_id'];
    $user_id = (int)$assignment_info['user_id'];
    $msg = "You have been unassigned from task '$task_title'";
    create_notification($user_id, $msg);
  }
  
  $mysqli->query("DELETE FROM task_assignments WHERE id=$id");
  header("Location: assign.php"); exit;
}

// update assignment
if(isset($_POST['update_assignment'])){
  $id = (int)$_POST['assignment_id'];
  $new_user_id = (int)$_POST['new_user_id'];
  $assigned_by = (int)$_SESSION['user']['id'];
  
  // Update the assignment
  $stmt = $mysqli->prepare("UPDATE task_assignments SET user_id=?, assigned_by=? WHERE id=?");
  $stmt->bind_param("iii", $new_user_id, $assigned_by, $id);
  $stmt->execute();
  
  // Notify the employee about the assignment update
  $task_info = $mysqli->query("SELECT title FROM tasks WHERE id = $task_id")->fetch_assoc();
  $msg = "Your assignment for task '" . ($task_info['title'] ?? "Task #$task_id") . "' has been updated";
  create_notification($new_user_id, $msg);
  
  header("Location: assign.php"); exit;
}

$tasks = $mysqli->query("SELECT id, title FROM tasks ORDER BY title ASC");
$employees = $mysqli->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
$teams = $mysqli->query("SELECT id, name FROM teams ORDER BY name ASC");

include __DIR__ . '/partials/header.php';
?>
<style>
.assignment-form {
  display: flex;
  align-items: center;
  gap: 5px;
}
.assignment-form select {
  padding: 2px;
  font-size: 0.9em;
}
.assignment-form button {
  padding: 2px 5px;
  font-size: 0.8em;
}
</style>
<div class="grid">
  <div class="card">
    <h2>Assign Task</h2>
    <form method="post" action="assign.php">
      <div class="form-row">
        <label>Task</label>
        <select name="task_id" required>
          <option value="">Select a task</option>
          <?php while($t=$tasks->fetch_assoc()): ?>
            <option value="<?php echo (int)$t['id']; ?>"><?php echo esc($t['title']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-row">
        <label>Assign to Employees (Ctrl/Cmd + Click for multiple)</label>
        <select name="user_ids[]" multiple size="6">
          <?php while($e=$employees->fetch_assoc()): ?>
            <option value="<?php echo (int)$e['id']; ?>"><?php echo esc($e['full_name']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-row">
        <label>Or Assign to a Team</label>
        <select name="team_id">
          <option value="">-- Select a Team --</option>
          <?php while($tm=$teams->fetch_assoc()): ?>
            <option value="<?php echo (int)$tm['id']; ?>"><?php echo esc($tm['name']); ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <button class="btn" type="submit">Assign</button>
    </form>
  </div>

  <div class="card">
    <h2>Current Assignments</h2>
    <table>
      <tr><th>Task</th><th>Employee</th><th>Assigned By</th><th>Assigned At</th><th>Actions</th></tr>
      <?php
      $q=$mysqli->query("
        SELECT a.id, t.title, u.full_name, u.id as user_id, assigner.full_name as assigned_by_name, a.assigned_at
        FROM task_assignments a
        JOIN tasks t ON t.id=a.task_id
        JOIN users u ON u.id=a.user_id
        JOIN users assigner ON assigner.id=a.assigned_by
        ORDER BY a.assigned_at DESC
      ");
      while($r=$q->fetch_assoc()): ?>
        <tr>
          <td><?php echo esc($r['title']); ?></td>
          <td><?php echo esc($r['full_name']); ?></td>
          <td><?php echo esc($r['assigned_by_name']); ?></td>
          <td><?php echo esc($r['assigned_at']); ?></td>
          <td>
            <!-- Update form -->
            <form method="post" class="assignment-form">
              <input type="hidden" name="assignment_id" value="<?php echo (int)$r['id']; ?>">
              <select name="new_user_id">
                <?php
                // Get all employees for the dropdown
                $employees_list = $mysqli->query("SELECT id, full_name FROM users WHERE role='employee' ORDER BY full_name ASC");
                while($emp = $employees_list->fetch_assoc()): ?>
                  <option value="<?php echo (int)$emp['id']; ?>" <?php echo ($emp['id'] == $r['user_id']) ? 'selected' : ''; ?>>
                    <?php echo esc($emp['full_name']); ?>
                  </option>
                <?php endwhile; ?>
              </select>
              <input type="hidden" name="update_assignment" value="1">
              <button type="submit" class="btn">Update</button>
            </form>
            <!-- Remove link -->
            <a class="btn" data-confirm="Remove assignment?" href="assign.php?unassign=<?php echo (int)$r['id']; ?>">Remove</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
