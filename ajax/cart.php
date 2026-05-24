<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'count' => 0];

// Получаем action из GET или POST
$action = $_GET['action'] ?? $_POST['action'] ?? '';

error_log('Cart action: ' . $action . ', Session user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));

// Для add/remove/count нужна авторизация
if (in_array($action, ['add', 'remove', 'clear', 'count', ''])) {
    if (!isLoggedIn()) {
        error_log('Not logged in');
        echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
        exit;
    }
}

if ($action === 'count') {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        $response['success'] = true;
        $response['count'] = $result['count'];
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка базы данных';
    }
} elseif ($action === 'add') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Неверный формат данных: ' . json_last_error_msg();
        error_log('JSON Error: ' . json_last_error_msg() . ', Input: ' . $input);
        echo json_encode($response);
        exit;
    }
    
    $productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
    
    if (!$productId) {
        $response['message'] = 'Неверный ID товара. Получено: ' . print_r($data, true);
        error_log('Add to cart - Invalid product ID. Data: ' . print_r($data, true));
        echo json_encode($response);
        exit;
    }
    
    error_log('Add to cart - User ID: ' . $_SESSION['user_id'] . ', Product ID: ' . $productId);
    
    try {
        // Проверка наличия товара
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_available = TRUE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $response['message'] = 'Товар недоступен';
            error_log('Product not found or not available: ' . $productId);
            echo json_encode($response);
            exit;
        }
        
        // Добавление в корзину или увеличение количества
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1) 
                               ON DUPLICATE KEY UPDATE quantity = quantity + 1");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        
        error_log('Successfully added to cart');
        $response['success'] = true;
        $response['message'] = 'Товар добавлен в корзину';
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка добавления в корзину: ' . $e->getMessage();
        error_log('Cart add error: ' . $e->getMessage());
    }
} elseif ($action === 'remove') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? 0;
    
    if (!$productId) {
        $response['message'] = 'Неверный ID товара';
        echo json_encode($response);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        
        $response['success'] = true;
        $response['message'] = 'Товар удален из корзины';
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка удаления из корзины';
    }
} elseif ($action === 'clear') {
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $response['success'] = true;
        $response['message'] = 'Корзина очищена';
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка очистки корзины';
    }
} else {
    // Получение содержимого корзины
    try {
        // Получение содержимого корзины
        $stmt = $pdo->prepare("
            SELECT c.*, p.title, p.price, p.old_price, p.image, p.slug 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $items = $stmt->fetchAll();
        
        $total = 0;
        foreach ($items as &$item) {
            $item['total_price'] = $item['price'] * $item['quantity'];
            $total += $item['total_price'];
        }
        
        $response['success'] = true;
        $response['items'] = $items;
        $response['total'] = $total;
    } catch (PDOException $e) {
        $response['message'] = 'Ошибка получения корзины: ' . $e->getMessage();
    }
}

echo json_encode($response);
