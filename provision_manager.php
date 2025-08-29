<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
session_start();

$tok = $_GET['token'] ?? '';
if (!$tok || !isset($_SESSION['provision_token']) || !hash_equals($_SESSION['provision_token'], $tok)) {
    http_response_code(403);
    echo "<h1>Forbidden</h1><p>Invalid or expired token.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        $err = "Provide a valid email and a password (6+ chars).";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (email,name,pass_hash,role) VALUES (?,?,?, 'manager')");
        $stmt->execute([$email, $name, password_hash($pass, PASSWORD_DEFAULT)]);
        unset($_SESSION['provision_token']);
        echo "<p>Manager created. <a href='/login.php'>Login</a>.</p>";
        exit;
    }
}
?>
<!doctype html><meta charset="utf-8">
<h2>Create first manager</h2>
<?php if (!empty($err)) echo "<p style='color:#b00'>".h($err)."</p>"; ?>
<form method="post">
  <label>Email</label><br><input name="email" required><br>
  <label>Name</label><br><input name="name"><br>
  <label>Password</label><br><input type="password" name="password" required><br><br>
  <button type="submit">Create Manager</button>
</form>
