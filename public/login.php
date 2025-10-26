<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $pw = $_POST['password'];

    // Query user by username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Use PHP’s built-in password_verify()
        if (password_verify($pw, $user['password_hash'])) {
            unset($user['password_hash']);
            $_SESSION['user'] = $user;

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /admin.php');
            } else {
                header('Location: /student.php');
            }
            exit;
        } else {
            $error = 'Invalid password';
        }
    } else {
        $error = 'User not found';
    }
}

?>
<!doctype html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container py-5">
  <div class="card mx-auto" style="max-width:420px;">
    <div class="card-body">
      <h4>Login</h4>
      <?php if(!empty($error)):?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif;?>
      <form method="post">
        <input name="username" class="form-control mb-2" placeholder="username" required>
        <input name="password" type="password" class="form-control mb-2" placeholder="password" required>
        <button class="btn btn-primary w-100">Login</button>
      </form>
      <hr>
      
    </div>
  </div>
</div>
</body></html>