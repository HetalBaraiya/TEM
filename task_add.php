<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin','manager']);

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
    
    if (!$errors) {
        $created_by = (int)$_SESSION['user']['id'];
        $stmt = $mysqli->prepare("INSERT INTO tasks(title,description,start_date,due_date,status,created_by) VALUES(?,?,?,?,?,?)");
        $stmt->bind_param("sssssi", $title,$description,$start_date,$due_date,$status,$created_by);
        $stmt->execute();
        
        header("Location: tasks.php");
        exit;
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="container">
  <h1>Create Task</h1>
  <?php if(!empty($errors)): ?>
    <div class="card" style="background:#2d1f1f;color:#ffb3b3;margin:1rem 0;">
      <ul>
        <?php foreach($errors as $e): ?>
          <li><?php echo esc($e); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <form method="post" class="form card" style="max-width:800px;margin:auto;padding:2rem;">
    <div class="form-row">
      <label>Title</label>
      <input name="title" required value="<?php echo esc($_POST['title'] ?? ''); ?>">
    </div>
    <div class="form-row">
      <label>Description</label>
      <textarea name="description" rows="4"><?php echo esc($_POST['description'] ?? ''); ?></textarea>
    </div>
    <div class="form-row two">
      <div>
        <label>Start Date</label>
        <input type="date" name="start_date" required value="<?php echo esc($_POST['start_date'] ?? ''); ?>">
      </div>
      <div>
        <label>Due Date</label>
        <input type="date" name="due_date" required value="<?php echo esc($_POST['due_date'] ?? ''); ?>">
      </div>
    </div>
    <div class="form-row">
      <label>Status</label>
      <select name="status">
        <?php foreach(['Pending','In Progress','Completed','Cancelled'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo (($_POST['status'] ?? 'Pending')===$s)?'selected':''; ?>><?php echo $s; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="margin-top:1.5rem;">
      <button class="btn" type="submit">Create Task</button>
      <a href="tasks.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
