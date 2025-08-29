<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_login();

$action = $_GET['action'] ?? 'home';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $client = trim($_POST['client'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $mileage = trim($_POST['mileage'] ?? '');
    if ($name === '' || !is_numeric($mileage)) {
        $flash = 'Provide name and numeric mileage.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO frequent_trips (name,client,location,mileage) VALUES (?,?,?,?)");
            $stmt->execute([$name,$client,$location,(float)$mileage]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) $flash = 'Name must be unique.';
            else $flash = 'Save failed: ' . $e->getMessage();
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM frequent_trips WHERE id=?");
    $stmt->execute([intval($_GET['id'])]);
    header('Location: /frequent.php'); exit;
}

$freq = $pdo->query("SELECT * FROM frequent_trips ORDER BY name")->fetchAll();
?>
<!doctype html><meta charset="utf-8">
<title>Frequent Trips</title>
<div style="display:flex;justify-content:space-between;align-items:center">
  <h2>Frequent Trips (shared)</h2>
  <div>
    <?php if (is_manager()): ?><a href="/manager.php">Manager</a> | <a href="/admin.php">Admin</a> | <?php endif; ?>
    <a href="/index.php">Home</a> | 
    <a href="/logout.php">Logout</a>
  </div>
</div>

<?php if (!empty($flash)) echo "<p style='color:#b00'>".h($flash)."</p>"; ?>

<section style="border:1px solid #ddd;padding:12px;border-radius:8px;margin:10px 0">
  <h3>Add Frequent Trip</h3>
  <form method="post" action="/frequent.php?action=add">
    <label>Name (unique)</label><br><input name="name" required><br>
    <label>Client</label><br><input name="client"><br>
    <label>Location</label><br><input name="location"><br>
    <label>Mileage</label><br><input type="number" name="mileage" step="0.01" required><br><br>
    <button type="submit">Save</button>
  </form>
</section>

<h3>All Frequent Trips</h3>
<?php if (!$freq): ?>
  <p>No frequent trips yet.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
  <tr><th>Name</th><th>Client</th><th>Location</th><th>Mileage</th><th>Actions</th></tr>
  <?php foreach ($freq as $f): ?>
  <tr>
    <td><?php echo h($f['name']); ?></td>
    <td><?php echo h($f['client']); ?></td>
    <td><?php echo h($f['location']); ?></td>
    <td><?php echo h(number_format((float)$f['mileage'],2)); ?></td>
    <td><a href="/frequent.php?action=delete&id=<?php echo (int)$f['id']; ?>" onclick="return confirm('Delete frequent?')">Delete</a></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
