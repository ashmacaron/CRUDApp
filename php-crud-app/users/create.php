<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireAdmin();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $old      = compact('name', 'email', 'role');

    if ($name === '' || strlen($name) < 2)          $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (strlen($password) < 8)                      $errors[] = 'Password must be at least 8 characters.';

    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'Email already in use.';
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = db()->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $hash, $role]);
        setFlash('success', "User \"$name\" created successfully.");
        redirect('/php-crud-app/dashboard.php');
    }
}

$pageTitle = 'Add User';
require __DIR__ . '/../resources/views/layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h5 class="fw-bold mb-4"><i class="bi bi-person-plus me-2"></i>Add New User</h5>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="/php-crud-app/users/create.php" novalidate id="createForm">
        <?= csrfField() ?>

        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control"
                 value="<?= e($old['name'] ?? '') ?>" minlength="2" required>
          <div class="invalid-feedback">Name required (min 2 chars).</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control"
                 value="<?= e($old['email'] ?? '') ?>" required>
          <div class="invalid-feedback">Valid email required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Password <small class="text-muted">(min 8 chars)</small></label>
          <input type="password" name="password" class="form-control" minlength="8" required>
          <div class="invalid-feedback">Min 8 characters.</div>
        </div>

        <div class="mb-4">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <option value="user"  <?= (($old['role'] ?? '') !== 'admin') ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= (($old['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
          </select>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success">Create User</button>
          <a href="/php-crud-app/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('createForm').addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>

<?php require __DIR__ . '/../resources/views/layouts/footer.php'; ?>
