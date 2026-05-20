<?php
require_once 'config.php';

// Получение популярных товаров для слайдера
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_popular = TRUE AND is_available = TRUE ORDER BY rating DESC LIMIT 5");
    $stmt->execute();
    $popularProducts = $stmt->fetchAll();
    
    // Получение новостей
    $stmt = $pdo->prepare("SELECT * FROM news WHERE is_published = TRUE ORDER BY published_at DESC LIMIT 6");
    $stmt->execute();
    $news = $stmt->fetchAll();
    
    // Получение акций
    $stmt = $pdo->prepare("SELECT * FROM promotions WHERE is_active = TRUE AND end_date > NOW() ORDER BY start_date DESC");
    $stmt->execute();
    $promotions = $stmt->fetchAll();
    
    // Получение категорий
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $popularProducts = [];
    $news = [];
    $promotions = [];
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Магазин цифровых ключей</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Section with Slider -->
        <section class="hero">
            <div class="container">
                <div class="slider">
                    <?php if (!empty($popularProducts)): ?>
                        <?php foreach ($popularProducts as $index => $product): ?>
                            <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                                <div class="slide-overlay"></div>
                                <img src="images/uploads/<?= escape($product['image']) ?>" alt="<?= escape($product['title']) ?>" class="slide-image" onerror="this.src='https://via.placeholder.com/1200x500?text=<?= urlencode($product['title']) ?>'">
                                <div class="slide-content">
                                    <h2 class="slide-title"><?= escape($product['title']) ?></h2>
                                    <p class="slide-description"><?= escape($product['short_description'] ?? mb_substr($product['description'], 0, 150) . '...') ?></p>
                                    <div class="slide-price"><?= formatPrice($product['price']) ?></div>
                                    <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary">Подробнее</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="slider-controls">
                            <button id="slider-prev" class="slider-btn">◀</button>
                            <button id="slider-next" class="slider-btn">▶</button>
                        </div>
                        
                        <div class="slider-indicators">
                            <?php foreach ($popularProducts as $index => $product): ?>
                                <span class="indicator <?= $index === 0 ? 'active' : '' ?>"></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="slide active">
                            <div class="slide-overlay"></div>
                            <img src="https://via.placeholder.com/1200x500?text=GamesKey+Store" alt="Welcome" class="slide-image">
                            <div class="slide-content">
                                <h2 class="slide-title">Добро пожаловать в GamesKey</h2>
                                <p class="slide-description">Лучшие игры по лучшим ценам! Мгновенная доставка ключей.</p>
                                <a href="products.php" class="btn btn-primary">Смотреть каталог</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- Search Section -->
        <section id="search" class="search-section">
            <div class="container">
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="search-input" class="search-input" placeholder="Поиск игр, новостей..." autocomplete="off">
                    <button class="btn btn-primary search-btn">Поиск</button>
                    <div id="search-suggestions" class="search-suggestions"></div>
                </div>
            </div>
        </section>
        
        <!-- Products Section -->
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Популярные товары</h2>
                    <p class="section-subtitle">Лучшие игры по версии наших покупателей</p>
                </div>
                
                <div class="products-grid">
                    <?php foreach (array_slice($popularProducts, 0, 4) as $product): ?>
                        <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                            <?php 
                            $discount = calculateDiscount($product['price'], $product['old_price']);
                            if ($discount > 0): ?>
                                <span class="product-badge discount">-<?= $discount ?>%</span>
                            <?php endif; ?>
                            
                            <div class="product-image-wrapper">
                                <img src="images/uploads/<?= escape($product['image']) ?>" alt="<?= escape($product['title']) ?>" class="product-image" onerror="this.src='https://via.placeholder.com/300x200?text=<?= urlencode($product['title']) ?>'">
                                <div class="product-actions">
                                    <?php if (isLoggedIn()): ?>
                                        <button class="product-action-btn" data-favorite="<?= $product['id'] ?>" onclick="event.stopPropagation(); toggleFavorite(<?= $product['id'] ?>)">🤍</button>
                                    <?php endif; ?>
                                    <button class="product-action-btn" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">🛒</button>
                                </div>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-category">Игры</div>
                                <h3 class="product-title"><?= escape($product['title']) ?></h3>
                                <p class="product-description"><?= escape($product['short_description'] ?? mb_substr($product['description'], 0, 80) . '...') ?></p>
                                
                                <div class="product-rating">
                                    <span class="stars"><?= str_repeat('★', round($product['rating'])) ?><?= str_repeat('☆', 5 - round($product['rating'])) ?></span>
                                    <span class="rating-value"><?= number_format($product['rating'], 1) ?></span>
                                </div>
                                
                                <div class="product-footer">
                                    <div class="product-price">
                                        <span class="price-current"><?= formatPrice($product['price']) ?></span>
                                        <?php if ($product['old_price']): ?>
                                            <span class="price-old"><?= formatPrice($product['old_price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-primary" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">В корзину</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 3rem;">
                    <a href="products.php" class="btn btn-outline">Смотреть все товары</a>
                </div>
            </div>
        </section>
        
        <!-- About Section -->
        <section id="about" class="about-section">
            <div class="container">
                <div class="about-content">
                    <div class="about-text">
                        <h2>О нас</h2>
                        <p>GamesKey - это современный магазин цифровых ключей для игр. Мы работаем на рынке с 2020 года и за это время заслужили доверие тысяч покупателей.</p>
                        <p>Наша миссия - предоставить геймерам быстрый и надежный доступ к любимым играм по лучшим ценам.</p>
                        
                        <div class="features-grid">
                            <div class="feature-item">
                                <div class="feature-icon">⚡</div>
                                <div class="feature-text">
                                    <h4>Мгновенная доставка</h4>
                                    <p>Ключи приходят сразу после оплаты</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">🔒</div>
                                <div class="feature-text">
                                    <h4>Гарантия качества</h4>
                                    <p>Только официальные ключи</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">💳</div>
                                <div class="feature-text">
                                    <h4>Безопасная оплата</h4>
                                    <p>Защищенные платежные системы</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">🎧</div>
                                <div class="feature-text">
                                    <h4>Поддержка 24/7</h4>
                                    <p>Всегда на связи</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img src="https://via.placeholder.com/600x400?text=About+Us" alt="О нас" class="about-image">
                </div>
            </div>
        </section>
        
        <!-- Promotions Section -->
        <section id="promotions" class="section" style="background: var(--bg-secondary);">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Акции</h2>
                    <p class="section-subtitle">Не пропустите выгодные предложения</p>
                </div>
                
                <?php if (!empty($promotions)): ?>
                    <div class="products-grid">
                        <?php foreach ($promotions as $promotion): ?>
                            <div class="product-card" style="background: var(--gradient-primary); color: white;">
                                <div class="product-info" style="padding: 2rem;">
                                    <h3 class="product-title" style="color: white;"><?= escape($promotion['title']) ?></h3>
                                    <p style="margin: 1rem 0;"><?= escape($promotion['description']) ?></p>
                                    <div style="font-size: 3rem; font-weight: bold;">-<?= $promotion['discount_percent'] ?>%</div>
                                    <p>до <?= date('d.m.Y', strtotime($promotion['end_date'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-secondary);">В данный момент акций нет</p>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- News Section -->
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Новости</h2>
                    <p class="section-subtitle">Последние события из мира игр</p>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($news as $item): ?>
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <img src="images/uploads/<?= escape($item['image']) ?>" alt="<?= escape($item['title']) ?>" class="product-image" onerror="this.src='https://via.placeholder.com/300x200?text=News'">
                            </div>
                            <div class="product-info">
                                <div class="product-category">Новость</div>
                                <h3 class="product-title"><?= escape($item['title']) ?></h3>
                                <p class="product-description"><?= escape($item['short_content'] ?? mb_substr($item['content'], 0, 100) . '...') ?></p>
                                <div class="product-rating">
                                    <span class="stars"><?= str_repeat('★', round($item['rating'])) ?><?= str_repeat('☆', 5 - round($item['rating'])) ?></span>
                                    <span class="rating-value"><?= number_format($item['rating'], 1) ?></span>
                                </div>
                                <a href="news-item.php?id=<?= $item['id'] ?>" class="btn btn-outline btn-block">Читать далее</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
