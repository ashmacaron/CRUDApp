<?php
// setup.php
// ─────────────────────────────────────────────────────────────────────────────
// ONE-TIME SETUP SCRIPT
// Run this ONCE at: http://localhost/php-crud-app/setup.php
// It will:
//   1. Create the `crud_app` database and `users` table (if they don't exist)
//   2. Delete any existing admin@admin.com account
//   3. Insert a fresh admin with a properly hashed password
//
// DELETE THIS FILE after running it.
// ─────────────────────────────────────────────────────────────────────────────

// ── DB credentials (must match config/database.php) ──────────────────────
$host  = 'localhost';
$user  = 'root';
$pass  = '';         // XAMPP default: blank
$db    = 'crud_app';

try {
    // Connect without selecting a DB first so we can CREATE it
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // 1. Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");

    // 2. Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(100)         NOT NULL,
            email      VARCHAR(150)         NOT NULL UNIQUE,
            password   VARCHAR(255)         NOT NULL,
            role       ENUM('admin','user') NOT NULL DEFAULT 'user',
            created_at TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");

    // 3. Remove old admin if exists
    $pdo->prepare("DELETE FROM users WHERE email = 'admin@admin.com'")->execute();

    // 4. Hash password using THIS server's PHP — guaranteed to work
    $hash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);

    // 5. Insert fresh admin
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['Admin', 'admin@admin.com', $hash]);

    echo '
    <!DOCTYPE html>
    <html><head>
        <title>Setup Complete</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh">
    <div class="card p-5 shadow text-center" style="max-width:420px">
        <div class="display-4 mb-3">✅</div>
        <h4 class="fw-bold">Setup Complete!</h4>
        <p class="text-muted mb-1">Admin account ready:</p>
        <code class="d-block mb-1">admin@admin.com</code>
        <code class="d-block mb-4">Admin@1234</code>
        <a href="/php-crud-app/login.php" class="btn btn-primary">Go to Login</a>
        <p class="text-danger small mt-4">⚠️ Delete <strong>setup.php</strong> now!</p>
    </div>
    </body></html>';

} catch (PDOException $e) {
    echo '
    <!DOCTYPE html>
    <html><head>
        <title>Setup Failed</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light d-flex justify-content-center align-items-center" style="height:100vh">
    <div class="card p-5 shadow text-center border-danger" style="max-width:480px">
        <div class="display-4 mb-3">❌</div>
        <h4 class="fw-bold text-danger">Setup Failed</h4>
        <p class="text-muted">Check your DB credentials in <code>setup.php</code></p>
        <pre class="text-start bg-dark text-white p-3 rounded small">' . htmlspecialchars($e->getMessage()) . '</pre>
    </div>
    </body></html>';
}
