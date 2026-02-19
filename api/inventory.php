<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Low-stock or out-of-stock plants
        if (isset($_GET['alerts'])) {
            $stmt = $pdo->query("SELECT * FROM plants WHERE quantity <= low_stock_threshold ORDER BY quantity ASC");
            jsonResponse($stmt->fetchAll());
        }

        if (isset($_GET['out_of_stock'])) {
            $stmt = $pdo->query("SELECT * FROM plants WHERE quantity = 0 ORDER BY name ASC");
            jsonResponse($stmt->fetchAll());
        }

        // Inventory logs
        $sql = "SELECT il.*, p.name as plant_name FROM inventory_log il LEFT JOIN plants p ON il.plant_id = p.id ORDER BY il.created_at DESC";
        $limit = max(1, min(200, intval($_GET['limit'] ?? 50)));
        $page = max(1, intval($_GET['page'] ?? 1));
        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT $limit OFFSET $offset";

        $stmt = $pdo->query($sql);
        jsonResponse([
            'data' => $stmt->fetchAll(),
            'page' => $page,
            'limit' => $limit
        ]);
        break;

    case 'POST':
        $body = getJsonBody();
        $plantId = $body['plant_id'] ?? null;
        $type = $body['type'] ?? 'incoming';
        $qty = intval($body['quantity'] ?? 0);
        $note = $body['note'] ?? '';

        if (!$plantId || $qty <= 0) {
            jsonResponse(['error' => 'plant_id and positive quantity required'], 400);
        }

        $pdo->beginTransaction();
        try {
            // Log entry
            $stmt = $pdo->prepare("INSERT INTO inventory_log (plant_id, type, quantity, note) VALUES (?, ?, ?, ?) RETURNING *");
            $stmt->execute([$plantId, $type, $qty, $note]);
            $log = $stmt->fetch();

            // Update plant stock
            if ($type === 'incoming') {
                $pdo->prepare("UPDATE plants SET quantity = quantity + ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$qty, $plantId]);
            }
            else {
                $pdo->prepare("UPDATE plants SET quantity = GREATEST(quantity - ?, 0), updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$qty, $plantId]);
            }

            $pdo->commit();
            jsonResponse($log, 201);
        }
        catch (Exception $e) {
            safeRollback($pdo);
            jsonResponse(['error' => 'Inventory transaction failed: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}
