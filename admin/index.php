<?php
session_start();
require_once '../config.php';

// Проверка на администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Получение статистики
$stats = [
    'products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'news' => $pdo->query("SELECT COUNT(*) FROM news")->fetchColumn(),
    'services' => $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn(),
    'promotions' => $pdo->query("SELECT COUNT(*) FROM promotions WHERE is_active = 1")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'reviews' => $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0")->fetchColumn(),
    'orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - GamesKey</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-header">
            <h1>🛡️ Панель управления</h1>
            <?php include 'includes/theme-toggle.php'; ?>
        </header>
        
        <div class="admin-grid">
            <a href="products.php" class="admin-card">
                <div class="admin-card-icon">📦</div>
                <h3>Товары</h3>
                <p><?= $stats['products'] ?> позиций</p>
            </a>
            
            <a href="news.php" class="admin-card">
                <div class="admin-card-icon">📰</div>
                <h3>Новости</h3>
                <p><?= $stats['news'] ?> публикаций</p>
            </a>
            
            <a href="services.php" class="admin-card">
                <div class="admin-card-icon">🔧</div>
                <h3>Услуги</h3>
                <p><?= $stats['services'] ?> услуг</p>
            </a>
            
            <a href="promotions.php" class="admin-card">
                <div class="admin-card-icon">🎁</div>
                <h3>Акции</h3>
                <p><?= $stats['promotions'] ?> активных</p>
            </a>
            
            <a href="users.php" class="admin-card">
                <div class="admin-card-icon">👥</div>
                <h3>Пользователи</h3>
                <p><?= $stats['users'] ?> человек</p>
            </a>
            
            <a href="reviews.php" class="admin-card">
                <div class="admin-card-icon">💬</div>
                <h3>Отзывы</h3>
                <?php if ($stats['reviews'] > 0): ?>
                    <span class="badge"><?= $stats['reviews'] ?> новых</span>
                <?php else: ?>
                    <p>Нет новых</p>
                <?php endif; ?>
            </a>
            
            <a href="orders.php" class="admin-card">
                <div class="admin-card-icon">🛒</div>
                <h3>Заказы</h3>
                <p><?= $stats['orders'] ?> заказов</p>
            </a>
        </div>
    </main>
</body>
</html>
