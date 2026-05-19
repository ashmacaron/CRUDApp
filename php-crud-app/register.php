<?php
require_once __DIR__ . '/config/bootstrap.php';

if (isLoggedIn()) redirect('/php-crud-app/dashboard.php');

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');
    $old      = compact('name', 'email');

    if ($name === '' || strlen($name) < 2)          $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 8)                      $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)                     $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, $hash]);
        setFlash('success', 'Account created! Please log in.');
        redirect('/php-crud-app/login.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/resources/views/layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card p-4 mt-3">
      <h4 class="mb-4 fw-bold text-center"><i class="bi bi-person-plus me-2"></i>Create Account</h4>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="/php-crud-app/register.php" novalidate id="registerForm">
        <?= csrfField() ?>

        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control"
                 value="<?= e($old['name'] ?? '') ?>" minlength="2" required>
          <div class="invalid-feedback">Name must be at least 2 characters.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control"
                 value="<?= e($old['email'] ?? '') ?>" required>
          <div class="invalid-feedback">Enter a valid email.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Password <small class="text-muted">(min 8 chars)</small></label>
          <input type="password" name="password" id="password" class="form-control" minlength="8" required>
          <div class="invalid-feedback">Password must be at least 8 characters.</div>
        </div>

        <div class="mb-4">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm" id="confirm" class="form-control" required>
          <div class="invalid-feedback">Passwords must match.</div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Register</button>
      </form>

      <p class="text-center mt-3 mb-0 small">
        Already have an account? <a href="/php-crud-app/login.php">Log in</a>
      </p>
    </div>
  </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function (e) {
    const password = document.getElementById('password');
    const confirm  = document.getElementById('confirm');
    confirm.setCustomValidity('');
    if (password.value !== confirm.value) confirm.setCustomValidity('Passwords must match.');
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>

<?php require __DIR__ . '/resources/views/layouts/footer.php'; ?>
