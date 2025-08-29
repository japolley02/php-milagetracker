<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_login();

if (!have_fpdf()) {
    http_response_code(500);
    echo "<h1>FPDF not available</h1><p>Install setasign/fpdf via Composer or place lib/fpdf.php.</p>";
    exit;
}

$period = trim($_GET['period'] ?? '');
$user_id = current_user()['id'];

if (is_manager() && isset($_GET['user_id'])) {
    $user_id = max(1, intval($_GET['user_id']));
}

function validPeriod(string $p): bool {
    return (bool)preg_match('/^\d{4}(-\d{2})?$/', $p);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $period === '') {
    // simple form
    ?>
    <!doctype html><meta charset="utf-8">
    <h2>PDF Report</h2>
    <form method="get">
      <label>Month (YYYY-MM) or Year (YYYY)</label><br>
      <input name="period" placeholder="2025-08 or 2025" required><br><br>
      <button type="submit">Open PDF</button>
    </form>
    <?php
    exit;
}

if (!validPeriod($period)) {
    http_response_code(400);
    echo "<h1>Bad Request</h1><p>Use YYYY or YYYY-MM.</p>";
    exit;
}

// Load FPDF
if (class_exists('FPDF') === false) {
    // loaded by have_fpdf()
    http_response_code(500);
    echo "FPDF class not loaded.";
    exit;
}

if (strlen($period) === 7) {
    $title = "Mileage Report for $period";
    $stmt = $pdo->prepare("SELECT date,ticket,client,location,notes,mileage FROM trips WHERE user_id=? AND DATE_FORMAT(date,'%Y-%m')=? ORDER BY date");
    $stmt->execute([$user_id, $period]);
} else {
    $title = "Mileage Report for $period";
    $stmt = $pdo->prepare("SELECT date,ticket,client,location,notes,mileage FROM trips WHERE user_id=? AND YEAR(date)=? ORDER BY date");
    $stmt->execute([$user_id, (int)$period]);
}
$rows = $stmt->fetchAll();
$total = 0.0; foreach ($rows as $r) { $total += (float)$r['mileage']; }

class PDF extends FPDF {
    public string $titleText = '';
    function Header() {
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10, $this->titleText,0,1,'L');
        $this->Ln(2);
        $this->SetFont('Arial','B',10);
        $this->Cell(0,7,'Date | Ticket | Client | Location | Notes | Mileage',0,1,'L');
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }
}

$pdf = new PDF('P','mm','Letter');
$pdf->titleText = $title;
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

foreach ($rows as $r) {
    $notes = mb_strimwidth((string)$r['notes'], 0, 60, '…','UTF-8');
    $line = sprintf("%s | %s | %s | %s | %s | %.2f",
        $r['date'], $r['ticket'], $r['client'], $r['location'], $notes, (float)$r['mileage']
    );
    $pdf->MultiCell(0,6,$line);
}
$pdf->Ln(2);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, sprintf("Total Mileage: %.2f mi", $total), 0, 1);

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="mileage_report.pdf"');
$pdf->Output('I');
exit;
