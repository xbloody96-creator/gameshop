<?php
require_once 'config.php';

// Получение популярных товаров для слайдера
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_popular = TRUE AND is_available = TRUE ORDER BY rating DESC LIMIT 5");
    $stmt->execute();
    $popularProducts = $stmt->fetchAll();
    
    // Получение новостей
    $stmt = $pdo->prepare("SELECT * FROM news WHERE is_active = TRUE ORDER BY published_at DESC LIMIT 6");
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
        <!-- Hero Section with Modern Design -->
        <section class="hero-modern">
            <div class="hero-bg-overlay"></div>
            <div class="container hero-container">
                <div class="hero-content">
                    <span class="hero-badge">🔥 Лучшие цены <?= date('Y') ?></span>
                    <h1 class="hero-title">Игровые ключи<br><span class="gradient-text">со скидкой до 70%</span></h1>
                    <p class="hero-description">Мгновенная доставка, гарантия качества и поддержка 24/7. Покупайте любимые игры по лучшим ценам!</p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary btn-lg"><svg class="btn-icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> В каталог</a>
                        <a href="#about" class="btn btn-outline btn-lg">ℹ️ О нас</a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">10K+</span>
                            <span class="stat-label">Довольных клиентов</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">500+</span>
                            <span class="stat-label">Игр в каталоге</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">24/7</span>
                            <span class="stat-label">Поддержка</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="floating-card card-1">
                        <img src="https://via.placeholder.com/150x200?text=Game+1" alt="Game">
                        <span class="float-badge">-50%</span>
                    </div>
                    <div class="floating-card card-2">
                        <img src="https://via.placeholder.com/150x200?text=Game+2" alt="Game">
                        <span class="float-badge">NEW</span>
                    </div>
                    <div class="floating-card card-3">
                        <img src="https://via.placeholder.com/150x200?text=Game+3" alt="Game">
                        <span class="float-badge">HOT</span>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Search Section -->
        <section id="search" class="search-section-modern">
            <div class="container">
                <div class="search-bar-modern">
                    <span class="search-icon"><svg class="icon-svg-inline" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
                    <input type="text" id="search-input" class="search-input-modern" placeholder="Поиск игр, новостей..." autocomplete="off">
                    <button class="btn btn-primary search-btn-modern">Найти</button>
                    <div id="search-suggestions" class="search-suggestions"></div>
                </div>
            </div>
        </section>
        
        <!-- Categories Quick Access -->
        <section class="categories-section">
            <div class="container">
                <div class="categories-grid">
                    <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                        <a href="products.php?category=<?= $category['id'] ?>" class="category-card">
                            <span class="category-icon">🎮</span>
                            <span class="category-name"><?= escape($category['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <!-- Popular Products Section -->
        <section class="section section-popular">
            <div class="container">
                <div class="section-header-modern">
                    <h2 class="section-title">🔥 Популярные товары</h2>
                    <p class="section-subtitle">Выбор наших покупателей этой недели</p>
                    <a href="products.php?sort=popular" class="btn btn-link">Смотреть все →</a>
                </div>
                
                <?php 
                // Если нет популярных товаров, берем просто последние добавленные
                if (empty($popularProducts)) {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_available = TRUE ORDER BY created_at DESC LIMIT 8");
                    $stmt->execute();
                    $popularProducts = $stmt->fetchAll();
                }
                ?>
                
                <?php if (!empty($popularProducts)): ?>
                    <div class="products-grid-modern">
                        <?php foreach (array_slice($popularProducts, 0, 8) as $product): ?>
                            <?php 
                            $discount = calculateDiscount($product['price'], $product['old_price']);
                            ?>
                            <div class="product-card-modern" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                                <?php if ($discount > 0): ?>
                                    <span class="product-badge-modern discount">-<?= $discount ?>%</span>
                                <?php endif; ?>
                                
                                <div class="product-image-wrapper-modern">
                                    <?php 
                                    // Используем image_url если есть, иначе image с префиксом
                                    $imgSrc = '';
                                    if (!empty($product['image_url'])) {
                                        $imgSrc = $product['image_url'];
                                    } elseif (!empty($product['image'])) {
                                        $imgSrc = 'images/uploads/' . $product['image'];
                                    } else {
                                        $imgSrc = 'https://via.placeholder.com/300x200?text=' . urlencode($product['title']);
                                    }
                                    ?>
                                    <img src="<?= escape($imgSrc) ?>" alt="<?= escape($product['name'] ?: $product['title']) ?>" class="product-image-modern" onerror="this.src='https://via.placeholder.com/300x200?text=<?= urlencode($product['title']) ?>'">
                                    <div class="product-actions-modern">
                                        <?php if (isLoggedIn()): ?>
                                            <button class="product-action-btn-modern" data-favorite="<?= $product['id'] ?>" onclick="event.stopPropagation(); toggleFavorite(<?= $product['id'] ?>, 'product')">🤍</button>
                                        <?php endif; ?>
                                        <button class="product-action-btn-modern primary" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)"><svg class="icon-svg-inline" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></button>
                                    </div>
                                </div>
                                
                                <div class="product-info-modern">
                                    <div class="product-category-modern">Игры</div>
                                    <h3 class="product-title-modern"><?= escape($product['name'] ?: $product['title']) ?></h3>
                                    
                                    <div class="product-rating-modern">
                                        <span class="stars"><?= str_repeat('★', round($product['rating'])) ?><?= str_repeat('☆', 5 - round($product['rating'])) ?></span>
                                        <span class="rating-value">(<?= number_format($product['rating'], 1) ?>)</span>
                                    </div>
                                    
                                    <div class="product-footer-modern">
                                        <div class="product-price-modern">
                                            <span class="price-current"><?= formatPrice($product['price']) ?></span>
                                            <?php if ($product['old_price']): ?>
                                                <span class="price-old"><?= formatPrice($product['old_price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">В корзину</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="text-align: center; margin-top: 3rem;">
                        <a href="products.php" class="btn btn-outline btn-lg">📦 Весь каталог</a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📦</span>
                        <h3>Товары скоро появятся</h3>
                        <p>Заходите позже, мы постоянно обновляем ассортимент!</p>
                        <a href="products.php" class="btn btn-primary">Смотреть все товары</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Game Roulette Section -->
        <section class="section section-roulette">
            <div class="container">
                <div class="section-header-modern">
                    <h2 class="section-title">🎰 Не нашли нужную игру?</h2>
                    <p class="section-subtitle">Прокрутите рулетку и получите случайную игру с эксклюзивной скидкой!</p>
                </div>
                
                <button class="btn btn-primary btn-roulette" id="spinRouletteBtn">🎲 Испытать удачу!</button>
                
                <div class="roulette-container" id="rouletteContainer">
                    <div class="roulette-window">
                        <div class="roulette-indicator"></div>
                        <div class="roulette-track" id="rouletteTrack">
                            <!-- Игры будут добавлены через JS -->
                        </div>
                    </div>
                    <div class="roulette-result" id="rouletteResult"></div>
                </div>
            </div>
        </section>
        
        <!-- About Section - Redesigned -->
        <section id="about" class="about-section-modern">
            <div class="container">
                <div class="about-header-modern">
                    <h2>Почему выбирают <span class="gradient-text">JustKey</span></h2>
                    <p>Мы создали лучший сервис для покупки игровых ключей</p>
                </div>
                
                <div class="features-grid-modern">
                    <div class="feature-card-modern">
                        <div class="feature-icon-modern"><svg class="feature-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
                        <h3>Мгновенная доставка</h3>
                        <p>Ключи приходят сразу после оплаты на почту и в личный кабинет</p>
                    </div>
                    <div class="feature-card-modern">
                        <div class="feature-icon-modern">🔒</div>
                        <h3>Гарантия качества</h3>
                        <p>Только официальные ключи от прямых поставщиков</p>
                    </div>
                    <div class="feature-card-modern">
                        <div class="feature-icon-modern"><svg class="feature-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
                        <h3>Безопасная оплата</h3>
                        <p>Платежная система NicePay с защитой данных</p>
                    </div>
                    <div class="feature-card-modern">
                        <div class="feature-icon-modern"><svg class="feature-icon-svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg></div>
                        <h3>Поддержка 24/7</h3>
                        <p>Всегда на связи и готовы помочь в любое время</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Promotions Section - Redesigned -->
        <section id="promotions" class="section section-promotions-modern">
            <div class="container">
                <div class="section-header-modern">
                    <h2 class="section-title"><svg class="section-title-icon-svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg> Акции и скидки</h2>
                    <p class="section-subtitle">Не пропустите выгодные предложения</p>
                </div>
                
                <?php if (!empty($promotions)): ?>
                    <div class="promotions-grid-modern">
                        <?php foreach ($promotions as $promotion): ?>
                            <div class="promotion-card-modern">
                                <div class="promotion-badge">-<?= $promotion['discount_percent'] ?>%</div>
                                <div class="promotion-content">
                                    <h3><?= escape($promotion['title']) ?></h3>
                                    <p><?= escape($promotion['description']) ?></p>
                                    <div class="promotion-timer">
                                        <span class="timer-label">До конца акции:</span>
                                        <span class="timer-date"><?= date('d.m.Y', strtotime($promotion['end_date'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon"><svg class="icon-svg-inline" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg></span>
                        <h3>Сейчас акций нет</h3>
                        <p>Но скоро будут! Следите за обновлениями</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- News Section - Redesigned -->
        <section class="section section-news-modern">
            <div class="container">
                <div class="section-header-modern">
                    <h2 class="section-title">📰 Новости</h2>
                    <p class="section-subtitle">Последние события из мира игр</p>
                    <a href="news.php" class="btn btn-link">Все новости →</a>
                </div>
                
                <?php if (!empty($news)): ?>
                    <div class="news-grid-modern">
                        <?php foreach (array_slice($news, 0, 3) as $item): ?>
                            <div class="news-card-modern">
                                <div class="news-image-wrapper">
                                    <img src="images/uploads/<?= escape($item['image']) ?>" alt="<?= escape($item['title']) ?>" class="news-image" onerror="this.src='https://via.placeholder.com/400x250?text=News'">
                                    <span class="news-category">Новость</span>
                                </div>
                                <div class="news-content">
                                    <h3><?= escape($item['title']) ?></h3>
                                    <p><?= escape($item['short_content'] ?? mb_substr($item['content'], 0, 120) . '...') ?></p>
                                    <div class="news-meta">
                                        <span class="news-date"><?= date('d.m.Y', strtotime($item['published_at'])) ?></span>
                                        <a href="news-item.php?id=<?= $item['id'] ?>" class="news-link">Читать далее →</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📰</span>
                        <h3>Новостей пока нет</h3>
                        <p>Заходите позже!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
