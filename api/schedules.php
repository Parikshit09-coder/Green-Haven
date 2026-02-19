<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT id, name, category, sunlight, watering_schedule, fertilizer_schedule, image_url FROM plants ORDER BY name ASC";
    $stmt = $pdo->query($sql);
    jsonResponse(['data' => $stmt->fetchAll()]);
}
else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
