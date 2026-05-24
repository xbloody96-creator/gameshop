<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'items' => []];

if (!isLoggedIn()) {
    echo json_encode($response);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'view_history') {
    $limit = (int)($_GET['limit'] ?? 3);
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id, p.title, p.image, p.slug, vh.viewed_at 
            FROM view_history vh 
            JOIN products p ON vh.product_id = p.id 
            WHERE vh.user_id = ? 
            ORDER BY vh.viewed_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$_SESSION['user_id'], $limit]);
        $items = $stmt->fetchAll();
        
        // Форматируем время для каждого элемента
        foreach ($items as &$item) {
            $item['viewed_ago'] = timeAgo($item['viewed_at']);
        }
        
        $response['success'] = true;
        $response['items'] = $items;
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка получения истории просмотров';
    }
}

echo json_encode($response);
