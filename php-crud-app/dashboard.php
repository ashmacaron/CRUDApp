<?php
require_once __DIR__ . '/config/bootstrap.php';
requireLogin();

try {
    $users = db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')
                 ->fetchAll();
} catch (PDOException $e) {
    $users = [];
    setFlash('danger', 'Could not load users. Please try again later.');
}

$pageTitle = 'Dashboard';
require __DIR__ . '/resources/views/layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-people-fill me-2"></i>User Management</h4>
    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
    <a href="/php-crud-app/users/create.php" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add User
    </a>
    <?php endif; ?>
</div>

<!-- Stats cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="fs-1 fw-bold text-primary"><?= count($users) ?></div>
            <div class="text-muted small">Total Users</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card p-3 text-center">
            <div class="fs-1 fw-bold text-warning">
                <?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?>
            </div>
            <div class="text-muted small">Admins</div>
        </div>
    </div>
</div>

<!-- Users table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <th class="text-center">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $i => $user): ?>
                        <tr>
                            <td class="text-muted small"><?= $i + 1 ?></td>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-warning text-dark">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= e(date('M d, Y', strtotime($user['created_at']))) ?>
                            </td>
                            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                            <td class="text-center">
                                <a href="/php-crud-app/users/edit.php?id=<?= (int)$user['id'] ?>"
                                   class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="/php-crud-app/users/delete.php"
                                      class="d-inline"
                                      onsubmit="return confirm('Delete <?= e(addslashes($user['name'])) ?>? This cannot be undone.')">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/resources/views/layouts/footer.php'; ?>
