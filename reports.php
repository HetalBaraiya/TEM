<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['admin']);

// Filters
$status = $_GET['status'] ?? '';
$department = $_GET['department'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

$where = [];
if($status!=='') $where[] = "t.status='".$mysqli->real_escape_string($status)."'";
if($department!=='') $where[] = "u.department='".$mysqli->real_escape_string($department)."'";
if($date_from!=='') $where[] = "t.start_date>='".$mysqli->real_escape_string($date_from)."'";
if($date_to!=='') $where[] = "t.due_date<='".$mysqli->real_escape_string($date_to)."'";
$W = $where ? ('WHERE '.implode(' AND ',$where)) : '';

include __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Reports</h2>
  <form method="get" class="form-row two">
    <div>
      <label>Status</label>
      <select name="status">
        <option value="">Any</option>
        <?php foreach(['Pending','In Progress','Completed','Cancelled'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo ($status===$s)?'selected':''; ?>><?php echo $s; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Department</label>
      <input name="department" value="<?php echo esc($department); ?>">
    </div>
    <div>
      <label>From (Start)</label>
      <input type="date" name="from" value="<?php echo esc($date_from); ?>">
    </div>
    <div>
      <label>To (Due)</label>
      <input type="date" name="to" value="<?php echo esc($date_to); ?>">
    </div>
    <div>
      <button class="btn" type="submit">Apply</button>
    </div>
  </form>
</div>

<div class="card">
  <h3>Task-wise Employee Assignment Overview</h3>
  <table>
    <tr><th>Task</th><th>Status</th><th>Employees</th><th>Start</th><th>Due</th></tr>
    <?php
    $sql = "
      SELECT t.id, t.title, t.status, t.start_date, t.due_date,
             GROUP_CONCAT(DISTINCT u.full_name SEPARATOR ', ') AS employees
      FROM tasks t
      LEFT JOIN task_assignments a ON a.task_id=t.id
      LEFT JOIN users u ON u.id=a.user_id
      $W
      GROUP BY t.id
      ORDER BY t.due_date ASC
    ";
    $q = $mysqli->query($sql);
    while($r=$q->fetch_assoc()): ?>
      <tr>
        <td><?php echo esc($r['title']); ?></td>
        <td><?php echo esc($r['status']); ?></td>
        <td><?php echo esc($r['employees']); ?></td>
        <td><?php echo esc($r['start_date']); ?></td>
        <td><?php echo esc($r['due_date']); ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
