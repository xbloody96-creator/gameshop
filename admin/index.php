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
    <style>
        /* Компактная верхняя навигация только для index.php */
        .admin-nav-compact {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 15px 20px;
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .admin-nav-compact a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 1.3rem;
            transition: all var(--transition-fast);
            background: var(--bg-surface-2);
            border: 1px solid var(--border);
            position: relative;
        }
        
        .admin-nav-compact a:hover,
        .admin-nav-compact a.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .admin-nav-compact a::after {
            content: attr(title);
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--text-primary);
            color: var(--bg-surface);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity var(--transition-fast);
            z-index: 100;
        }
        
        .admin-nav-compact a:hover::after {
            opacity: 1;
        }
        
        /* Скрываем сайдбар на главной */
        .admin-main {
            margin-left: 0 !important;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .admin-header {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="admin-body">
    <main class="admin-main">
        <header class="admin-header">
            <h1>🛡️ Панель управления</h1>
            <?php include 'includes/theme-toggle.php'; ?>
        </header>
        
        <!-- Компактная навигация -->
        <nav class="admin-nav-compact">
            <a href="products.php" title="Товары">📦</a>
            <a href="news.php" title="Новости">📰</a>
            <a href="services.php" title="Услуги">🔧</a>
            <a href="promotions.php" title="Акции">🎁</a>
            <a href="users.php" title="Пользователи">👥</a>
            <a href="reviews.php" title="Отзывы">💬</a>
            <a href="orders.php" title="Заказы">🛒</a>
        </nav>
        
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
