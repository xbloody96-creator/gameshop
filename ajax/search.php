<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'results' => []];

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode($response);
    exit;
}

try {
    // Поиск товаров
    $stmt = $pdo->prepare("
        SELECT id, title, price, image, 'product' as type 
        FROM products 
        WHERE title LIKE ? AND is_available = TRUE 
        LIMIT 5
    ");
    $stmt->execute(['%' . $query . '%']);
    $products = $stmt->fetchAll();
    
    // Поиск новостей
    $stmt = $pdo->prepare("
        SELECT id, title, 0 as price, image, 'news' as type 
        FROM news 
        WHERE title LIKE ? AND is_published = TRUE 
        LIMIT 3
    ");
    $stmt->execute(['%' . $query . '%']);
    $news = $stmt->fetchAll();
    
    $results = array_merge($products, $news);
    
    // Сортировка по релевантности (товары сначала)
    usort($results, function($a, $b) {
        if ($a['type'] === 'product' && $b['type'] !== 'product') {
            return -1;
        }
        return 0;
    });
    
    $response['success'] = true;
    $response['results'] = $results;
} catch (PDOException $e) {
    $response['message'] = 'Ошибка поиска';
}

echo json_encode($response);
