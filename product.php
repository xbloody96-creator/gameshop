<?php
require_once 'config.php';

$productId = $_GET['id'] ?? 0;

if (!$productId) {
    header('Location: products.php');
    exit;
}

try {
    // Получение товара
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, pl.name as platform_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN platforms pl ON p.platform_id = pl.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: products.php');
        exit;
    }
    
    // Увеличение счетчика просмотров
    $stmt = $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
    $stmt->execute([$productId]);
    
    // Запись в историю просмотров (если авторизован)
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("INSERT INTO view_history (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed_at = NOW()");
        $stmt->execute([$_SESSION['user_id'], $productId]);
    }
    
    // Получение отзывов
    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name, u.nickname, u.avatar 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? AND r.is_approved = TRUE 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll();
    
    // Проверка, оставлял ли пользователь отзыв
    $userReview = null;
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        $userReview = $stmt->fetch();
    }
    
    // Похожие товары
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = ? AND id != ? AND is_available = TRUE 
        ORDER BY rating DESC 
        LIMIT 4
    ");
    $stmt->execute([$product['category_id'], $productId]);
    $relatedProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    $product = null;
    $reviews = [];
    $relatedProducts = [];
}

if (!$product) {
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($product['title']) ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="product-page">
        <div class="container">
            <nav class="breadcrumbs">
                <a href="index.php">Главная</a> / 
                <a href="products.php">Каталог</a> / 
                <span><?= escape($product['title']) ?></span>
            </nav>
            
            <div class="product-detail">
                <div class="product-gallery">
                    <img src="images/uploads/<?= escape($product['image']) ?>" alt="<?= escape($product['title']) ?>" class="main-image" onerror="this.src='https://via.placeholder.com/600x400?text=<?= urlencode($product['title']) ?>'">
                </div>
                
                <div class="product-info-detail">
                    <h1><?= escape($product['title']) ?></h1>
                    
                    <div class="product-meta">
                        <span class="product-category"><?= escape($product['category_name'] ?? 'Игры') ?></span>
                        <?php if ($product['platform_name']): ?>
                            <span class="product-platform"><?= escape($product['platform_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-rating-large">
                        <span class="stars"><?= str_repeat('★', round($product['rating'])) ?><?= str_repeat('☆', 5 - round($product['rating'])) ?></span>
                        <span class="rating-value"><?= number_format($product['rating'], 2) ?> / 5</span>
                        <span class="rating-count">(<?= count($reviews) ?> отзывов)</span>
                    </div>
                    
                    <div class="product-price-large">
                        <span class="price-current"><?= formatPrice($product['price']) ?></span>
                        <?php if ($product['old_price']): ?>
                            <span class="price-old"><?= formatPrice($product['old_price']) ?></span>
                            <?php $discount = calculateDiscount($product['price'], $product['old_price']); ?>
                            <span class="price-discount">-<?= $discount ?>%</span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="product-description-full"><?= nl2br(escape($product['description'])) ?></p>
                    
                    <div class="product-status">
                        <?php if ($product['stock'] > 0): ?>
                            <span class="in-stock">✓ В наличии (<?= $product['stock'] ?> шт.)</span>
                        <?php else: ?>
                            <span class="out-of-stock">✗ Нет в наличии</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-actions-large">
                        <?php if ($product['stock'] > 0): ?>
                            <button class="btn btn-primary btn-lg" onclick="addToCart(<?= $product['id'] ?>)">
                                🛒 Добавить в корзину
                            </button>
                        <?php endif; ?>
                        
                        <?php if (isLoggedIn()): ?>
                            <button class="btn btn-outline btn-lg" onclick="toggleFavorite(<?= $product['id'] ?>)">
                                ❤️ В избранное
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['system_requirements']): ?>
                        <div class="system-requirements">
                            <h3>Системные требования</h3>
                            <pre><?= escape(json_encode($product['system_requirements'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Отзывы -->
            <section class="reviews-section">
                <h2>Отзывы (<?= count($reviews) ?>)</h2>
                
                <?php if (isLoggedIn() && !$userReview): ?>
                    <div class="review-form-wrapper">
                        <h3>Оставить отзыв</h3>
                        <form class="review-form" onsubmit="event.preventDefault(); submitReview(<?= $product['id'] ?>);">
                            <div class="rating-input">
                                <label>Ваша оценка:</label>
                                <div class="stars-input">
                                    <input type="radio" name="rating" value="5" id="star5"><label for="star5">★</label>
                                    <input type="radio" name="rating" value="4" id="star4"><label for="star4">★</label>
                                    <input type="radio" name="rating" value="3" id="star3"><label for="star3">★</label>
                                    <input type="radio" name="rating" value="2" id="star2"><label for="star2">★</label>
                                    <input type="radio" name="rating" value="1" id="star1"><label for="star1">★</label>
                                </div>
                            </div>
                            <textarea id="review-comment" placeholder="Напишите ваш отзыв..." rows="4" required></textarea>
                            <button type="submit" class="btn btn-primary">Отправить на модерацию</button>
                        </form>
                    </div>
                <?php elseif ($userReview): ?>
                    <div class="user-review-display">
                        <h3>Ваш отзыв</h3>
                        <div class="review-status-block">
                            <p class="review-status">Статус: <?= $userReview['is_approved'] ? '✓ Одобрено' : '⏳ На модерации' ?></p>
                            <div class="review-content-preview">
                                <div class="review-rating-display">
                                    <?= str_repeat('★', $userReview['rating']) ?><?= str_repeat('☆', 5 - $userReview['rating']) ?>
                                </div>
                                <p class="review-comment-preview"><?= nl2br(escape($userReview['comment'])) ?></p>
                            </div>
                            <button type="button" class="btn btn-outline" onclick="showEditForm()">✏️ Редактировать</button>
                        </div>
                    </div>
                    
                    <div class="review-edit-wrapper" id="edit-form-container" style="display: none;">
                        <h3>Редактирование отзыва</h3>
                        <form class="review-form" onsubmit="event.preventDefault(); updateReview(<?= $userReview['id'] ?>, <?= $product['id'] ?>);">
                            <div class="rating-input">
                                <label>Ваша оценка:</label>
                                <div class="stars-input">
                                    <input type="radio" name="edit-rating" value="5" id="edit-star5" <?= $userReview['rating'] == 5 ? 'checked' : '' ?>><label for="edit-star5">★</label>
                                    <input type="radio" name="edit-rating" value="4" id="edit-star4" <?= $userReview['rating'] == 4 ? 'checked' : '' ?>><label for="edit-star4">★</label>
                                    <input type="radio" name="edit-rating" value="3" id="edit-star3" <?= $userReview['rating'] == 3 ? 'checked' : '' ?>><label for="edit-star3">★</label>
                                    <input type="radio" name="edit-rating" value="2" id="edit-star2" <?= $userReview['rating'] == 2 ? 'checked' : '' ?>><label for="edit-star2">★</label>
                                    <input type="radio" name="edit-rating" value="1" id="edit-star1" <?= $userReview['rating'] == 1 ? 'checked' : '' ?>><label for="edit-star1">★</label>
                                </div>
                            </div>
                            <textarea id="edit-review-comment" placeholder="Напишите ваш отзыв..." rows="4" required><?= escape($userReview['comment']) ?></textarea>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                <button type="button" class="btn btn-outline" onclick="hideEditForm()">Отмена</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-author">
                                    <img src="images/uploads/<?= escape($review['avatar']) ?>" alt="<?= escape($review['nickname']) ?>" class="author-avatar" onerror="this.src='https://via.placeholder.com/50?text=<?= urlencode($review['nickname']) ?>'">
                                    <div>
                                        <span class="author-name"><?= escape($review['full_name']) ?></span>
                                        <span class="author-nickname">@<?= escape($review['nickname']) ?></span>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
                                </div>
                            </div>
                            <p class="review-comment"><?= nl2br(escape($review['comment'])) ?></p>
                            <span class="review-date"><?= timeAgo($review['created_at']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($reviews)): ?>
                        <p class="no-reviews">Отзывов пока нет. Будьте первым!</p>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Похожие товары -->
            <?php if (!empty($relatedProducts)): ?>
                <section class="related-products">
                    <h2>Похожие товары</h2>
                    <div class="products-grid">
                        <?php foreach ($relatedProducts as $item): ?>
                            <div class="product-card" onclick="window.location.href='product.php?id=<?= $item['id'] ?>'">
                                <div class="product-image-wrapper">
                                    <img src="images/uploads/<?= escape($item['image']) ?>" alt="<?= escape($item['title']) ?>" class="product-image" onerror="this.src='https://via.placeholder.com/300x200?text=<?= urlencode($item['title']) ?>'">
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title"><?= escape($item['title']) ?></h3>
                                    <div class="product-price">
                                        <span class="price-current"><?= formatPrice($item['price']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
