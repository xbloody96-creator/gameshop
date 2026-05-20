<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

// Получаем заказ
$stmt = $pdo->prepare("SELECT o.*, u.login, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}

// Получаем товары заказа
$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name FROM order_items oi 
                       LEFT JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

echo json_encode([
    'id' => $order['id'],
    'login' => $order['login'],
    'email' => $order['email'],
    'phone' => $order['phone'],
    'delivery_address' => $order['delivery_address'],
    'payment_method' => $order['payment_method'],
    'status' => $order['status'],
    'total' => number_format($order['total'], 2),
    'created_at' => date('d.m.Y H:i', strtotime($order['created_at'])),
    'items' => $items
]);
