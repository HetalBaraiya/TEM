<?php
require_once __DIR__ . '/includes/auth.php';
require_role(['manager','admin']); // allow managers and admins

$editing = false;
$editRow = null;

// CREATE or UPDATE TEAM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = (int)($_POST['id'] ?? 0);
    $team_name = trim($_POST['team_name'] ?? '');
    $user_ids = $_POST['user_ids'] ?? [];
    $created_by = (int)$_SESSION['user']['id'];

    if ($team_name !== '' && count($user_ids) > 0) {
        if ($team_id) {
            // Update team name
            $stmt = $mysqli->prepare("UPDATE teams SET name=? WHERE id=? AND created_by=?");
            $stmt->bind_param("sii", $team_name, $team_id, $created_by);
            $stmt->execute();

            // Remove old members
            $mysqli->query("DELETE FROM team_members WHERE team_id=$team_id");

            // Insert new members
            foreach ($user_ids as $uid) {
                $uid = (int)$uid;
                $stmt2 = $mysqli->prepare("INSERT INTO team_members (team_id, user_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $team_id, $uid);
                $stmt2->execute();
            }
        } else {
            // Insert new team
            $stmt = $mysqli->prepare("INSERT INTO teams (name, created_by) VALUES (?, ?)");
            $stmt->bind_param("si", $team_name, $created_by);
            $stmt->execute();
            $team_id = $stmt->insert_id;

            // Assign members
            foreach ($user_ids as $uid) {
                $uid = (int)$uid;
                $stmt2 = $mysqli->prepare("INSERT INTO team_members (team_id, user_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $team_id, $uid);
                $stmt2->execute();
            }
        }
        header("Location: teams.php");
        exit;
    }
}

// DELETE TEAM
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $mysqli->query("DELETE FROM teams WHERE id=$id AND created_by=".(int)$_SESSION['user']['id']);
    header("Location: teams.php");
    exit;
}

// FETCH TEAM for EDIT
if (isset($_GET['edit'])) {
    $editing = true;
    $id = (int)$_GET['edit'];
    $res = $mysqli->query("SELECT * FROM teams WHERE id=$id AND created_by=".(int)$_SESSION['user']['id']);
    $editRow = $res->fetch_assoc();

    // Fetch current members
    $memberRes = $mysqli->query("SELECT user_id FROM team_members WHERE team_id=$id");
    $editMembers = [];
    while ($m = $memberRes->fetch_assoc()) {
        $editMembers[] = $m['user_id'];
    }
}

// FETCH teams with members (only teams created by this manager)
$q = $mysqli->query("
    SELECT t.id, t.name, t.created_at, u.full_name AS created_by
    FROM teams t
    JOIN users u ON u.id = t.created_by
    WHERE t.created_by=".(int)$_SESSION['user']['id']."
    ORDER BY t.created_at DESC
");

include __DIR__ . '/partials/header.php';
?>

<div class="container">
  <h1>Teams</h1>
  <a class="btn-add" href="teams.php">Create Team</a>

  <div class="grid">
    <div class="card">
      <h2><?php echo $editing ? 'Edit Team' : 'Create Team'; ?></h2>
      <form method="post" class="form">
        <input type="hidden" name="id" value="<?php echo (int)($editRow['id'] ?? 0); ?>">
        <div class="form-row">
          <label>Team Name</label>
          <input name="team_name" required value="<?php echo esc($editRow['name'] ?? ''); ?>">
        </div>
        <div class="form-row">
          <label>Select Employees</label>
          <div class="checkbox-group">
            <?php
            $employees = $mysqli->query("SELECT id, full_name FROM users WHERE role='employee' ORDER BY full_name ASC");
            while ($e = $employees->fetch_assoc()):
            ?>
              <div class="checkbox-item">
                <input type="checkbox" name="user_ids[]" value="<?php echo (int)$e['id']; ?>" 
                       id="emp_<?php echo (int)$e['id']; ?>"
                       <?php echo (isset($editMembers) && in_array($e['id'], $editMembers)) ? 'checked' : ''; ?>>
                <label for="emp_<?php echo (int)$e['id']; ?>"><?php echo esc($e['full_name']); ?></label>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
        <button class="btn" type="submit"><?php echo $editing ? 'Update Team' : 'Create Team'; ?></button>
      </form>
    </div>

    <div class="card">
      <h2>My Teams</h2>
      <table>
        <thead>
          <tr>
            <th>Team</th>
            <th>Created At</th>
            <th>Members</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($t = $q->fetch_assoc()): ?>
            <tr>
              <td><?php echo esc($t['name']); ?></td>
              <td><?php echo esc($t['created_at']); ?></td>
              <td>
                <?php
                $tm = $mysqli->query("
                  SELECT u.full_name 
                  FROM team_members tm 
                  JOIN users u ON u.id = tm.user_id 
                  WHERE tm.team_id = " . (int)$t['id']
                );
                $names = [];
                while ($m = $tm->fetch_assoc()) {
                  $names[] = esc($m['full_name']);
                }
                echo implode(', ', $names);
                ?>
              </td>
              <td>
                <a class="btn" href="teams.php?edit=<?php echo (int)$t['id']; ?>">Edit</a>
                <a class="btn" href="teams.php?del=<?php echo (int)$t['id']; ?>" onclick="return confirm('Delete this team?')">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
