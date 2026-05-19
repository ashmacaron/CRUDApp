<?php
require_once __DIR__ . '/config/bootstrap.php';

if (isLoggedIn()) {
    redirect('/php-crud-app/dashboard.php');
} else {
    redirect('/php-crud-app/login.php');
}
