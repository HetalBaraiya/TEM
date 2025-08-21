<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

$id = (int)($_GET['id'] ?? 0);
$res = $mysqli->query("SELECT * FROM tasks WHERE id=$id");
$task = $res ? $res->fetch_assoc() : null;
if (!$task) { die('Task not found'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $status = $_POST['status'] ?? 'Pending';
    
    // Validations
    if ($title === '' || mb_strlen($title) < 3 || mb_strlen($title) > 200) {
        $errors[] = 'Title must be between 3 and 200 characters.';
    }
    if ($start_date === '') {
        $errors[] = 'Start date is required.';
    }
    if ($due_date === '') {
        $errors[] = 'Due date is required.';
    }
    if ($start_date && $due_date && $start_date > $due_date) {
        $errors[] = 'Start date cannot be after due date.';
    }
    
    // Check for unique task title (excluding current task)
    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT id FROM tasks WHERE title = ? AND id != ?");
        $stmt->bind_param("si", $title, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = 'A task with this title already exists.';
        }
        $stmt->close();
    }
    
    if (!$errors) {
        $stmt = $mysqli->prepare("UPDATE tasks SET title=?, description=?, start_date=?, due_date=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $title,$description,$start_date,$due_date,$status,$id);
        $stmt->execute();
        
        // Notify assigned employees about the task update
        $assigned_employees = $mysqli->query("SELECT user_id FROM task_assignments WHERE task_id = $id");
        while ($emp = $assigned_employees->fetch_assoc()) {
            $emp_id = (int)$emp['user_id'];
            $msg = "Task '" . $title . "' has been updated";
            create_notification($emp_id, $msg);
        }
        
        header("Location: tasks.php");
        exit;
    } else {
        // Overwrite form with latest attempt
        $task['title'] = $title;
        $task['description'] = $description;
        $task['start_date'] = $start_date;
        $task['due_date'] = $due_date;
        $task['status'] = $status;
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Edit Task</h1>
  <?php if(!empty($errors)): ?>
    <div class="card" style="background:#2d1f1f;color:#ffb3b3;margin:1rem 0;">
      <ul>
        <?php foreach($errors as $e): ?>
          <li><?php echo esc($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <form id="task-form" method="post" class="form card" style="max-width:800px;margin:auto;padding:2rem;">
    <div class="form-row">
      <label>Title</label>
      <input name="title" required value="<?php echo esc($task['title']); ?>">
    </div>
    <div class="form-row">
      <label>Description</label>
      <textarea name="description" rows="4"><?php echo esc($task['description']); ?></textarea>
    </div>
    <div class="form-row two">
      <div>
        <label>Start Date</label>
        <input type="date" name="start_date" required value="<?php echo esc($task['start_date']); ?>">
      </div>
      <div>
        <label>Due Date</label>
        <input type="date" name="due_date" required value="<?php echo esc($task['due_date']); ?>">
      </div>
    </div>
    <div class="form-row">
      <label>Status</label>
      <select name="status">
        <?php foreach(['Pending','In Progress','Completed','Cancelled'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php if($task['status']===$s) echo 'selected'; ?>><?php echo $s; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-top:1.5rem;">
      <button class="btn" type="submit">Update Task</button>
      <a href="tasks.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
