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
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="products-page">
        <div class="container">
            <div class="page-header">
                <h1>Каталог товаров</h1>
                <p>Найдено товаров: <?= count($products) ?></p>
            </div>
            
            <div class="products-layout">
                <!-- Фильтры -->
                <aside class="filters-sidebar">
                    <form method="GET" action="" class="filters-form">
                        <div class="filter-group">
                            <h3>Поиск</h3>
                            <input type="text" name="search" class="filter-input" placeholder="Название товара..." value="<?= escape($search) ?>">
                        </div>
                        
                        <div class="filter-group">
                            <h3>Категория</h3>
                            <select name="category" class="filter-select">
                                <option value="">Все категории</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= escape($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <h3>Платформа</h3>
                            <select name="platform" class="filter-select">
                                <option value="">Все платформы</option>
                                <?php foreach ($platforms as $plat): ?>
                                    <option value="<?= $plat['id'] ?>" <?= $platform == $plat['id'] ? 'selected' : '' ?>><?= escape($plat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <h3>Цена</h3>
                            <div class="price-range">
                                <input type="number" name="min_price" class="filter-input" placeholder="От" value="<?= escape($minPrice) ?>" min="0">
                                <input type="number" name="max_price" class="filter-input" placeholder="До" value="<?= escape($maxPrice) ?>" min="0">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="in_stock" <?= $inStock ? 'checked' : '' ?>>
                                В наличии
                            </label>
                        </div>
                        
                        <div class="filter-group">
                            <h3>Сортировка</h3>
                            <select name="sort" class="filter-select">
                                <option value="popular" <?= $sortBy === 'popular' ? 'selected' : '' ?>>Популярные</option>
                                <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Цена: по возрастанию</option>
                                <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Цена: по убыванию</option>
                                <option value="rating" <?= $sortBy === 'rating' ? 'selected' : '' ?>>По рейтингу</option>
                                <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>По названию</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Применить</button>
                        <a href="products.php" class="btn btn-outline btn-block">Сбросить</a>
                    </form>
                </aside>
                
                <!-- Товары -->
                <div class="products-content">
                    <?php if (!empty($promotions)): ?>
                        <div class="promotions-banner">
                            <?php foreach ($promotions as $promo): ?>
                                <div class="promo-item">
                                    <h3><?= escape($promo['title']) ?></h3>
                                    <p><?= escape($promo['description']) ?></p>
                                    <span class="promo-discount">-<?= $promo['discount_percent'] ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($products)): ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['id'] ?>'">
                                    <?php 
                                    $discount = calculateDiscount($product['price'], $product['old_price']);
                                    if ($discount > 0): ?>
                                        <span class="product-badge discount">-<?= $discount ?>%</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['stock'] > 0): ?>
                                        <span class="product-badge new">В наличии</span>
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
                                        <div class="product-category"><?= escape($product['category_name'] ?? 'Игры') ?></div>
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
                    <?php else: ?>
                        <div class="no-products">
                            <h2>Товары не найдены</h2>
                            <p>Попробуйте изменить параметры поиска или фильтры</p>
                            <a href="products.php" class="btn btn-primary">Сбросить фильтры</a>
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
