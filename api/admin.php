<?php
session_start();
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'POST' && $action === 'login') {
        $body = getJsonBody();
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && $admin['password_hash'] === $password) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            jsonResponse([
                'message' => 'Login successful',
                'username' => $admin['username'],
                'login_time' => $_SESSION['login_time']
            ]);
        } else {
            jsonResponse(['error' => 'Invalid username or password'], 401);
        }

    } elseif ($method === 'POST' && $action === 'logout') {
        session_destroy();
        jsonResponse(['message' => 'Logged out']);

    } elseif ($method === 'GET' && $action === 'status') {
        if (isset($_SESSION['admin_id'])) {
            jsonResponse([
                'logged_in' => true,
                'username' => $_SESSION['admin_user'],
                'login_time' => $_SESSION['login_time'] ?? ''
            ]);
        } else {
            jsonResponse(['logged_in' => false]);
        }

    } elseif ($method === 'GET' && $action === 'dashboard') {
        $totalPlants = $pdo->query("SELECT COUNT(*) FROM plants")->fetchColumn();
        $lowStock = $pdo->query("SELECT COUNT(*) FROM plants WHERE quantity <= low_stock_threshold AND quantity > 0")->fetchColumn();
        $outOfStock = $pdo->query("SELECT COUNT(*) FROM plants WHERE quantity = 0")->fetchColumn();
        $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
        $totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        $revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
        $todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURRENT_DATE")->fetchColumn();

        $recentOrders = $pdo->query("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();

        jsonResponse([
            'total_plants' => (int)$totalPlants,
            'low_stock' => (int)$lowStock,
            'out_of_stock' => (int)$outOfStock,
            'total_orders' => (int)$totalOrders,
            'pending_orders' => (int)$pendingOrders,
            'total_customers' => (int)$totalCustomers,
            'revenue' => (float)$revenue,
            'today_orders' => (int)$todayOrders,
            'recent_orders' => $recentOrders
        ]);

    } else {
        jsonResponse(['error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
}
