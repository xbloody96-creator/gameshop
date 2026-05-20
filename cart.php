<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Получение содержимого корзины
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.title, p.price, p.old_price, p.image, p.slug 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
    
    $total = 0;
    foreach ($cartItems as &$item) {
        $item['total_price'] = $item['price'] * $item['quantity'];
        $total += $item['total_price'];
    }
    
    // Получение избранных новостей
    $stmt = $pdo->prepare("
        SELECT n.* FROM favorites f 
        JOIN news n ON f.news_id = n.id 
        WHERE f.user_id = ? AND f.news_id IS NOT NULL
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $favoriteNews = $stmt->fetchAll();
} catch (PDOException $e) {
    $cartItems = [];
    $total = 0;
    $favoriteNews = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="cart-page">
        <div class="container">
            <h1>Корзина</h1>
            
            <?php if (!empty($cartItems)): ?>
                <div class="cart-layout">
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <img src="images/uploads/<?= escape($item['image']) ?>" alt="<?= escape($item['title']) ?>" class="cart-item-image" onerror="this.src='https://via.placeholder.com/100x100?text=<?= urlencode($item['title']) ?>'">
                                <div class="cart-item-info">
                                    <h3><?= escape($item['title']) ?></h3>
                                    <p class="cart-item-price"><?= formatPrice($item['price']) ?></p>
                                    <p class="cart-item-quantity">Количество: <?= $item['quantity'] ?></p>
                                    <p class="cart-item-total">Итого: <?= formatPrice($item['total_price']) ?></p>
                                </div>
                                <button class="btn btn-outline" onclick="removeFromCart(<?= $item['product_id'] ?>)">Удалить</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <h2>Итого</h2>
                        <div class="summary-row">
                            <span>Товары (<?= count($cartItems) ?>):</span>
                            <span><?= formatPrice($total) ?></span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Общая сумма:</span>
                            <span><?= formatPrice($total) ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary btn-block btn-lg">Оформить заказ</a>
                    </div>
                </div>
                
                <!-- Избранные новости -->
                <?php if (!empty($favoriteNews)): ?>
                    <section class="favorites-news">
                        <h2>Избранные новости</h2>
                        <div class="news-grid">
                            <?php foreach ($favoriteNews as $news): ?>
                                <div class="news-item">
                                    <img src="images/uploads/<?= escape($news['image']) ?>" alt="<?= escape($news['title']) ?>" onerror="this.src='https://via.placeholder.com/300x200?text=News'">
                                    <div class="news-info">
                                        <h3><?= escape($news['title']) ?></h3>
                                        <p><?= escape($news['short_content'] ?? mb_substr($news['content'], 0, 100) . '...') ?></p>
                                        <a href="news-item.php?id=<?= $news['id'] ?>" class="btn btn-outline">Читать</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
                
                <!-- Подбор новостей по интересам -->
                <section class="news-recommendations">
                    <h2>Новости по интересам</h2>
                    <div class="news-grid">
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM news WHERE is_published = TRUE ORDER BY rating DESC LIMIT 4");
                            $recommendedNews = $stmt->fetchAll();
                            foreach ($recommendedNews as $news):
                        ?>
                            <div class="news-item">
                                <img src="images/uploads/<?= escape($news['image']) ?>" alt="<?= escape($news['title']) ?>" onerror="this.src='https://via.placeholder.com/300x200?text=News'">
                                <div class="news-info">
                                    <h3><?= escape($news['title']) ?></h3>
                                    <a href="news-item.php?id=<?= $news['id'] ?>" class="btn btn-outline">Читать</a>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        } catch (PDOException $e) {}
                        ?>
                    </div>
                </section>
            <?php else: ?>
                <div class="empty-cart">
                    <h2>Корзина пуста</h2>
                    <p>Добавьте товары в корзину, чтобы оформить заказ</p>
                    <a href="products.php" class="btn btn-primary">Перейти в каталог</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
