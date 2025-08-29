<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_manager();

$action = $_GET['action'] ?? 'list';

if ($action === 'role' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $role = $_POST['role'] === 'manager' ? 'manager' : 'user';
    $stmt = $pdo->prepare("UPDATE users SET role=? WHERE id=?");
    $stmt->execute([$role, $id]);
    header('Location: /admin.php'); exit;
}

if ($action === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $newpass = trim($_POST['password'] ?? '');
    if (strlen($newpass) < 6) { $err = "Password must be 6+ characters."; }
    else {
        $stmt = $pdo->prepare("UPDATE users SET pass_hash=? WHERE id=?");
        $stmt->execute([password_hash($newpass, PASSWORD_DEFAULT), $id]);
        header('Location: /admin.php'); exit;
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id === current_user()['id']) {
        $err = "You cannot delete your own account.";
    } else {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        header('Location: /admin.php'); exit;
    }
}

$users = $pdo->query("SELECT id,email,name,role,created_at FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html><meta charset="utf-8">
<title>Admin — Users</title>
<div style="display:flex;justify-content:space-between;align-items:center">
  <h2>Admin — Users</h2>
  <div>
    <a href="/manager.php">Manager</a> | 
    <a href="/index.php">Home</a> | 
    <a href="/logout.php">Logout</a>
  </div>
</div>
<?php if (!empty($err)) echo "<p style='color:#b00'>".h($err)."</p>"; ?>

<table border="1" cellpadding="6" cellspacing="0">
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr>
  <?php foreach ($users as $u): ?>
  <tr>
    <td><?php echo (int)$u['id']; ?></td>
    <td><?php echo h($u['name'] ?: '—'); ?></td>
    <td><?php echo h($u['email']); ?></td>
    <td>
      <form method="post" action="/admin.php?action=role" style="display:inline">
        <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
        <select name="role">
          <option value="user" <?php echo $u['role']==='user'?'selected':''; ?>>user</option>
          <option value="manager" <?php echo $u['role']==='manager'?'selected':''; ?>>manager</option>
        </select>
        <button type="submit">Save</button>
      </form>
    </td>
    <td><?php echo h($u['created_at']); ?></td>
    <td>
      <details>
        <summary>Reset Password</summary>
        <form method="post" action="/admin.php?action=reset">
          <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
          <input type="password" name="password" placeholder="New password" required>
          <button type="submit">Update</button>
        </form>
      </details>
      <?php if ($u['id'] !== current_user()['id']): ?>
        <a href="/admin.php?action=delete&id=<?php echo (int)$u['id']; ?>" onclick="return confirm('Delete this user and all their trips?')">Delete</a>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
