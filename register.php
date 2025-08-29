<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        $err = "Provide a valid email and a password (6+ chars).";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (email,name,pass_hash,role) VALUES (?,?,?, 'user')");
            $stmt->execute([$email, $name, password_hash($pass, PASSWORD_DEFAULT)]);
            header('Location: /login.php?ok=1'); exit;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                $err = "Email already in use.";
            } else {
                $err = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html><meta charset="utf-8">
<h2>Register</h2>
<?php if (!empty($_GET['ok'])) echo "<p style='color:#070'>Account created. Please login.</p>"; ?>
<?php if (!empty($err)) echo "<p style='color:#b00'>".h($err)."</p>"; ?>
<form method="post">
  <label>Email</label><br><input name="email" required><br>
  <label>Name</label><br><input name="name"><br>
  <label>Password</label><br><input type="password" name="password" required><br><br>
  <button type="submit">Create Account</button>
</form>
<p><a href="/login.php">Login</a></p>
