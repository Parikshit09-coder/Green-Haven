<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Single customer with purchase history
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $customer = $stmt->fetch();
            if (!$customer)
                jsonResponse(['error' => 'Customer not found'], 404);

            // Purchase history
            $ordersStmt = $pdo->prepare("SELECT o.*, (SELECT json_agg(json_build_object('plant_name', p.name, 'quantity', oi.quantity, 'unit_price', oi.unit_price)) FROM order_items oi LEFT JOIN plants p ON oi.plant_id = p.id WHERE oi.order_id = o.id) as items FROM orders o WHERE o.customer_id = ? ORDER BY o.created_at DESC");
            $ordersStmt->execute([$_GET['id']]);
            $customer['orders'] = $ordersStmt->fetchAll();
            jsonResponse($customer);
        }

        // List
        $search = $_GET['search'] ?? '';
        $sql = "SELECT * FROM customers";
        $params = [];
        if ($search) {
            $sql .= " WHERE name ILIKE ? OR phone ILIKE ? OR email ILIKE ?";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        jsonResponse(['data' => $stmt->fetchAll()]);
        break;

    case 'POST':
        $body = getJsonBody();
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?) RETURNING *");
        $stmt->execute([
            $body['name'] ?? '',
            $body['email'] ?? '',
            $body['phone'] ?? '',
            $body['address'] ?? ''
        ]);
        jsonResponse($stmt->fetch(), 201);
        break;

    case 'PUT':
        $body = getJsonBody();
        if (empty($body['id']))
            jsonResponse(['error' => 'ID required'], 400);

        $stmt = $pdo->prepare("UPDATE customers SET name=?, email=?, phone=?, address=? WHERE id=? RETURNING *");
        $stmt->execute([
            $body['name'] ?? '',
            $body['email'] ?? '',
            $body['phone'] ?? '',
            $body['address'] ?? '',
            $body['id']
        ]);
        $customer = $stmt->fetch();
        if ($customer)
            jsonResponse($customer);
        else
            jsonResponse(['error' => 'Customer not found'], 404);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id)
            jsonResponse(['error' => 'ID required'], 400);
        $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
        jsonResponse(['message' => 'Customer deleted']);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
