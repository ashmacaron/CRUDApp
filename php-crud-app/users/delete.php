<?php
require_once __DIR__ . '/../config/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/php-crud-app/dashboard.php');
}

verifyCsrf();

$id = (int)($_POST['id'] ?? 0);

if ($id === (int)$_SESSION['user_id']) {
    setFlash('danger', 'You cannot delete your own account.');
    redirect('/php-crud-app/dashboard.php');
}

$stmt = db()->prepare('SELECT name FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect('/php-crud-app/dashboard.php');
}

$stmt = db()->prepare('DELETE FROM users WHERE id = ?');
$stmt->execute([$id]);

setFlash('success', "User \"{$user['name']}\" deleted.");
redirect('/php-crud-app/dashboard.php');
