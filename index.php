<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_login();

$u = current_user();
$action = $_GET['action'] ?? 'home';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['date'] ?? '');
    $ticket = trim($_POST['ticket'] ?? '');
    $client = trim($_POST['client'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $mileage = trim($_POST['mileage'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !is_numeric($mileage)) {
        $flash = 'Check date (YYYY-MM-DD) and mileage.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO trips (user_id,date,ticket,client,location,notes,mileage) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$u['id'], $date, $ticket, $client, $location, $notes, (float)$mileage]);
        header('Location: /index.php'); exit;
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM trips WHERE id=? AND user_id=?");
    $stmt->execute([intval($_GET['id']), $u['id']]);
    header('Location: /index.php'); exit;
}

if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $ticket = trim($_POST['ticket'] ?? '');
    $client = trim($_POST['client'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $mileage = trim($_POST['mileage'] ?? '');
    if ($id < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !is_numeric($mileage)) {
        $flash = 'Check inputs.';
    } else {
        $stmt = $pdo->prepare("UPDATE trips SET date=?, ticket=?, client=?, location=?, notes=?, mileage=? WHERE id=? AND user_id=?");
        $stmt->execute([$date,$ticket,$client,$location,$notes,(float)$mileage,$id,$u['id']]);
        header('Location: /index.php'); exit;
    }
}

$trips = $pdo->prepare("SELECT * FROM trips WHERE user_id=? ORDER BY date DESC, id DESC");
$trips->execute([$u['id']]);
$rows = $trips->fetchAll();

$tot = $pdo->prepare("SELECT SUM(mileage) AS total FROM trips WHERE user_id=?");
$tot->execute([$u['id']]);
$total = (float)($tot->fetch()['total'] ?? 0);
$fpdf = have_fpdf();
?>
<!doctype html><meta charset="utf-8">
<title>Mileage — Home</title>
<div style="display:flex;justify-content:space-between;align-items:center">
  <h2>Mileage — Welcome <?php echo h($u['name'] ?: $u['email']); ?></h2>
  <div>
    <?php if (is_manager()): ?><a href="/manager.php">Manager</a> | <a href="/admin.php">Admin</a> | <?php endif; ?>
    <a href="/frequent.php">Frequent trips</a> | 
    <a href="/export_csv.php">Export CSV</a> | 
    <?php if ($fpdf): ?><a href="/pdf_report.php">PDF Report</a> | <?php endif; ?>
    <a href="/logout.php">Logout</a>
  </div>
</div>

<?php if (!$fpdf): ?>
<p style="color:#555"><em>FPDF not detected. Add via Composer (<code>setasign/fpdf</code>) or place <code>lib/fpdf.php</code>.</em></p>
<?php endif; ?>

<?php if (!empty($flash)) echo "<p style='color:#b00'>".h($flash)."</p>"; ?>

<section style="border:1px solid #ddd;padding:12px;border-radius:8px;margin:10px 0">
  <h3>Add Trip</h3>
  <form method="post" action="/index.php?action=add">
    <label>Date</label><br><input type="date" name="date" required><br>
    <label>Ticket</label><br><input name="ticket"><br>
    <label>Client</label><br><input name="client"><br>
    <label>Location</label><br><input name="location"><br>
    <label>Notes</label><br><input name="notes"><br>
    <label>Mileage</label><br><input type="number" name="mileage" step="0.01" required><br><br>
    <button type="submit">Add</button>
  </form>
</section>

<h3>Your Trips (Total: <?php echo number_format($total,2); ?> mi)</h3>
<?php if (!$rows): ?>
  <p>No trips yet.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
  <tr><th>ID</th><th>Date</th><th>Ticket</th><th>Client</th><th>Location</th><th>Notes</th><th>Mileage</th><th>Actions</th></tr>
  <?php foreach ($rows as $t): ?>
  <tr>
    <td><?php echo (int)$t['id']; ?></td>
    <td><?php echo h($t['date']); ?></td>
    <td><?php echo h($t['ticket']); ?></td>
    <td><?php echo h($t['client']); ?></td>
    <td><?php echo h($t['location']); ?></td>
    <td><?php echo h($t['notes']); ?></td>
    <td><?php echo h(number_format((float)$t['mileage'],2)); ?></td>
    <td>
      <details>
        <summary>Edit</summary>
        <form method="post" action="/index.php?action=edit">
          <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
          <label>Date</label><br><input name="date" value="<?php echo h($t['date']); ?>"><br>
          <label>Ticket</label><br><input name="ticket" value="<?php echo h($t['ticket']); ?>"><br>
          <label>Client</label><br><input name="client" value="<?php echo h($t['client']); ?>"><br>
          <label>Location</label><br><input name="location" value="<?php echo h($t['location']); ?>"><br>
          <label>Notes</label><br><input name="notes" value="<?php echo h($t['notes']); ?>"><br>
          <label>Mileage</label><br><input type="number" name="mileage" step="0.01" value="<?php echo h((string)$t['mileage']); ?>"><br><br>
          <button type="submit">Save</button>
        </form>
      </details>
      <a href="/index.php?action=delete&id=<?php echo (int)$t['id']; ?>" onclick="return confirm('Delete?')">Delete</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
