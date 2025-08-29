<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT id,email,name,pass_hash,role FROM users WHERE email=?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($pass, $u['pass_hash'])) {
        $_SESSION['user'] = ['id'=>$u['id'], 'email'=>$u['email'], 'name'=>$u['name'], 'role'=>$u['role']];
        header('Location: /index.php'); exit;
    } else {
        $err = "Invalid credentials.";
    }
}
?>
<!doctype html><meta charset="utf-8">
<h2>Login</h2>
<?php if (!empty($_GET['ok'])) echo "<p style='color:#070'>Account created. Please login.</p>"; ?>
<?php if (!empty($err)) echo "<p style='color:#b00'>".h($err)."</p>"; ?>
<form method="post">
  <label>Email</label><br><input name="email" required><br>
  <label>Password</label><br><input type="password" name="password" required><br><br>
  <button type="submit">Login</button>
</form>
<p><a href="/register.php">Register</a></p>
