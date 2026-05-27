<?php
require_once 'config.php';

// Получение параметров фильтрации и сортировки
$category = $_GET['category'] ?? '';
$platform = $_GET['platform'] ?? '';
$sortBy = $_GET['sort'] ?? 'popular';
$minPrice = $_GET['min_price'] ?? 0;
$maxPrice = $_GET['max_price'] ?? 100000;
$inStock = isset($_GET['in_stock']);
$search = $_GET['search'] ?? '';

// Построение SQL запроса
$where = ['p.is_available = TRUE'];
$params = [];

if ($category) {
    $where[] = 'p.category_id = ?';
    $params[] = $category;
}

if ($platform) {
    $where[] = 'p.platform_id = ?';
    $params[] = $platform;
}

if ($minPrice > 0 || $maxPrice < 100000) {
    $where[] = 'p.price BETWEEN ? AND ?';
    $params[] = $minPrice;
    $params[] = $maxPrice;
}

if ($inStock) {
    $where[] = 'p.stock > 0';
}

if ($search) {
    $where[] = '(p.title LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$whereClause = implode(' AND ', $where);

// Сортировка
$orderBy = 'p.created_at DESC';
switch ($sortBy) {
    case 'price_asc':
        $orderBy = 'p.price ASC';
        break;
    case 'price_desc':
        $orderBy = 'p.price DESC';
        break;
    case 'rating':
        $orderBy = 'p.rating DESC';
        break;
    case 'name':
        $orderBy = 'p.title ASC';
        break;
    case 'popular':
    default:
        $orderBy = 'p.is_featured DESC, p.rating DESC';
}

try {
    // Получение товаров
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, pl.name as platform_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN platforms pl ON p.platform_id = pl.id 
        WHERE $whereClause 
        ORDER BY $orderBy
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Получение категорий для фильтра
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    // Получение платформ для фильтра
    $stmt = $pdo->query("SELECT * FROM platforms ORDER BY name");
    $platforms = $stmt->fetchAll();
    
    // Получение акций
    $stmt = $pdo->query("SELECT * FROM promotions WHERE is_active = TRUE AND end_date > NOW()");
    $promotions = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $categories = [];
    $platforms = [];
    $promotions = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="products-page-modern">
        <!-- Hero секция каталога -->
        <div class="catalog-hero">
            <div class="container">
                <div class="catalog-hero-content">
                    <h1 class="catalog-title">
                        <svg class="icon-svg catalog-title-icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Каталог товаров
                    </h1>
                    <p class="catalog-subtitle">Найдено товаров: <span class="products-count"><?= count($products) ?></span></p>
                </div>
            </div>
        </div>
        
        <div class="container">
            <div class="products-layout-modern">
                <!-- Фильтры -->
                <aside class="filters-sidebar-modern">
                    <div class="filters-header">
                        <svg class="icon-svg filters-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <h2>Фильтры</h2>
                    </div>
                    
                    <form method="GET" action="" class="filters-form-modern">
                        <div class="filter-group-modern">
                            <label class="filter-label">
                                <svg class="icon-svg filter-label-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                Поиск
                            </label>
                            <div class="input-wrapper">
                                <input type="text" name="search" class="filter-input-modern" placeholder="Название товара..." value="<?= escape($search) ?>">
                            </div>
                        </div>
                        
                        <div class="filter-group-modern">
                            <label class="filter-label">
                                <svg class="icon-svg filter-label-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                Категория
                            </label>
                            <div class="select-wrapper">
                                <select name="category" class="filter-select-modern">
                                    <option value="">Все категории</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= escape($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="select-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>
                        </div>
                        
                        <div class="filter-group-modern">
                            <label class="filter-label">
                                <svg class="icon-svg filter-label-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                Платформа
                            </label>
                            <div class="select-wrapper">
                                <select name="platform" class="filter-select-modern">
                                    <option value="">Все платформы</option>
                                    <?php foreach ($platforms as $plat): ?>
                                        <option value="<?= $plat['id'] ?>" <?= $platform == $plat['id'] ? 'selected' : '' ?>><?= escape($plat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <svg class="select-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>
                        </div>
                        
                        <div class="filter-group-modern">
                            <label class="filter-label">
                                <svg class="icon-svg filter-label-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                Цена
                            </label>
                            <div class="price-range-modern">
                                <div class="price-input-wrapper">
                                    <span class="price-input-label">От</span>
                                    <input type="number" name="min_price" class="filter-input-modern price-input" placeholder="0" value="<?= escape($minPrice) ?>" min="0">
                                </div>
                                <div class="price-input-wrapper">
                                    <span class="price-input-label">До</span>
                                    <input type="number" name="max_price" class="filter-input-modern price-input" placeholder="100000" value="<?= escape($maxPrice) ?>" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="filter-group-modern">
                            <label class="checkbox-label-modern">
                                <input type="checkbox" name="in_stock" <?= $inStock ? 'checked' : '' ?>>
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-text">В наличии</span>
                            </label>
                        </div>
                        
                        <div class="filter-group-modern">
                            <label class="filter-label">
                                <svg class="icon-svg filter-label-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 6h18M6 12h12M9 18h9"/>
                                Сортировка
                            </label>
                            <div class="select-wrapper">
                                <select name="sort" class="filter-select-modern">
                                    <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Популярные</option>
                                    <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Цена: по возрастанию</option>
                                    <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Цена: по убыванию</option>
                                    <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>По рейтингу</option>
                                    <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>По названию</option>
                                </select>
                                <svg class="select-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </div>
                        </div>
                        
                        <div class="filter-actions-modern">
                            <button type="submit" class="btn btn-primary-modern btn-block-modern">
                                <svg class="icon-svg btn-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Применить
                            </button>
                            <a href="products.php" class="btn btn-outline-modern btn-block-modern">
                                <svg class="icon-svg btn-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                Сбросить
                            </a>
                        </div>
                    </form>
                </aside>
                
                <!-- Товары -->
                <div class="products-content-modern">
                    <?php if (!empty($promotions)): ?>
                        <div class="promotions-banner-modern">
                            <?php foreach ($promotions as $promo): ?>
                                <div class="promo-item-modern">
                                    <div class="promo-badge">
                                        <svg class="icon-svg promo-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    </div>
                                    <h3><?= escape($promo['title']) ?></h3>
                                    <p><?= escape($promo['description']) ?></p>
                                    <span class="promo-discount-modern">-<?= $promo['discount_percent'] ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($products)): ?>
                        <div class="products-grid-modern">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card-modern" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                                    <?php 
                                    $discount = calculateDiscount($product['price'], $product['old_price']);
                                    if ($discount > 0): ?>
                                        <span class="product-badge-modern discount-modern">
                                            <svg class="icon-svg badge-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                            -<?= $discount ?>%
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['stock'] > 0): ?>
                                        <span class="product-badge-modern stock-modern">
                                            <svg class="icon-svg badge-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                            В наличии
                                        </span>
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
                                        <img src="<?= escape($imgSrc) ?>" alt="<?= escape($product['title']) ?>" class="product-image-modern" onerror="this.src='https://via.placeholder.com/300x200?text=<?= urlencode($product['title']) ?>'">
                                        <div class="product-overlay-modern">
                                            <div class="product-actions-modern">
                                                <?php if (isLoggedIn()): ?>
                                                    <button class="product-action-btn-modern" data-favorite="<?= $product['id'] ?>" onclick="event.stopPropagation(); toggleFavorite(<?= $product['id'] ?>)" title="В избранное">
                                                        <svg class="icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="product-action-btn-modern cart-modern" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)" title="В корзину">
                                                    <svg class="icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="product-info-modern">
                                        <div class="product-category-modern">
                                            <svg class="icon-svg category-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                            <?= escape($product['category_name'] ?? 'Игры') ?>
                                        </div>
                                        <h3 class="product-title-modern"><?= escape($product['name'] ?: $product['title']) ?></h3>
                                        <p class="product-description-modern"><?= escape($product['short_description'] ?? mb_substr($product['description'], 0, 80) . '...') ?></p>
                                        
                                        <div class="product-rating-modern">
                                            <div class="stars-modern">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <svg class="star-icon <?= $i <= round($product['rating']) ? 'filled' : '' ?>" width="16" height="16" viewBox="0 0 24 24" fill="<?= $i <= round($product['rating']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="rating-value-modern"><?= number_format($product['rating'], 1) ?></span>
                                        </div>
                                        
                                        <div class="product-footer-modern">
                                            <div class="product-price-modern">
                                                <span class="price-current-modern"><?= formatPrice($product['price']) ?></span>
                                                <?php if ($product['old_price']): ?>
                                                    <span class="price-old-modern"><?= formatPrice($product['old_price']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <button class="btn btn-primary-modern btn-sm-modern" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>)">
                                                <svg class="icon-svg btn-icon-sm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                                                В корзину
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-products-modern">
                            <div class="no-products-icon">
                                <svg class="icon-svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                            <h2>Товары не найдены</h2>
                            <p>Попробуйте изменить параметры поиска или фильтры</p>
                            <a href="products.php" class="btn btn-primary-modern">
                                <svg class="icon-svg btn-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                Сбросить фильтры
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
