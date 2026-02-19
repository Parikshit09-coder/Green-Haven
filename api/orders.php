<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Single order with items
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT o.*, c.name as customer_name, c.phone as customer_phone FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
            $stmt->execute([$_GET['id']]);
            $order = $stmt->fetch();
            if (!$order)
                jsonResponse(['error' => 'Order not found'], 404);

            $itemStmt = $pdo->prepare("SELECT oi.*, p.name as plant_name FROM order_items oi LEFT JOIN plants p ON oi.plant_id = p.id WHERE oi.order_id = ?");
            $itemStmt->execute([$_GET['id']]);
            $order['items'] = $itemStmt->fetchAll();
            jsonResponse($order);
        }

        // Filter by status
        $where = [];
        $params = [];
        if (!empty($_GET['status'])) {
            $where[] = "o.status = ?";
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['customer_id'])) {
            $where[] = "o.customer_id = ?";
            $params[] = $_GET['customer_id'];
        }

        $sql = "SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id";
        if ($where)
            $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY o.created_at DESC";

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Total count
        $countSql = "SELECT COUNT(*) FROM orders o";
        if ($where)
            $countSql .= " WHERE " . implode(' AND ', $where);
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);

        jsonResponse([
            'data' => $stmt->fetchAll(),
            'total' => (int)$countStmt->fetchColumn(),
            'page' => $page,
            'limit' => $limit
        ]);
        break;

    case 'POST':
        $body = getJsonBody();
        $customerId = $body['customer_id'] ?? null;
        $paymentMode = $body['payment_mode'] ?? 'cash';
        $items = $body['items'] ?? [];

        if (empty($items))
            jsonResponse(['error' => 'Order must have at least one item'], 400);

        $pdo->beginTransaction();
        try {
            // Calculate total
            $total = 0;
            foreach ($items as $item) {
                // Ensure price and quantity are numeric
                $price = floatval($item['unit_price'] ?? 0);
                $qty = intval($item['quantity'] ?? 1);
                $total += $price * $qty;
            }

            // Create order - ensure payment_mode is treated as string
            $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_amount, status, payment_mode) VALUES (?, ?, 'pending', ?) RETURNING *");
            $stmt->execute([$customerId, $total, strval($paymentMode)]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new Exception("Failed to create order record");
            }

            // Create order items & deduct stock
            foreach ($items as $item) {
                $plantId = intval($item['plant_id'] ?? 0);
                $itemQty = intval($item['quantity'] ?? 1);
                $itemPrice = floatval($item['unit_price'] ?? 0);

                if ($plantId <= 0)
                    continue;

                $pdo->prepare("INSERT INTO order_items (order_id, plant_id, quantity, unit_price) VALUES (?, ?, ?, ?)")
                    ->execute([$order['id'], $plantId, $itemQty, $itemPrice]);

                // Deduct stock
                $pdo->prepare("UPDATE plants SET quantity = GREATEST(quantity - ?, 0), updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                    ->execute([$itemQty, $plantId]);

                // Log inventory
                $pdo->prepare("INSERT INTO inventory_log (plant_id, type, quantity, note) VALUES (?, 'sold', ?, ?)")
                    ->execute([$plantId, $itemQty, 'Order #' . $order['id']]);
            }

            $pdo->commit();
            $order['items'] = $items;
            jsonResponse($order, 201);
        }
        catch (Exception $e) {
            safeRollback($pdo);
            jsonResponse(['error' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'PUT':
        $body = getJsonBody();
        if (empty($body['id']))
            jsonResponse(['error' => 'Order ID required'], 400);

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? RETURNING *");
        $stmt->execute([$body['status'] ?? 'pending', $body['id']]);
        $order = $stmt->fetch();
        if ($order)
            jsonResponse($order);
        else
            jsonResponse(['error' => 'Order not found'], 404);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
