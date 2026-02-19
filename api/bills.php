<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $orderId = $_GET['order_id'] ?? null;
    if (!$orderId)
        jsonResponse(['error' => 'order_id required'], 400);

    // Order details
    $stmt = $pdo->prepare("SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order)
        jsonResponse(['error' => 'Order not found'], 404);

    // Order items
    $itemStmt = $pdo->prepare("SELECT oi.*, p.name as plant_name, p.category as plant_category FROM order_items oi LEFT JOIN plants p ON oi.plant_id = p.id WHERE oi.order_id = ?");
    $itemStmt->execute([$orderId]);
    $order['items'] = $itemStmt->fetchAll();

    // Invoice metadata
    $order['invoice_number'] = 'INV-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
    $order['invoice_date'] = $order['created_at'];
    $order['business_name'] = 'Green Haven Nursery';
    $order['business_address'] = 'Plant Nursery Management System';

    jsonResponse($order);
}
else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
