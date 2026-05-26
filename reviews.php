<?php
require_once 'config.php';

// Получение отзывов
try {
    $stmt = $pdo->prepare("SELECT r.*, p.title as product_title, u.nickname, u.avatar 
                          FROM reviews r 
                          JOIN products p ON r.product_id = p.id 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.is_approved = TRUE 
                          ORDER BY r.created_at DESC");
    $stmt->execute();
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="info-page">
        <div class="container">
            <h1>Отзывы покупателей</h1>
            <div class="reviews-list">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="review-author">
                                    <?php if (!empty($review['avatar'])): ?>
                                        <img src="images/uploads/<?= escape($review['avatar']) ?>" alt="<?= escape($review['nickname']) ?>" class="review-avatar">
                                    <?php else: ?>
                                        <div class="review-avatar-placeholder"><?= mb_substr($review['nickname'], 0, 1) ?></div>
                                    <?php endif; ?>
                                    <span class="review-nickname"><?= escape($review['nickname']) ?></span>
                                </div>
                                <div class="review-rating"><?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?></div>
                            </div>
                            <div class="review-product">Товар: <?= escape($review['product_title']) ?></div>
                            <div class="review-comment"><?= nl2br(escape($review['comment'])) ?></div>
                            <div class="review-date"><?= date('d.m.Y', strtotime($review['created_at'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-reviews">Отзывов пока нет. Будьте первым!</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="main.js"></script>
</body>
</html>
