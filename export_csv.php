<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_login();

$user_id = current_user()['id'];
if (is_manager() && isset($_GET['user_id'])) {
    $user_id = max(1, intval($_GET['user_id']));
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="trips.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID','User ID','Date','Ticket','Client','Location','Notes','Mileage']);
$stmt = $pdo->prepare("SELECT * FROM trips WHERE user_id=? ORDER BY date DESC, id DESC");
$stmt->execute([$user_id]);
while ($row = $stmt->fetch()) {
    fputcsv($out, [$row['id'],$row['user_id'],$row['date'],$row['ticket'],$row['client'],$row['location'],$row['notes'],$row['mileage']]);
}
fclose($out);
