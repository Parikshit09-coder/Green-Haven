<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // Single plant or list
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM plants WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $plant = $stmt->fetch();
                if ($plant)
                    jsonResponse($plant);
                else
                    jsonResponse(['error' => 'Plant not found'], 404);
            }

            // Filters
            $where = [];
            $params = [];

            if (!empty($_GET['search'])) {
                $where[] = "name ILIKE ?";
                $params[] = '%' . $_GET['search'] . '%';
            }
            if (!empty($_GET['category'])) {
                $where[] = "category = ?";
                $params[] = $_GET['category'];
            }

            $sql = "SELECT * FROM plants";
            if ($where)
                $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY created_at DESC";

            // Pagination
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $sql .= " LIMIT $limit OFFSET $offset";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $plants = $stmt->fetchAll();

            // Total count
            $countSql = "SELECT COUNT(*) FROM plants";
            if ($where)
                $countSql .= " WHERE " . implode(' AND ', $where);
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();

            jsonResponse([
                'data' => $plants,
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit
            ]);
        }
        catch (Exception $e) {
            jsonResponse(['error' => 'Failed to fetch plants: ' . $e->getMessage()], 500);
        }
        break;

    case 'POST':
        try {
            $body = getJsonBody();
            $stmt = $pdo->prepare("INSERT INTO plants (name, category, price, quantity, low_stock_threshold, image_url, sunlight, watering_schedule, fertilizer_schedule) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING *");
            $stmt->execute([
                $body['name'] ?? '',
                $body['category'] ?? 'indoor',
                $body['price'] ?? 0,
                $body['quantity'] ?? 0,
                $body['low_stock_threshold'] ?? 5,
                $body['image_url'] ?? '',
                $body['sunlight'] ?? '',
                $body['watering_schedule'] ?? '',
                $body['fertilizer_schedule'] ?? ''
            ]);
            $plant = $stmt->fetch();
            jsonResponse($plant, 201);
        }
        catch (Exception $e) {
            jsonResponse(['error' => 'Failed to create plant: ' . $e->getMessage()], 500);
        }
        break;

    case 'PUT':
        try {
            $body = getJsonBody();
            if (empty($body['id']))
                jsonResponse(['error' => 'ID required'], 400);

            $stmt = $pdo->prepare("UPDATE plants SET name=?, category=?, price=?, quantity=?, low_stock_threshold=?, image_url=?, sunlight=?, watering_schedule=?, fertilizer_schedule=?, updated_at=CURRENT_TIMESTAMP WHERE id=? RETURNING *");
            $stmt->execute([
                $body['name'] ?? '',
                $body['category'] ?? 'indoor',
                $body['price'] ?? 0,
                $body['quantity'] ?? 0,
                $body['low_stock_threshold'] ?? 5,
                $body['image_url'] ?? '',
                $body['sunlight'] ?? '',
                $body['watering_schedule'] ?? '',
                $body['fertilizer_schedule'] ?? '',
                $body['id']
            ]);
            $plant = $stmt->fetch();
            if ($plant)
                jsonResponse($plant);
            else
                jsonResponse(['error' => 'Plant not found'], 404);
        }
        catch (Exception $e) {
            jsonResponse(['error' => 'Failed to update plant: ' . $e->getMessage()], 500);
        }
        break;

    case 'DELETE':
        try {
            $id = $_GET['id'] ?? null;
            if (!$id)
                jsonResponse(['error' => 'ID required'], 400);

            $stmt = $pdo->prepare("DELETE FROM plants WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['message' => 'Plant deleted']);
        }
        catch (Exception $e) {
            jsonResponse(['error' => 'Failed to delete plant: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
