<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = intval($_GET['id'] ?? 0);
if (!$user_id) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

// Получаем историю сессий пользователя
$stmt = $pdo->prepare("SELECT * FROM user_sessions WHERE user_id = ? ORDER BY login_time DESC LIMIT 10");
$stmt->execute([$user_id]);
$sessions = $stmt->fetchAll();

// Получаем заказы пользователя
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Получаем избранные новости
$stmt = $pdo->prepare("SELECT n.* FROM favorites f JOIN news n ON f.news_id = n.id WHERE f.user_id = ?");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();

echo json_encode([
    'id' => $user['id'],
    'login' => $user['login'],
    'email' => $user['email'],
    'fullname' => $user['fullname'],
    'nickname' => $user['nickname'],
    'gender' => $user['gender'],
    'role' => $user['role'],
    'is_active' => $user['is_active'],
    'created_at' => date('d.m.Y H:i', strtotime($user['created_at'])),
    'last_login' => $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Никогда',
    'sessions_count' => count($sessions),
    'orders_count' => count($orders),
    'favorites_count' => count($favorites)
]);
