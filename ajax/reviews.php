<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isLoggedIn()) {
    $response['message'] = 'Необходимо авторизоваться';
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $productId = $_POST['product_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $comment = trim($_POST['comment'] ?? '');
    
    if (!$productId || !$rating || !$comment) {
        $response['message'] = 'Заполните все поля';
        echo json_encode($response);
        exit;
    }
    
    if ($rating < 1 || $rating > 5) {
        $response['message'] = 'Рейтинг должен быть от 1 до 5';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Проверяем, не оставлял ли пользователь уже отзыв на этот товар
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        
        if ($stmt->fetch()) {
            $response['message'] = 'Вы уже оставляли отзыв на этот товар';
            echo json_encode($response);
            exit;
        }
        
        // Добавляем отзыв (требуется модерация)
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, FALSE)");
        $stmt->execute([$productId, $_SESSION['user_id'], $rating, $comment]);
        
        $response['success'] = true;
        $response['message'] = 'Отзыв отправлен на модерацию';
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка добавления отзыва';
    }
} elseif ($action === 'get') {
    $productId = $_GET['product_id'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, u.full_name, u.nickname, u.avatar 
            FROM reviews r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.product_id = ? AND r.is_approved = TRUE 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$productId]);
        $reviews = $stmt->fetchAll();
        
        $response['success'] = true;
        $response['reviews'] = $reviews;
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка получения отзывов';
    }
}

echo json_encode($response);
