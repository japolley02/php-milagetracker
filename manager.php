<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_manager();

$view_user = intval($_GET['user_id'] ?? 0);
$fpdf = have_fpdf();

if ($view_user > 0) {
    $u = $pdo->prepare("SELECT id,email,name FROM users WHERE id=?");
    $u->execute([$view_user]);
    $usr = $u->fetch();
    if (!$usr) { echo "<p>User not found.</p>"; exit; }

    $tr = $pdo->prepare("SELECT * FROM trips WHERE user_id=? ORDER BY date DESC, id DESC");
    $tr->execute([$view_user]);
    $rows = $tr->fetchAll();

    $tot = $pdo->prepare("SELECT SUM(mileage) AS total FROM trips WHERE user_id=?");
    $tot->execute([$view_user]);
    $total = (float)($tot->fetch()['total'] ?? 0);
    ?>
    <!doctype html><meta charset="utf-8">
    <h2>Manager — Trips for <?php echo h($usr['name'] ?: $usr['email']); ?></h2>
    <p>
      <a href="/manager.php">← Back to overview</a> | 
      <a href="/export_csv.php?user_id=<?php echo $usr['id']; ?>">Export CSV</a>
      <?php if ($fpdf): ?> | 
      <a href="/pdf_report.php?user_id=<?php echo $usr['id']; ?>">PDF Report</a>
      <?php endif; ?>
    </p>
    <p>Total: <?php echo number_format($total,2); ?> mi</p>
    <table border="1" cellpadding="6" cellspacing="0">
      <tr><th>ID</th><th>Date</th><th>Ticket</th><th>Client</th><th>Location</th><th>Notes</th><th>Mileage</th></tr>
      <?php foreach ($rows as $t): ?>
      <tr>
        <td><?php echo (int)$t['id']; ?></td>
        <td><?php echo h($t['date']); ?></td>
        <td><?php echo h($t['ticket']); ?></td>
        <td><?php echo h($t['client']); ?></td>
        <td><?php echo h($t['location']); ?></td>
        <td><?php echo h($t['notes']); ?></td>
        <td><?php echo h(number_format((float)$t['mileage'],2)); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php
    exit;
}

$users = $pdo->query("SELECT id,email,name,role,created_at FROM users ORDER BY created_at DESC")->fetchAll();

$totals = $pdo->query("SELECT user_id, SUM(mileage) AS total FROM trips GROUP BY user_id")->fetchAll();
$map = [];
foreach ($totals as $r) $map[$r['user_id']] = (float)$r['total'];
?>
<!doctype html><meta charset="utf-8">
<title>Manager Portal</title>
<div style="display:flex;justify-content:space-between;align-items:center">
  <h2>Manager — Overview</h2>
  <div>
    <a href="/frequent.php">Frequent</a> | 
    <a href="/admin.php">Admin</a> | 
    <a href="/index.php">Home</a> | 
    <a href="/logout.php">Logout</a>
  </div>
</div>

<table border="1" cellpadding="6" cellspacing="0">
  <tr><th>User</th><th>Email</th><th>Role</th><th>Total Mileage</th><th>Actions</th></tr>
  <?php foreach ($users as $u): ?>
  <tr>
    <td><?php echo h($u['name'] ?: '—'); ?></td>
    <td><?php echo h($u['email']); ?></td>
    <td><?php echo h($u['role']); ?></td>
    <td><?php echo h(number_format($map[$u['id']] ?? 0, 2)); ?></td>
    <td>
      <a href="/manager.php?user_id=<?php echo (int)$u['id']; ?>">View trips</a>
      <?php if ($fpdf): ?> | <a href="/pdf_report.php?user_id=<?php echo (int)$u['id']; ?>">PDF</a><?php endif; ?>
      | <a href="/export_csv.php?user_id=<?php echo (int)$u['id']; ?>">CSV</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
