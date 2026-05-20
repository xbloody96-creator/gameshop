<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'is_favorite' => false];

if (!isLoggedIn()) {
    $response['message'] = 'Необходимо авторизоваться';
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$type = $_POST['type'] ?? 'product';
$id = $_POST['id'] ?? 0;

if (!$id) {
    $response['message'] = 'Неверный ID';
    echo json_encode($response);
    exit;
}

try {
    if ($action === 'toggle') {
        // Проверяем, есть ли уже в избранном
        $field = $type === 'news' ? 'news_id' : 'product_id';
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND $field = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Удаляем из избранного
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
            $stmt->execute([$existing['id']]);
            $response['success'] = true;
            $response['message'] = 'Удалено из избранного';
            $response['is_favorite'] = false;
        } else {
            // Добавляем в избранное
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, product_id, news_id) VALUES (?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'], 
                $type === 'product' ? $id : null, 
                $type === 'news' ? $id : null
            ]);
            $response['success'] = true;
            $response['message'] = 'Добавлено в избранное';
            $response['is_favorite'] = true;
        }
    } elseif ($action === 'check') {
        $field = $type === 'news' ? 'news_id' : 'product_id';
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND $field = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $response['is_favorite'] = (bool)$stmt->fetch();
        $response['success'] = true;
    }
} catch (PDOException $e) {
    $response['message'] = 'Ошибка базы данных';
}

echo json_encode($response);
