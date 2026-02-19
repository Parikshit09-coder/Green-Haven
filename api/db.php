<?php
// ============================================
// Database Connection — Neon PostgreSQL
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#')
            continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Build DSN from .env variables
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbPort = $_ENV['DB_PORT'] ?? '5432';
$dbName = $_ENV['DB_NAME'] ?? 'neondb';
$dbUser = $_ENV['DB_USER'] ?? '';
$dbPass = $_ENV['DB_PASSWORD'] ?? '';
$dbSSL = $_ENV['DB_SSLMODE'] ?? 'require';

$dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName};sslmode={$dbSSL}";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); // Required for Neon/PgBouncer

    // CRITICAL: Clean up any stale transaction state from Neon's connection pooler.
    // PgBouncer may reuse a backend connection that was left in an aborted transaction.
    // This ROLLBACK clears that state so our queries don't fail with
    // "current transaction is aborted, commands ignored until end of transaction block"
    try {
        $pdo->exec("ROLLBACK");
    }
    catch (PDOException $e) {
    // Ignore — no stale transaction existed, which is the normal case
    }
}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Helper: read JSON body
function getJsonBody()
{
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// Helper: send JSON response
function jsonResponse($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Helper: Safe rollback to avoid "transaction is aborted" errors lingering
function safeRollback($pdo)
{
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
