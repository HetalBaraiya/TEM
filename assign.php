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
      $msg = "You have been assigned to task #$task_id";
      $stmt2 = $mysqli->prepare("INSERT INTO notifications(user_id,message) VALUES(?,?)");
      $stmt2->bind_param("is",$uid,$msg);
      $stmt2->execute();
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
      $msg = "Your team has been assigned to task #$task_id";
      $stmt2 = $mysqli->prepare("INSERT INTO notifications(user_id,message) VALUES(?,?)");
      $stmt2->bind_param("is",$uid,$msg);
      $stmt2->execute();
    }
  }

  header("Location: ".BASE_PATH."assign.php"); exit;
}

// unassign
if(isset($_GET['unassign'])){
  $id = (int)$_GET['unassign'];
  $mysqli->query("DELETE FROM task_assignments WHERE id=$id");
  header("Location: ".BASE_PATH."assign.php"); exit;
}

$tasks = $mysqli->query("SELECT id, title FROM tasks ORDER BY title ASC");
$employees = $mysqli->query("SELECT id, full_name FROM users WHERE status='active' ORDER BY full_name ASC");
$teams = $mysqli->query("SELECT id, name FROM teams ORDER BY name ASC");

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Assign Task</h1>
  
  <div class="grid">
    <div class="card">
      <h2>Assign Task</h2>
      <form method="post" class="form">
        <div class="form-row">
          <label>Select Task</label>
          <select name="task_id" required>
            <option value="">Choose a task to assign</option>
            <?php while($t=$tasks->fetch_assoc()): ?>
              <option value="<?php echo (int)$t['id']; ?>"><?php echo esc($t['title']); ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-row">
          <label>Assign to Employees</label>
          <div class="checkbox-group">
            <?php while($e=$employees->fetch_assoc()): ?>
              <div class="checkbox-item">
                <input type="checkbox" name="user_ids[]" value="<?php echo (int)$e['id']; ?>" id="emp_<?php echo (int)$e['id']; ?>">
                <label for="emp_<?php echo (int)$e['id']; ?>"><?php echo esc($e['full_name']); ?></label>
              </div>
            <?php endwhile; ?>
          </div>
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

        <button class="btn" type="submit">Assign Task</button>
      </form>
    </div>

    <div class="card">
      <h2>Current Assignments</h2>
      <table>
        <thead>
          <tr>
            <th>Task</th>
            <th>Employee</th>
            <th>Assigned At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $q=$mysqli->query("
            SELECT a.id, t.title, u.full_name, a.assigned_at
            FROM task_assignments a
            JOIN tasks t ON t.id=a.task_id
            JOIN users u ON u.id=a.user_id
            ORDER BY a.assigned_at DESC
          ");
          while($r=$q->fetch_assoc()): ?>
            <tr>
              <td><?php echo esc($r['title']); ?></td>
              <td><?php echo esc($r['full_name']); ?></td>
              <td><?php echo esc($r['assigned_at']); ?></td>
              <td>
                <a class="btn" data-confirm="Remove assignment?" href="<?php echo BASE_PATH; ?>assign.php?unassign=<?php echo (int)$r['id']; ?>">Remove</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
