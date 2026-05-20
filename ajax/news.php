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

if ($action === 'rate') {
    $data = json_decode(file_get_contents('php://input'), true);
    $newsId = $data['news_id'] ?? 0;
    $rating = $data['rating'] ?? 0;
    
    if (!$newsId || !$rating) {
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
        // Проверяем, не оценивал ли пользователь уже эту новость
        $stmt = $pdo->prepare("SELECT id FROM news_ratings WHERE user_id = ? AND news_id = ?");
        $stmt->execute([$_SESSION['user_id'], $newsId]);
        
        if ($stmt->fetch()) {
            // Обновляем рейтинг
            $stmt = $pdo->prepare("UPDATE news_ratings SET rating = ? WHERE user_id = ? AND news_id = ?");
            $stmt->execute([$rating, $_SESSION['user_id'], $newsId]);
        } else {
            // Добавляем новый рейтинг
            $stmt = $pdo->prepare("INSERT INTO news_ratings (news_id, user_id, rating) VALUES (?, ?, ?)");
            $stmt->execute([$newsId, $_SESSION['user_id'], $rating]);
        }
        
        // Пересчитываем средний рейтинг новости
        $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM news_ratings WHERE news_id = ?");
        $stmt->execute([$newsId]);
        $avgRating = $stmt->fetch()['avg_rating'];
        
        $stmt = $pdo->prepare("UPDATE news SET rating = ? WHERE id = ?");
        $stmt->execute([$avgRating, $newsId]);
        
        $response['success'] = true;
        $response['message'] = 'Спасибо за вашу оценку!';
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка оценки новости';
    }
}

echo json_encode($response);
