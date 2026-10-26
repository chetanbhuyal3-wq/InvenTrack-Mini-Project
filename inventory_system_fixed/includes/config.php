<?php
// ============================================================
// includes/config.php
// Database connection & global configuration
// ============================================================

// ---- Database credentials (edit these for your XAMPP setup) ----
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Default XAMPP MySQL user
define('DB_PASS', '');           // Default XAMPP MySQL password (empty)
define('DB_NAME', 'inventory_db');

// ---- App settings ----
define('APP_NAME', 'InvenTrack');
define('LOW_STOCK_DEFAULT', 10);

// ---- Session setup ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// Create PDO database connection (uses prepared statements
// to prevent SQL injection)
// ============================================================
function getDB(): PDO {
    static $pdo = null;          // Reuse connection within a request

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // Real prepared statements
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose raw DB errors to the browser in production
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed. Check config.php']);
            exit;
        }
    }
    return $pdo;
}

// ============================================================
// Helper: send a JSON response and stop execution
// ============================================================
function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ============================================================
// Helper: require a logged-in session (redirect otherwise)
// ============================================================
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /inventory_system/index.php');
        exit;
    }
}

// ============================================================
// Helper: sanitise a string for HTML output (XSS prevention)
// ============================================================
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
