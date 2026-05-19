<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireAdmin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect('/php-crud-app/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $role  = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $newPw = trim($_POST['password'] ?? '');
    $user  = array_merge($user, compact('name', 'email', 'role'));

    if ($name === '' || strlen($name) < 2)          $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if ($newPw !== '' && strlen($newPw) < 8)        $errors[] = 'New password must be at least 8 characters.';

    if (empty($errors)) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) $errors[] = 'Email already used by another account.';
    }

    if (empty($errors)) {
        if ($newPw !== '') {
            $hash = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = db()->prepare('UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?');
            $stmt->execute([$name, $email, $role, $hash, $id]);
        } else {
            $stmt = db()->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
            $stmt->execute([$name, $email, $role, $id]);
        }
        setFlash('success', "User \"$name\" updated.");
        redirect('/php-crud-app/dashboard.php');
    }
}

$pageTitle = 'Edit User';
require __DIR__ . '/../resources/views/layouts/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card p-4">
      <h5 class="fw-bold mb-4"><i class="bi bi-pencil-square me-2"></i>Edit User</h5>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="/php-crud-app/users/edit.php" novalidate id="editForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control"
                 value="<?= e($user['name']) ?>" minlength="2" required>
          <div class="invalid-feedback">Name required (min 2 chars).</div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control"
                 value="<?= e($user['email']) ?>" required>
          <div class="invalid-feedback">Valid email required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label">
            New Password <small class="text-muted">(leave blank to keep current)</small>
          </label>
          <input type="password" name="password" class="form-control" minlength="8">
          <div class="invalid-feedback">Min 8 characters.</div>
        </div>

        <div class="mb-4">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <option value="user"  <?= $user['role'] !== 'admin' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
          </select>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="/php-crud-app/dashboard.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('editForm').addEventListener('submit', function (e) {
    const pw = this.querySelector('[name="password"]');
    pw.removeAttribute('required');
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
});
</script>

<?php require __DIR__ . '/../resources/views/layouts/footer.php'; ?>
