<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Проверка корректности данных пользователя
if (!$user || !is_array($user)) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Получение истории сессий
try {
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE user_id = ? ORDER BY login_time DESC LIMIT 10");
    $stmt->execute([$user['id']]);
    $sessions = $stmt->fetchAll();
    
    // Получение текущих заказов
    $stmt = $pdo->prepare("
        SELECT o.*, GROUP_CONCAT(oi.product_title) as products 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND o.status IN ('pending', 'processing') 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $currentOrders = $stmt->fetchAll();
    
    // Проверка возможности отмены для каждого заказа (24 часа)
    foreach ($currentOrders as &$order) {
        $createdAt = strtotime($order['created_at']);
        $now = time();
        $hoursDiff = ($now - $createdAt) / 3600;
        $order['can_cancel'] = ($hoursDiff <= 24 && $order['status'] === 'pending');
        $order['hours_left'] = max(0, 24 - $hoursDiff);
    }
    unset($order);
    
    // Получение истории заказов
    $stmt = $pdo->prepare("
        SELECT o.*, GROUP_CONCAT(oi.product_title) as products 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? AND o.status IN ('completed', 'cancelled') 
        GROUP BY o.id 
        ORDER BY o.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $orderHistory = $stmt->fetchAll();
    
    // Получение избранных товаров
    $stmt = $pdo->prepare("
        SELECT p.* FROM favorites f 
        JOIN products p ON f.product_id = p.id 
        WHERE f.user_id = ? AND f.product_id IS NOT NULL
    ");
    $stmt->execute([$user['id']]);
    $favorites = $stmt->fetchAll();
    
    // Получение истории просмотров - только 3 элемента
    $stmt = $pdo->prepare("
        SELECT p.*, vh.viewed_at FROM view_history vh 
        JOIN products p ON vh.product_id = p.id 
        WHERE vh.user_id = ? 
        ORDER BY vh.viewed_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$user['id']]);
    $viewHistory = $stmt->fetchAll();
    
    // Получение общего количества просмотров для кнопки "показать все"
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM view_history WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $viewHistoryTotal = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $sessions = [];
    $currentOrders = [];
    $orderHistory = [];
    $favorites = [];
    $viewHistory = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="profile-page">
        <div class="container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="images/uploads/<?= escape($user['avatar']) ?>" alt="<?= escape($user['nickname']) ?>" onerror="this.src='https://via.placeholder.com/150?text=<?= urlencode($user['nickname']) ?>'">
                </div>
                <div class="profile-info">
                    <h1><?= escape($user['full_name']) ?></h1>
                    <p class="nickname">@<?= escape($user['nickname']) ?></p>
                    <p class="email"><?= escape($user['email']) ?></p>
                </div>
                <div class="profile-actions">
                    <a href="logout.php" class="btn btn-danger">🚪 Выйти из аккаунта</a>
                </div>
            </div>
            
            <div class="profile-grid">
                <!-- Текущие заказы -->
                <section class="profile-section">
                    <h2>Текущие заказы</h2>
                    <?php if (!empty($currentOrders)): ?>
                        <div class="orders-list">
                            <?php foreach ($currentOrders as $order): ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <span class="order-number">Заказ #<?= escape($order['order_number']) ?></span>
                                        <span class="order-status status-<?= escape($order['status']) ?>"><?= escape($order['status']) ?></span>
                                    </div>
                                    <div class="order-details">
                                        <p>Товары: <?= escape($order['products']) ?></p>
                                        <p class="order-total">Сумма: <?= formatPrice($order['total_amount']) ?></p>
                                        <?php if ($order['can_cancel']): ?>
                                            <p class="cancel-timer">⏱ Можно отменить в течение <?= ceil($order['hours_left']) ?> ч.</p>
                                            <button class="btn btn-danger btn-sm cancel-order-btn" data-order-id="<?= $order['id'] ?>" data-order-number="<?= escape($order['order_number']) ?>">
                                                ❌ Отменить заказ
                                            </button>
                                        <?php elseif ($order['status'] === 'pending'): ?>
                                            <p class="cancel-expired">⚠️ Срок отмены истёк (прошло более 24 часов)</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Нет активных заказов</p>
                    <?php endif; ?>
                </section>
                
                <!-- Избранное -->
                <section class="profile-section">
                    <h2>Избранные товары</h2>
                    <?php if (!empty($favorites)): ?>
                        <div class="products-grid">
                            <?php foreach (array_slice($favorites, 0, 4) as $product): ?>
                                <div class="product-card">
                                    <div class="product-image-wrapper">
                                        <img src="images/uploads/<?= escape($product['image']) ?>" alt="<?= escape($product['title']) ?>" class="product-image" onerror="this.src='https://via.placeholder.com/300x200?text=<?= urlencode($product['title']) ?>'">
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-title"><?= escape($product['title']) ?></h3>
                                        <p class="product-price"><?= formatPrice($product['price']) ?></p>
                                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">Подробнее</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Нет избранных товаров</p>
                    <?php endif; ?>
                </section>
                
                <!-- История заказов -->
                <section class="profile-section">
                    <h2>История заказов</h2>
                    <?php if (!empty($orderHistory)): ?>
                        <div class="orders-list">
                            <?php foreach ($orderHistory as $order): ?>
                                <div class="order-item">
                                    <div class="order-header">
                                        <span class="order-number">Заказ #<?= escape($order['order_number']) ?></span>
                                        <span class="order-status status-<?= escape($order['status']) ?>"><?= escape($order['status']) ?></span>
                                    </div>
                                    <div class="order-details">
                                        <p>Дата: <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                                        <p>Товары: <?= escape($order['products']) ?></p>
                                        <p class="order-total">Сумма: <?= formatPrice($order['total_amount']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">История заказов пуста</p>
                    <?php endif; ?>
                </section>
                
                <!-- История просмотров -->
                <section class="profile-section">
                    <h2>Недавние просмотры</h2>
                    <?php if (!empty($viewHistory)): ?>
                        <div class="view-history-list">
                            <?php foreach ($viewHistory as $product): ?>
                                <div class="view-item">
                                    <img src="images/uploads/<?= escape($product['image']) ?>" alt="<?= escape($product['title']) ?>" onerror="this.src='https://via.placeholder.com/50x50?text=<?= urlencode($product['title']) ?>'">
                                    <div class="view-info">
                                        <a href="product.php?id=<?= $product['id'] ?>"><?= escape($product['title']) ?></a>
                                        <span class="view-time"><?= timeAgo($product['viewed_at']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($viewHistoryTotal > 3): ?>
                            <div style="margin-top: 1rem; text-align: center;">
                                <button class="btn btn-outline" onclick="toggleViewHistory()" id="toggle-history-btn">
                                    Показать все (<?= $viewHistoryTotal - 3 ?> еще)
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <script>
                        let viewHistoryExpanded = false;
                        let sessionsExpanded = false;
                        
                        async function toggleViewHistory() {
                            const btn = document.getElementById('toggle-history-btn');
                            const container = document.querySelector('.view-history-list');
                            
                            if (viewHistoryExpanded) {
                                // Свернуть - загрузить только 3
                                try {
                                    const response = await fetch('ajax/profile.php?action=view_history&limit=3');
                                    const data = await response.json();
                                    if (data.success) {
                                        renderViewHistory(data.items);
                                        btn.textContent = `Показать все (${data.total - 3} еще)`;
                                        viewHistoryExpanded = false;
                                    }
                                } catch (e) { location.reload(); }
                            } else {
                                // Развернуть - показать все
                                try {
                                    const response = await fetch('ajax/profile.php?action=view_history&limit=100');
                                    const data = await response.json();
                                    if (data.success) {
                                        renderViewHistory(data.items);
                                        btn.textContent = 'Свернуть';
                                        viewHistoryExpanded = true;
                                    }
                                } catch (e) { location.reload(); }
                            }
                        }
                        
                        function renderViewHistory(items) {
                            const container = document.querySelector('.view-history-list');
                            let html = '';
                            items.forEach(product => {
                                html += `
                                    <div class="view-item">
                                        <img src="images/uploads/${product.image}" alt="${product.title}" onerror="this.src='https://via.placeholder.com/50x50?text=${encodeURIComponent(product.title)}'">
                                        <div class="view-info">
                                            <a href="product.php?id=${product.id}">${product.title}</a>
                                            <span class="view-time">${product.viewed_ago}</span>
                                        </div>
                                    </div>
                                `;
                            });
                            container.innerHTML = html;
                        }
                        
                        async function toggleSessions() {
                            const btn = document.getElementById('toggle-sessions-btn');
                            const container = document.getElementById('sessions-container');
                            
                            if (sessionsExpanded) {
                                // Свернуть - загрузить только 5
                                try {
                                    const response = await fetch('ajax/profile.php?action=sessions&limit=5');
                                    const data = await response.json();
                                    if (data.success) {
                                        renderSessions(data.items);
                                        btn.textContent = `Показать все (${data.total - 5} еще)`;
                                        sessionsExpanded = false;
                                    }
                                } catch (e) { location.reload(); }
                            } else {
                                // Развернуть - показать все
                                try {
                                    const response = await fetch('ajax/profile.php?action=sessions&limit=100');
                                    const data = await response.json();
                                    if (data.success) {
                                        renderSessions(data.items);
                                        btn.textContent = 'Свернуть';
                                        sessionsExpanded = true;
                                    }
                                } catch (e) { location.reload(); }
                            }
                        }
                        
                        function renderSessions(items) {
                            const container = document.getElementById('sessions-container');
                            let html = '<table class="sessions-table"><thead><tr><th>Дата входа</th><th>IP адрес</th><th>Устройство</th></tr></thead><tbody>';
                            items.forEach(session => {
                                const date = new Date(session.login_time).toLocaleString('ru-RU', {day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit'});
                                html += `
                                    <tr>
                                        <td>${date}</td>
                                        <td>${session.ip_address}</td>
                                        <td>${session.user_agent.substring(0, 50)}...</td>
                                    </tr>
                                `;
                            });
                            html += '</tbody></table>';
                            container.innerHTML = html;
                        }
                        
                        async function clearSessions() {
                            if (!confirm('Вы уверены, что хотите очистить историю входов?')) return;
                            
                            try {
                                const response = await fetch('ajax/profile.php?action=clear_sessions');
                                const data = await response.json();
                                if (data.success) {
                                    showNotification(data.message, 'success');
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    showNotification(data.message || 'Ошибка очистки', 'error');
                                }
                            } catch (e) {
                                showNotification('Ошибка соединения', 'error');
                            }
                        }
                        </script>
                    <?php else: ?>
                        <p class="no-data">Вы еще ничего не смотрели</p>
                    <?php endif; ?>
                </section>
                
                <!-- Сессии -->
                <section class="profile-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h2 style="margin-bottom: 0;">История входов</h2>
                        <?php if (count($sessions) > 0): ?>
                            <button class="btn btn-outline btn-sm" onclick="clearSessions()" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                🗑 Очистить
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($sessions)): ?>
                        <div id="sessions-container">
                            <table class="sessions-table">
                                <thead>
                                    <tr>
                                        <th>Дата входа</th>
                                        <th>IP адрес</th>
                                        <th>Устройство</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sessionLimit = 5;
                                    $sessionCount = 0;
                                    foreach ($sessions as $session): 
                                        if ($sessionCount >= $sessionLimit) break;
                                        $sessionCount++;
                                    ?>
                                        <tr>
                                            <td><?= date('d.m.Y H:i', strtotime($session['login_time'])) ?></td>
                                            <td><?= escape($session['ip_address']) ?></td>
                                            <td><?= escape(substr($session['user_agent'], 0, 50)) ?>...</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($sessions) > $sessionLimit): ?>
                            <div style="margin-top: 1rem; text-align: center;">
                                <button class="btn btn-outline" onclick="toggleSessions()" id="toggle-sessions-btn">
                                    Показать все (<?= count($sessions) - $sessionLimit ?> еще)
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="no-data">Нет данных о сессиях</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
    <script>
    // Обработка отмены заказа
    document.querySelectorAll('.cancel-order-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const orderId = this.dataset.orderId;
            const orderNumber = this.dataset.orderNumber;
            
            if (!confirm(`Вы уверены, что хотите отменить заказ #${orderNumber}?\n\nОтмена возможна только в течение 24 часов после оформления.`)) {
                return;
            }
            
            try {
                const response = await fetch('ajax/profile.php?action=cancel_order', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message || 'Ошибка отмены заказа', 'error');
                }
            } catch (e) {
                showNotification('Ошибка соединения с сервером', 'error');
            }
        });
    });
    </script>
</body>
</html>
