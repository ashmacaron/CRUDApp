<?php
require_once __DIR__ . '/config/bootstrap.php';

if (isLoggedIn()) redirect('/php-crud-app/dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['name']          = $user['name'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['last_activity'] = time();
            redirect('/php-crud-app/dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/resources/views/layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card p-4 mt-3">
      <h4 class="mb-4 fw-bold text-center"><i class="bi bi-lock-fill me-2"></i>Sign In</h4>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['timeout'])): ?>
        <div class="alert alert-warning">Your session expired. Please log in again.</div>
      <?php endif; ?>

      <form method="POST" action="/php-crud-app/login.php" novalidate id="loginForm">
        <?= csrfField() ?>

        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required autofocus>
          <div class="invalid-feedback">Enter a valid email.</div>
        </div>

        <div class="mb-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
          <div class="invalid-feedback">Password is required.</div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Log In</button>
      </form>

      <p class="text-center mt-3 mb-0 small">
        No account yet? <a href="/php-crud-app/register.php">Register</a>
      </p>
    </div>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>

<?php require __DIR__ . '/resources/views/layouts/footer.php'; ?>
