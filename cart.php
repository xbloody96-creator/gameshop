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
    <style>
        /* Редизайн корзины */
        .cart-page {
            padding: 40px 0;
            min-height: 60vh;
        }
        
        .cart-page h1 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-align: center;
            color: var(--text-primary);
        }
        
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 900px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            
            .cart-summary {
                position: sticky;
                top: 20px;
            }
        }
        
        .cart-items {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            padding: 20px;
            background: var(--bg-primary);
            border-radius: 12px;
            margin-bottom: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            align-items: center;
        }
        
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0,0,0,0.12);
        }
        
        .cart-item:last-child {
            margin-bottom: 0;
        }
        
        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-item-info h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        
        .cart-item-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 8px;
        }
        
        .cart-item-quantity {
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .cart-item-total {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .cart-summary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 16px;
            padding: 30px;
            color: white;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .cart-summary h2 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            font-size: 1rem;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-total {
            font-size: 1.4rem;
            font-weight: 700;
            margin-top: 15px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }
        
        .cart-summary .btn-primary {
            background: white;
            color: var(--primary-color);
            margin-top: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 16px 20px;
            transition: all 0.3s ease;
        }
        
        .cart-summary .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255,255,255,0.3);
        }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: var(--bg-secondary);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .empty-cart h2 {
            font-size: 2rem;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .empty-cart p {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .favorites-news, .news-recommendations {
            margin-top: 50px;
        }
        
        .favorites-news h2, .news-recommendations h2 {
            font-size: 2rem;
            margin-bottom: 25px;
            color: var(--text-primary);
        }
        
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .news-item {
            background: var(--bg-secondary);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .news-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.15);
        }
        
        .news-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        
        .news-info {
            padding: 20px;
        }
        
        .news-info h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        
        .news-info p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }
    </style>
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
                                <button class="btn btn-danger" onclick="event.stopPropagation(); removeFromCart(<?= $item['product_id'] ?>)">🗑️ Удалить</button>
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
                            $stmt = $pdo->query("SELECT * FROM news WHERE is_active = TRUE ORDER BY rating DESC LIMIT 4");
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
