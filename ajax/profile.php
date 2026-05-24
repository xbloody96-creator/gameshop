<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'items' => [], 'total' => 0];

if (!isLoggedIn()) {
    echo json_encode($response);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'view_history') {
    $limit = (int)($_GET['limit'] ?? 3);
    
    try {
        // Получаем общее количество
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM view_history WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $total = $stmt->fetch()['total'];
        
        // Получаем историю просмотров
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
        $response['total'] = $total;
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка получения истории просмотров';
    }
} elseif ($action === 'sessions') {
    $limit = (int)($_GET['limit'] ?? 5);
    
    try {
        // Получаем общее количество
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sessions WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $total = $stmt->fetch()['total'];
        
        // Получаем сессии
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE user_id = ? ORDER BY login_time DESC LIMIT ?");
        $stmt->execute([$_SESSION['user_id'], $limit]);
        $items = $stmt->fetchAll();
        
        $response['success'] = true;
        $response['items'] = $items;
        $response['total'] = $total;
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка получения истории входов';
    }
} elseif ($action === 'clear_sessions') {
    try {
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $response['success'] = true;
        $response['message'] = 'История входов очищена';
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка очистки истории';
    }
}

echo json_encode($response);
