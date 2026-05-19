<?php
// config/bootstrap.php

ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 1800);

session_start();

require_once __DIR__ . '/database.php';

// 30-min session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    session_unset();
    session_destroy();
    header('Location: /php-crud-app/login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

// ── Helpers ───────────────────────────────────────────────────────────────

/** Escape output to prevent XSS */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirect and exit */
function redirect(string $url): never
{
    header("Location: $url");
    exit;
}

/** True if user is logged in */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Redirect to login if not logged in */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('/php-crud-app/login.php');
    }
}

/** Abort if not admin */
function requireAdmin(): void
{
    requireLogin();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die('Access denied.');
    }
}

// ── CSRF ──────────────────────────────────────────────────────────────────

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(419);
        die('CSRF token mismatch. Please go back and try again.');
    }
}

// ── Flash messages ────────────────────────────────────────────────────────

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
