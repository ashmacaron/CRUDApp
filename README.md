[README.md](https://github.com/user-attachments/files/28003634/README.md)
# PHP CRUD App — Complete Setup & User Guide

A secure Login & Registration system with a full user management dashboard.
Built with **PHP 8**, **PDO**, **Bootstrap 5**, and no extra frameworks needed.

---

## Table of Contents

1. [Requirements](#requirements)
2. [Folder Structure](#folder-structure)
3. [Installation on XAMPP](#installation-on-xampp)
4. [First Login](#first-login)
5. [Using the App](#using-the-app)
6. [Security Features](#security-features)
7. [Troubleshooting](#troubleshooting)

---

## Requirements

| Software | Version |
|---|---|
| XAMPP | Any recent version |
| PHP | 8.0 or higher |
| MySQL | 5.7+ (included in XAMPP) |
| Browser | Any modern browser |

---

## Folder Structure

```
php-crud-app/                   ← Put this inside C:\xampp\htdocs\
│
├── config/
│   ├── bootstrap.php           Session setup, security helpers, CSRF, flash messages
│   └── database.php            PDO database connection (edit credentials here)
│
├── database/
│   └── schema.sql              Database & table structure (optional — setup.php does this too)
│
├── resources/
│   └── views/
│       └── layouts/
│           ├── header.php      Shared navbar and Bootstrap CSS
│           └── footer.php      Shared footer and Bootstrap JS
│
├── users/
│   ├── create.php              Add a new user (admin only)
│   ├── edit.php                Edit an existing user (admin only)
│   └── delete.php              Delete a user (admin only)
│
├── index.php                   Entry point — redirects to login or dashboard
├── login.php                   Login form
├── register.php                Self-registration form
├── logout.php                  Destroys session and redirects to login
├── dashboard.php               Main user management table
└── setup.php                   ⚡ One-time setup script (delete after use!)
```

---

## Installation on XAMPP

### Step 1 — Copy Files

Extract the zip and place the folder inside your XAMPP web root:

```
C:\xampp\htdocs\php-crud-app\
```

Make sure the structure looks like this (not double-nested):

```
✅ C:\xampp\htdocs\php-crud-app\login.php
❌ C:\xampp\htdocs\php-crud-app\php-crud-app\login.php
```

### Step 2 — Start XAMPP

Open the **XAMPP Control Panel** and click **Start** for both:
- ✅ Apache
- ✅ MySQL

### Step 3 — Run the Setup Script

Open your browser and go to:

```
http://localhost/php-crud-app/setup.php
```

This will automatically:
- Create the `crud_app` database
- Create the `users` table
- Insert your admin account with a correctly hashed password

You should see a green **"Setup Complete!"** screen.

> ⚠️ **Delete `setup.php` after it runs!** It's a security risk to leave it accessible.

### Step 4 — Done!

Go to the app:

```
http://localhost/php-crud-app/
```

---

## First Login

After running `setup.php`, log in with:

| Field | Value |
|---|---|
| Email | `admin@admin.com` |
| Password | `Admin@1234` |

---

## Using the App

### Login Page (`/login.php`)
- Enter your email and password
- Invalid credentials show a generic error (intentional — doesn't reveal if email exists)
- Sessions expire after **30 minutes** of inactivity

### Register Page (`/register.php`)
- Anyone can create a new account (role defaults to `user`)
- Password must be at least 8 characters
- Duplicate emails are rejected

### Dashboard (`/dashboard.php`)
The main screen after login. Shows a table of all users.

| Column | Description |
|---|---|
| # | Row number |
| Name | Full name |
| Email | Email address |
| Role | `Admin` (yellow badge) or `User` (grey badge) |
| Registered | Account creation date |
| Actions | Edit / Delete buttons *(admin only)* |

**Admin users** also see:
- **Add User** button (top right)
- **Edit** pencil icon per row
- **Delete** trash icon per row
- Stats cards showing total users and admin count

### Add User (`/users/create.php`) — Admin Only
Fill in name, email, password, and role. Duplicate emails are rejected.

### Edit User (`/users/edit.php`) — Admin Only
Update any user's name, email, role, or password.
Leave the password field **blank** to keep the existing password unchanged.

### Delete User (`/users/delete.php`) — Admin Only
Clicking the trash icon shows a **confirmation prompt** before deleting.
Admins **cannot delete their own account** (safety measure).

### Logout (`/logout.php`)
Completely destroys the session and clears the session cookie.

---

## Security Features

| Feature | Implementation |
|---|---|
| Password hashing | `password_hash()` with BCRYPT, cost 12 |
| SQL Injection prevention | PDO prepared statements on every query |
| XSS prevention | All output escaped with `htmlspecialchars()` |
| CSRF protection | Token generated per session, verified on every POST |
| Session fixation prevention | `session_regenerate_id(true)` on login |
| Session timeout | 30-minute inactivity limit |
| Role-based access | `admin` vs `user` — non-admins get read-only access |
| Secure cookies | `httponly` flag set (JS cannot read session cookie) |
| Error handling | DB errors caught silently — no sensitive info shown to users |

---

## Troubleshooting

### "Invalid email or password"
→ Run `setup.php` again — it regenerates the hash using your local PHP version.

### "Not Found" error
→ Check that your folder is named exactly `php-crud-app` inside `C:\xampp\htdocs\`.

### "Internal Server Error"
→ Delete `public/.htaccess` if it exists, or enable `mod_rewrite` in XAMPP.

### Blank page / no styles
→ Make sure Apache is running in XAMPP Control Panel and you have internet access (Bootstrap loads from CDN).

### Can't see Edit/Delete buttons
→ Your account role is `user`, not `admin`. Fix it in phpMyAdmin:
```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@admin.com';
```
Then log out and back in.

### phpMyAdmin shows empty users table
→ Re-run `http://localhost/php-crud-app/setup.php`.

---

## Changing the Database Password

If your MySQL has a password set, edit `config/database.php`:

```php
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
```

---

## Default Credentials Summary

```
URL:      http://localhost/php-crud-app/
Email:    admin@admin.com
Password: Admin@1234
Role:     admin
```
