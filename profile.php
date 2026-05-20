<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Получение истории сессий
try {
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE user_id = ? ORDER BY login_time DESC LIMIT 10");
    $stmt->execute([$user['id']]);
    $sessions = $stmt->fetchAll();
    
    // Получение текущих заказов
    $stmt = $pdo->prepare("
        SELECT o.*, GROUP_CONCAT(oi.product_title) as products 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND o.status IN ('pending', 'processing') 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $currentOrders = $stmt->fetchAll();
    
    // Получение истории заказов
    $stmt = $pdo->prepare("
        SELECT o.*, GROUP_CONCAT(oi.product_title) as products 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND o.status IN ('completed', 'cancelled') 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $orderHistory = $stmt->fetchAll();
    
    // Получение избранных товаров
    $stmt = $pdo->prepare("
        SELECT p.* FROM favorites f 
        JOIN products p ON f.product_id = p.id 
        WHERE f.user_id = ? AND f.product_id IS NOT NULL
    ");
    $stmt->execute([$user['id']]);
    $favorites = $stmt->fetchAll();
    
    // Получение истории просмотров
    $stmt = $pdo->prepare("
        SELECT p.*, vh.viewed_at FROM view_history vh 
        JOIN products p ON vh.product_id = p.id 
        WHERE vh.user_id = ? 
        ORDER BY vh.viewed_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $viewHistory = $stmt->fetchAll();
} catch (PDOException $e) {
    $sessions = [];
    $currentOrders = [];
    $orderHistory = [];
    $favorites = [];
    $viewHistory = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="profile-page">
        <div class="container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="images/uploads/<?= escape($user['avatar']) ?>" alt="<?= escape($user['nickname']) ?>" onerror="this.src='https://via.placeholder.com/150?text=<?= urlencode($user['nickname']) ?>'">
                </div>
                <div class="profile-info">
                    <h1><?= escape($user['full_name']) ?></h1>
                    <p class="nickname">@<?= escape($user['nickname']) ?></p>
                    <p class="email"><?= escape($user['email']) ?></p>
                </div>
            </div>
            
            <div class="profile-grid">
                <!-- Текущие заказы -->
                <section class="profile-section">
                    <h2>Текущие заказы</h2>
                    <?php if (!empty($currentOrders)): ?>
                        <div class="orders-list">
                            <?php foreach ($currentOrders as $order): ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <span class="order-number">Заказ #<?= escape($order['order_number']) ?></span>
                                        <span class="order-status status-<?= escape($order['status']) ?>"><?= escape($order['status']) ?></span>
                                    </div>
                                    <div class="order-details">
                                        <p>Товары: <?= escape($order['products']) ?></p>
                                        <p class="order-total">Сумма: <?= formatPrice($order['total_amount']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Нет активных заказов</p>
                    <?php endif; ?>
                </section>
                
                <!-- Избранное -->
                <section class="profile-section">
                    <h2>Избранные товары</h2>
                    <?php if (!empty($favorites)): ?>
                        <div class="products-grid">
                            <?php foreach (array_slice($favorites, 0, 4) as $product): ?>
                                <div class="product-card">
                                    <div class="product-image-wrapper">
                                        <img src="images/uploads/<?= escape($product['image']) ?>" alt="<?= escape($product['title']) ?>" class="product-image" onerror="this.src='https://via.placeholder.com/300x200?text=<?= urlencode($product['title']) ?>'">
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-title"><?= escape($product['title']) ?></h3>
                                        <p class="product-price"><?= formatPrice($product['price']) ?></p>
                                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">Подробнее</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Нет избранных товаров</p>
                    <?php endif; ?>
                </section>
                
                <!-- История заказов -->
                <section class="profile-section">
                    <h2>История заказов</h2>
                    <?php if (!empty($orderHistory)): ?>
                        <div class="orders-list">
                            <?php foreach ($orderHistory as $order): ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <span class="order-number">Заказ #<?= escape($order['order_number']) ?></span>
                                        <span class="order-status status-<?= escape($order['status']) ?>"><?= escape($order['status']) ?></span>
                                    </div>
                                    <div class="order-details">
                                        <p>Дата: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                                        <p>Товары: <?= escape($order['products']) ?></p>
                                        <p class="order-total">Сумма: <?= formatPrice($order['total_amount']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">История заказов пуста</p>
                    <?php endif; ?>
                </section>
                
                <!-- История просмотров -->
                <section class="profile-section">
                    <h2>Недавние просмотры</h2>
                    <?php if (!empty($viewHistory)): ?>
                        <div class="view-history-list">
                            <?php foreach ($viewHistory as $product): ?>
                                <div class="view-item">
                                    <img src="images/uploads/<?= escape($product['image']) ?>" alt="<?= escape($product['title']) ?>" onerror="this.src='https://via.placeholder.com/60x60?text=<?= urlencode($product['title']) ?>'">
                                    <div class="view-info">
                                        <a href="product.php?id=<?= $product['id'] ?>"><?= escape($product['title']) ?></a>
                                        <span class="view-time"><?= timeAgo($product['viewed_at']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Вы еще ничего не смотрели</p>
                    <?php endif; ?>
                </section>
                
                <!-- Сессии -->
                <section class="profile-section">
                    <h2>История входов</h2>
                    <?php if (!empty($sessions)): ?>
                        <table class="sessions-table">
                            <thead>
                                <tr>
                                    <th>Дата входа</th>
                                    <th>IP адрес</th>
                                    <th>Устройство</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessions as $session): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i', strtotime($session['login_time'])) ?></td>
                                        <td><?= escape($session['ip_address']) ?></td>
                                        <td><?= escape(substr($session['user_agent'], 0, 50)) ?>...</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">Нет данных о сессиях</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
