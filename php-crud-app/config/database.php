<?php
// config/database.php
// PDO connection – edit credentials to match your environment

define('DB_HOST', 'localhost');
define('DB_NAME', 'crud_app');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO instance (singleton pattern).
 * Throws a PDOException on failure – caught at the bootstrap level.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw on error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // return arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statements
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
