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
    'reviews_pending' => $pdo->query("SELECT COUNT(*) FROM reviews WHERE is_approved = 0")->fetchColumn(),
    'orders_total' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
];

// Продажи за день
$sales_today_stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'");
$sales_today_stmt->execute();
$sales_today = $sales_today_stmt->fetchColumn() ?? 0;

// Новые заказы за день
$new_orders_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
$new_orders_stmt->execute();
$new_orders = $new_orders_stmt->fetchColumn() ?? 0;

// Последние 5 заказов
$last_orders_stmt = $pdo->query("SELECT o.*, u.full_name as user_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5");
$last_orders = $last_orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - GamesKey</title>
    <link rel="stylesheet" href="admin-style.css">
    <style>
        /* Compact Top Menu */
        .compact-top-menu {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 15px 20px;
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .compact-menu-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-primary);
            padding: 10px 14px;
            border-radius: var(--radius);
            transition: all var(--transition-fast);
            position: relative;
            min-width: 50px;
        }
        
        .compact-menu-item:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }
        
        .compact-menu-item.active {
            background: var(--primary);
            color: white;
        }
        
        .compact-menu-icon {
            font-size: 1.4rem;
            margin-bottom: 4px;
        }
        
        .compact-menu-label {
            font-size: 0.7rem;
            font-weight: 500;
            text-align: center;
        }
        
        /* Tooltip */
        .compact-menu-item .tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-5px);
            background: var(--text-primary);
            color: var(--bg-surface);
            padding: 6px 10px;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 100;
            box-shadow: var(--shadow-md);
        }
        
        .compact-menu-item .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: var(--text-primary);
        }
        
        .compact-menu-item:hover .tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-8px);
        }
        
        /* Stats Cards Grid */
        .stats-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card-mini {
            background: var(--bg-surface);
            padding: 20px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            transition: all var(--transition);
        }
        
        .stat-card-mini:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card-mini.primary { border-left: 4px solid var(--success); }
        .stat-card-mini.success { border-left: 4px solid var(--info); }
        .stat-card-mini.warning { border-left: 4px solid var(--warning); }
        .stat-card-mini.danger { border-left: 4px solid var(--danger); }
        
        .stat-card-header-mini {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .stat-card-title {
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin: 0;
        }
        
        .stat-card-icon-mini {
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-card-mini.primary .stat-card-icon-mini { background: var(--success-bg); }
        .stat-card-mini.success .stat-card-icon-mini { background: var(--info-bg); }
        .stat-card-mini.warning .stat-card-icon-mini { background: var(--warning-bg); }
        .stat-card-mini.danger .stat-card-icon-mini { background: var(--danger-bg); }
        
        .stat-card-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .stat-card-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin: 5px 0 0;
        }
        
        /* Orders Table Section */
        .orders-table-section {
            background: var(--bg-surface);
            padding: 25px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .orders-table-wrapper {
            overflow-x: auto;
        }
        
        .compact-orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        
        .compact-orders-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
        }
        
        .compact-orders-table td {
            padding: 14px 15px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-primary);
            vertical-align: middle;
        }
        
        .compact-orders-table tbody tr:hover {
            background: var(--bg-hover);
        }
        
        .order-id {
            font-weight: 600;
            color: var(--primary);
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: var(--radius-xl);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-badge.pending { background: var(--warning-bg); color: var(--warning); }
        .status-badge.confirmed { background: var(--info-bg); color: var(--info); }
        .status-badge.processing { background: var(--primary-light); color: var(--primary); }
        .status-badge.completed { background: var(--success-bg); color: var(--success); }
        .status-badge.cancelled { background: var(--danger-bg); color: var(--danger); }
        
        /* Quick Links Block */
        .quick-links-block {
            background: var(--bg-surface);
            padding: 25px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        
        .quick-link-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: var(--bg-surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all var(--transition-fast);
        }
        
        .quick-link-item:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateX(5px);
        }
        
        .quick-link-icon {
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-header">
            <h1>🛡️ Панель управления</h1>
            <?php include 'includes/theme-toggle.php'; ?>
        </header>
        
        <!-- Компактное верхнее меню с иконками -->
        <nav class="compact-top-menu">
            <a href="products.php" class="compact-menu-item" title="Товары">
                <span class="compact-menu-icon">📦</span>
                <span class="compact-menu-label">Товары</span>
                <span class="tooltip">Управление товарами</span>
            </a>
            <a href="news.php" class="compact-menu-item" title="Новости">
                <span class="compact-menu-icon">📰</span>
                <span class="compact-menu-label">Новости</span>
                <span class="tooltip">Лента новостей</span>
            </a>
            <a href="services.php" class="compact-menu-item" title="Услуги">
                <span class="compact-menu-icon">🔧</span>
                <span class="compact-menu-label">Услуги</span>
                <span class="tooltip">Список услуг</span>
            </a>
            <a href="promotions.php" class="compact-menu-item" title="Акции">
                <span class="compact-menu-icon">🎁</span>
                <span class="compact-menu-label">Акции</span>
                <span class="tooltip">Акционные предложения</span>
            </a>
            <a href="users.php" class="compact-menu-item" title="Пользователи">
                <span class="compact-menu-icon">👥</span>
                <span class="compact-menu-label">Пользователи</span>
                <span class="tooltip">База пользователей</span>
            </a>
            <a href="reviews.php" class="compact-menu-item" title="Отзывы">
                <span class="compact-menu-icon">💬</span>
                <span class="compact-menu-label">Отзывы</span>
                <span class="tooltip">Модерация отзывов</span>
            </a>
            <a href="orders.php" class="compact-menu-item active" title="Заказы">
                <span class="compact-menu-icon">🛒</span>
                <span class="compact-menu-label">Заказы</span>
                <span class="tooltip">Все заказы</span>
            </a>
        </nav>
        
        <!-- 4 карточки статистики -->
        <div class="stats-cards-grid">
            <div class="stat-card-mini primary">
                <div class="stat-card-header-mini">
                    <p class="stat-card-title">Продажи за день</p>
                    <div class="stat-card-icon-mini">💰</div>
                </div>
                <h3 class="stat-card-value"><?= number_format($sales_today, 0, '.', ' ') ?> ₽</h3>
                <p class="stat-card-subtitle">на текущий момент</p>
            </div>
            
            <div class="stat-card-mini success">
                <div class="stat-card-header-mini">
                    <p class="stat-card-title">Новые заказы</p>
                    <div class="stat-card-icon-mini">📋</div>
                </div>
                <h3 class="stat-card-value"><?= $new_orders ?></h3>
                <p class="stat-card-subtitle">за сегодня</p>
            </div>
            
            <div class="stat-card-mini warning">
                <div class="stat-card-header-mini">
                    <p class="stat-card-title">Всего пользователей</p>
                    <div class="stat-card-icon-mini">👥</div>
                </div>
                <h3 class="stat-card-value"><?= $stats['users'] ?></h3>
                <p class="stat-card-subtitle">зарегистрировано</p>
            </div>
            
            <div class="stat-card-mini danger">
                <div class="stat-card-header-mini">
                    <p class="stat-card-title">Отзывы на модерации</p>
                    <div class="stat-card-icon-mini">⏳</div>
                </div>
                <h3 class="stat-card-value"><?= $stats['reviews_pending'] ?></h3>
                <p class="stat-card-subtitle">требуют проверки</p>
            </div>
        </div>
        
        <!-- Таблица с последними 5 заказами -->
        <div class="orders-table-section">
            <h2 class="section-title">📊 Последние заказы</h2>
            <div class="orders-table-wrapper">
                <table class="compact-orders-table">
                    <thead>
                        <tr>
                            <th>№ Заказа</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Дата</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($last_orders) > 0): ?>
                            <?php foreach ($last_orders as $order): ?>
                                <tr>
                                    <td><span class="order-id">#<?= $order['id'] ?></span></td>
                                    <td><?= htmlspecialchars($order['user_name'] ?? $order['email'] ?? 'Гость') ?></td>
                                    <td><strong><?= number_format($order['total_amount'], 0, '.', ' ') ?> ₽</strong></td>
                                    <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= $order['status'] ?>">
                                            <?php
                                            $status_labels = [
                                                'pending' => 'Ожидает',
                                                'confirmed' => 'Подтверждён',
                                                'processing' => 'В работе',
                                                'completed' => 'Выполнен',
                                                'cancelled' => 'Отменён'
                                            ];
                                            echo $status_labels[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    Заказов пока нет
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Блок быстрых ссылок -->
        <div class="quick-links-block">
            <h2 class="section-title">🔗 Быстрые ссылки</h2>
            <div class="quick-links-grid">
                <a href="products.php" class="quick-link-item">
                    <span class="quick-link-icon">📦</span>
                    <span>Все товары</span>
                </a>
                <a href="product-edit.php?action=add" class="quick-link-item">
                    <span class="quick-link-icon">➕</span>
                    <span>Добавить товар</span>
                </a>
                <a href="orders.php" class="quick-link-item">
                    <span class="quick-link-icon">🛒</span>
                    <span>Все заказы</span>
                </a>
                <a href="users.php" class="quick-link-item">
                    <span class="quick-link-icon">👥</span>
                    <span>Пользователи</span>
                </a>
                <a href="news.php" class="quick-link-item">
                    <span class="quick-link-icon">📰</span>
                    <span>Новости</span>
                </a>
                <a href="news.php?action=add" class="quick-link-item">
                    <span class="quick-link-icon">✏️</span>
                    <span>Добавить новость</span>
                </a>
                <a href="services.php" class="quick-link-item">
                    <span class="quick-link-icon">🔧</span>
                    <span>Услуги</span>
                </a>
                <a href="promotions.php" class="quick-link-item">
                    <span class="quick-link-icon">🎁</span>
                    <span>Акции</span>
                </a>
                <a href="reviews.php" class="quick-link-item">
                    <span class="quick-link-icon">💬</span>
                    <span>Отзывы</span>
                </a>
                <a href="../index.php" target="_blank" class="quick-link-item">
                    <span class="quick-link-icon">🌐</span>
                    <span>На сайт</span>
                </a>
            </div>
        </div>
    </main>
</body>
</html>
