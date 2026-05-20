<?php
session_start();
require_once '../config.php';

// Проверка на администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_product':
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = intval($_POST['category_id']);
            $platform = trim($_POST['platform']);
            $stock = intval($_POST['stock']);
            $image_url = trim($_POST['image_url']);
            
            if ($name && $price > 0 && $category_id) {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, platform, stock, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$name, $description, $price, $category_id, $platform, $stock, $image_url]);
                $success = 'Товар успешно добавлен';
            } else {
                $error = 'Заполните обязательные поля';
            }
            break;
            
        case 'edit_product':
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = intval($_POST['category_id']);
            $platform = trim($_POST['platform']);
            $stock = intval($_POST['stock']);
            $image_url = trim($_POST['image_url']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, platform=?, stock=?, image_url=?, is_active=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $category_id, $platform, $stock, $image_url, $is_active, $id]);
            $success = 'Товар успешно обновлен';
            break;
            
        case 'delete_product':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Товар удален';
            break;
            
        case 'add_news':
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $image_url = trim($_POST['image_url']);
            $rating = floatval($_POST['rating']);
            
            if ($title && $content) {
                $stmt = $pdo->prepare("INSERT INTO news (title, content, image_url, rating, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$title, $content, $image_url, $rating]);
                $success = 'Новость добавлена';
            } else {
                $error = 'Заполните заголовок и содержание';
            }
            break;
            
        case 'edit_news':
            $id = intval($_POST['id']);
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $image_url = trim($_POST['image_url']);
            $rating = floatval($_POST['rating']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE news SET title=?, content=?, image_url=?, rating=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $content, $image_url, $rating, $is_active, $id]);
            $success = 'Новость обновлена';
            break;
            
        case 'delete_news':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM news WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Новость удалена';
            break;
            
        case 'add_service':
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $duration = intval($_POST['duration']);
            $image_url = trim($_POST['image_url']);
            
            if ($name && $price > 0) {
                $stmt = $pdo->prepare("INSERT INTO services (name, description, price, duration, image_url, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$name, $description, $price, $duration, $image_url]);
                $success = 'Услуга добавлена';
            } else {
                $error = 'Заполните обязательные поля';
            }
            break;
            
        case 'edit_service':
            $id = intval($_POST['id']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $duration = intval($_POST['duration']);
            $image_url = trim($_POST['image_url']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE services SET name=?, description=?, price=?, duration=?, image_url=?, is_active=? WHERE id=?");
            $stmt->execute([$name, $description, $price, $duration, $image_url, $is_active, $id]);
            $success = 'Услуга обновлена';
            break;
            
        case 'delete_service':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM services WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Услуга удалена';
            break;
            
        case 'add_promotion':
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $discount = intval($_POST['discount']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $image_url = trim($_POST['image_url']);
            
            if ($title && $discount > 0 && $start_date && $end_date) {
                $stmt = $pdo->prepare("INSERT INTO promotions (title, description, discount, start_date, end_date, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$title, $description, $discount, $start_date, $end_date, $image_url]);
                $success = 'Акция добавлена';
            } else {
                $error = 'Заполните обязательные поля';
            }
            break;
            
        case 'edit_promotion':
            $id = intval($_POST['id']);
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $discount = intval($_POST['discount']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $image_url = trim($_POST['image_url']);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE promotions SET title=?, description=?, discount=?, start_date=?, end_date=?, image_url=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $description, $discount, $start_date, $end_date, $image_url, $is_active, $id]);
            $success = 'Акция обновлена';
            break;
            
        case 'delete_promotion':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM promotions WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Акция удалена';
            break;
            
        case 'edit_user':
            $id = intval($_POST['id']);
            $fullname = trim($_POST['fullname']);
            $email = trim($_POST['email']);
            $nickname = trim($_POST['nickname']);
            $role = $_POST['role'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE users SET fullname=?, email=?, nickname=?, role=?, is_active=? WHERE id=?");
            $stmt->execute([$fullname, $email, $nickname, $role, $is_active, $id]);
            $success = 'Пользователь обновлен';
            break;
            
        case 'delete_user':
            $id = intval($_POST['id']);
            if ($id != $_SESSION['user_id']) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
                $stmt->execute([$id]);
                $success = 'Пользователь удален';
            } else {
                $error = 'Нельзя удалить себя';
            }
            break;
            
        case 'moderate_review':
            $id = intval($_POST['id']);
            $approved = isset($_POST['approved']) ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved=? WHERE id=?");
            $stmt->execute([$approved, $id]);
            $success = 'Отзыв обновлен';
            break;
            
        case 'delete_review':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Отзыв удален';
            break;
    }
}

// Получение данных для отображения
$tab = $_GET['tab'] ?? 'dashboard'; // Изменили default на dashboard

// Вспомогательная функция для безопасного подсчета
function safe_count($data) {
    if (is_array($data)) return count($data);
    if ($data instanceof Countable) return count($data);
    return 0;
}

// Товары
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$products = $products ? $products->fetchAll() : [];
if (!is_array($products)) $products = [];

// Новости
$news = $pdo->query("SELECT * FROM news ORDER BY created_at DESC");
$news = $news ? $news->fetchAll() : [];
if (!is_array($news)) $news = [];

// Услуги
$services = $pdo->query("SELECT * FROM services ORDER BY id DESC");
$services = $services ? $services->fetchAll() : [];
if (!is_array($services)) $services = [];

// Акции
$promotions = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
$promotions = $promotions ? $promotions->fetchAll() : [];
if (!is_array($promotions)) $promotions = [];

// Пользователи
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $users ? $users->fetchAll() : [];
if (!is_array($users)) $users = [];

// Отзывы на модерации
$pending_reviews = $pdo->query("SELECT r.*, u.full_name, u.nickname, p.title as product_name FROM reviews r JOIN users u ON r.user_id = u.id LEFT JOIN products p ON r.product_id = p.id WHERE r.is_approved = 0 ORDER BY r.created_at DESC");
$pending_reviews = $pending_reviews ? $pending_reviews->fetchAll() : [];
if (!is_array($pending_reviews)) $pending_reviews = [];

// Заказы - исправленный запрос с LEFT JOIN для обработки заказов без пользователей
$orders = $pdo->query("SELECT o.*, u.full_name, u.email, u.nickname FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$orders = $orders ? $orders->fetchAll() : [];
if (!is_array($orders)) $orders = [];

// Категории
$categories = $pdo->query("SELECT * FROM categories");
$categories = $categories ? $categories->fetchAll() : [];
if (!is_array($categories)) $categories = [];
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
                <h1>🛡️ Dashboard</h1>
                <?php include 'includes/theme-toggle.php'; ?>
            </header>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-card-header">
                    <div class="stat-icon">🎮</div>
                    <span class="stat-trend up">↑ 12%</span>
                </div>
                <div class="stat-info">
                    <h3><?= safe_count($products) ?></h3>
                    <p>Товаров в каталоге</p>
                </div>
                <a href="?tab=products" class="stat-link">Управление →</a>
            </div>
            
            <div class="stat-card success">
                <div class="stat-card-header">
                    <div class="stat-icon">💰</div>
                    <span class="stat-trend up">↑ 8%</span>
                </div>
                <div class="stat-info">
                    <h3><?= safe_count($orders) ?></h3>
                    <p>Всего заказов</p>
                </div>
                <a href="?tab=orders" class="stat-link">Просмотр →</a>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-card-header">
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-info">
                    <h3><?= safe_count($users) ?></h3>
                    <p>Зарегистрировано пользователей</p>
                </div>
                <a href="?tab=users" class="stat-link">Управление →</a>
            </div>
            
            <div class="stat-card danger">
                <div class="stat-card-header">
                    <div class="stat-icon">💬</div>
                    <span class="stat-trend down"><?= safe_count($pending_reviews) ?> новых</span>
                </div>
                <div class="stat-info">
                    <h3><?= safe_count($pending_reviews) ?></h3>
                    <p>Отзывов на модерации</p>
                </div>
                <a href="?tab=reviews" class="stat-link">Проверить →</a>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="quick-actions-title">⚡ Быстрые действия</div>
            <button class="quick-action-btn" onclick="openModal('productModal')">
                <span class="icon">➕</span> Добавить товар
            </button>
            <button class="quick-action-btn" onclick="openModal('newsModal')">
                <span class="icon">📰</span> Добавить новость
            </button>
            <button class="quick-action-btn" onclick="openModal('promotionModal')">
                <span class="icon">🏷️</span> Создать акцию
            </button>
            <button class="quick-action-btn" onclick="location.href='?tab=reviews'">
                <span class="icon">✅</span> Модерация отзывов
            </button>
        </div>
        
        <nav class="admin-nav admin-nav-compact" style="margin-top: 25px;">
            <a href="?tab=products" class="<?= $tab === 'products' ? 'active' : '' ?>" title="Товары">📦</a>
            <a href="?tab=news" class="<?= $tab === 'news' ? 'active' : '' ?>" title="Новости">📰</a>
            <a href="?tab=services" class="<?= $tab === 'services' ? 'active' : '' ?>" title="Услуги">🔧</a>
            <a href="?tab=promotions" class="<?= $tab === 'promotions' ? 'active' : '' ?>" title="Акции">🎁</a>
            <a href="?tab=users" class="<?= $tab === 'users' ? 'active' : '' ?>" title="Пользователи">👥</a>
            <a href="?tab=reviews" class="<?= $tab === 'reviews' ? 'active' : '' ?>" title="Отзывы">💬</a>
            <a href="?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>" title="Заказы">🛒</a>
        </nav>
        
        <!-- Товары -->
        <section id="products" class="admin-section <?= $tab === 'products' ? 'active' : '' ?>">
            <h2>Управление товарами</h2>
            <button class="btn-add" onclick="openModal('productModal')">+ Добавить товар</button>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Платформа</th>
                        <th>Цена</th>
                        <th>Остаток</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($product['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($product['name'] ?? '') ?>" class="product-thumb"></td>
                        <td><?= htmlspecialchars($product['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($product['category_name'] ?? 'Без категории') ?></td>
                        <td><?= htmlspecialchars($product['platform'] ?? '-') ?></td>
                        <td><?= number_format($product['price'], 2) ?> ₽</td>
                        <td><?= $product['stock'] ?? 0 ?></td>
                        <td>
                            <span class="status-badge <?= $product['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $product['is_active'] ? 'Активен' : 'Неактивен' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-admin btn-edit" onclick='editProduct(<?= json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Редактировать">✏️</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить товар?')">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn-admin btn-delete" title="Удалить">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Новости -->
        <section id="news" class="admin-section <?= $tab === 'news' ? 'active' : '' ?>">
            <h2>Управление новостями</h2>
            <button class="btn-add" onclick="openModal('newsModal')">+ Добавить новость</button>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Фото</th>
                        <th>Заголовок</th>
                        <th>Рейтинг</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><img src="<?= htmlspecialchars($item['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($item['title']) ?>"></td>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= number_format($item['rating'], 1) ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($item['created_at'])) ?></td>
                        <td>
                            <span class="status-badge <?= $item['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $item['is_active'] ? 'Активна' : 'Неактивна' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-admin btn-edit" onclick='editNews(<?= json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить новость?')">
                                <input type="hidden" name="action" value="delete_news">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-admin btn-delete">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Услуги -->
        <section id="services" class="admin-section <?= $tab === 'services' ? 'active' : '' ?>">
            <h2>Управление услугами</h2>
            <button class="btn-add" onclick="openModal('serviceModal')">+ Добавить услугу</button>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Цена</th>
                        <th>Длительность (мин)</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= $service['id'] ?></td>
                        <td><img src="<?= htmlspecialchars($service['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($service['name'] ?? '') ?>"></td>
                        <td><?= htmlspecialchars($service['name'] ?? '') ?></td>
                        <td><?= number_format($service['price'], 2) ?> ₽</td>
                        <td><?= htmlspecialchars($service['duration'] ?? 0 ?? '-') ?></td>
                        <td>
                            <span class="status-badge <?= $service['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $service['is_active'] ? 'Активна' : 'Неактивна' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-admin btn-edit" onclick='editService(<?= json_encode($service, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить услугу?')">
                                <input type="hidden" name="action" value="delete_service">
                                <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                <button type="submit" class="btn-admin btn-delete">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Акции -->
        <section id="promotions" class="admin-section <?= $tab === 'promotions' ? 'active' : '' ?>">
            <h2>Управление акциями</h2>
            <button class="btn-add" onclick="openModal('promotionModal')">+ Добавить акцию</button>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Фото</th>
                        <th>Название</th>
                        <th>Скидка</th>
                        <th>Период</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promotions as $promo): ?>
                    <tr>
                        <td><?= $promo['id'] ?></td>
                        <td><img src="<?= htmlspecialchars($promo['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($promo['title'] ?? 'Без названия') ?>"></td>
                        <td><?= htmlspecialchars($promo['title'] ?? 'Без названия') ?></td>
                        <td>-<?= $promo['discount'] ?? 0 ?>%</td>
                        <td><?= date('d.m.Y', strtotime($promo['start_date'] ?? '')) ?> - <?= date('d.m.Y', strtotime($promo['end_date'] ?? '')) ?></td>
                        <td>
                            <span class="status-badge <?= $promo['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $promo['is_active'] ? 'Активна' : 'Неактивна' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-admin btn-edit" onclick='editPromotion(<?= json_encode($promo) ?>)'>✏️</button>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить акцию?')">
                                <input type="hidden" name="action" value="delete_promotion">
                                <input type="hidden" name="id" value="<?= $promo['id'] ?>">
                                <button type="submit" class="btn-admin btn-delete">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Пользователи -->
        <section id="users" class="admin-section <?= $tab === 'users' ? 'active' : '' ?>">
            <h2>Управление пользователями</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Аватар</th>
                        <th>ФИО</th>
                        <th>Email</th>
                        <th>Никнейм</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><img src="<?= htmlspecialchars($user['avatar_url'] ?? '') ?>" alt="Avatar"></td>
                        <td><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['nickname'] ?? '-') ?></td>
                        <td><?= ucfirst($user['role']) ?></td>
                        <td>
                            <span class="status-badge <?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $user['is_active'] ? 'Активен' : 'Неактивен' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-admin btn-edit" onclick='editUser(<?= json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>✏️</button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить пользователя?')">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn-admin btn-delete">🗑️</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Отзывы -->
        <section id="reviews" class="admin-section <?= $tab === 'reviews' ? 'active' : '' ?>">
            <h2>Модерация отзывов</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Товар</th>
                        <th>Текст</th>
                        <th>Рейтинг</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_reviews as $review): ?>
                    <tr>
                        <td><?= $review['id'] ?></td>
                        <td><?= htmlspecialchars($review['fullname'] ?? $review['nickname']) ?></td>
                        <td><?= htmlspecialchars($review['product_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars(mb_substr($review['comment'], 0, 50)) ?>...</td>
                        <td>⭐ <?= $review['rating'] ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                        <td>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="moderate_review">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <input type="hidden" name="approved" value="1">
                                <button type="submit" class="btn-admin btn-approve">✓</button>
                            </form>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Удалить отзыв?')">
                                <input type="hidden" name="action" value="delete_review">
                                <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                <button type="submit" class="btn-admin btn-delete">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($pending_reviews)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center">Нет отзывов на модерации</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        
        <!-- Заказы -->
        <section id="orders" class="admin-section <?= $tab === 'orders' ? 'active' : '' ?>">
            <h2>Все заказы</h2>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Email</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['fullname']) ?></td>
                        <td><?= htmlspecialchars($order['email']) ?></td>
                        <td><?= number_format($order['total_amount'], 2) ?> ₽</td>
                        <td>
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
    
    <!-- Модальное окно товара -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="productModalTitle">Добавить товар</h3>
                <button class="modal-close" onclick="closeModal('productModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="product_action" value="add_product">
                <input type="hidden" name="id" id="product_id">
                
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="name" id="product_name" required>
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" id="product_description"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Цена *</label>
                    <input type="number" step="0.01" name="price" id="product_price" required>
                </div>
                
                <div class="form-group">
                    <label>Категория</label>
                    <select name="category_id" id="product_category_id">
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Платформа</label>
                    <input type="text" name="platform" id="product_platform" placeholder="Steam, Epic Games, etc.">
                </div>
                
                <div class="form-group">
                    <label>Остаток</label>
                    <input type="number" name="stock" id="product_stock" value="100">
                </div>
                
                <div class="form-group">
                    <label>URL изображения</label>
                    <input type="url" name="image_url" id="product_image_url" placeholder="https://...">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_active" id="product_is_active" checked>
                    <label for="product_is_active">Активен</label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно новости -->
    <div id="newsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="newsModalTitle">Добавить новость</h3>
                <button class="modal-close" onclick="closeModal('newsModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="news_action" value="add_news">
                <input type="hidden" name="id" id="news_id">
                
                <div class="form-group">
                    <label>Заголовок *</label>
                    <input type="text" name="title" id="news_title" required>
                </div>
                
                <div class="form-group">
                    <label>Содержание *</label>
                    <textarea name="content" id="news_content" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Рейтинг</label>
                    <input type="number" step="0.1" name="rating" id="news_rating" value="5" min="0" max="10">
                </div>
                
                <div class="form-group">
                    <label>URL изображения</label>
                    <input type="url" name="image_url" id="news_image_url" placeholder="https://...">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_active" id="news_is_active" checked>
                    <label for="news_is_active">Активна</label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно услуги -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="serviceModalTitle">Добавить услугу</h3>
                <button class="modal-close" onclick="closeModal('serviceModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="service_action" value="add_service">
                <input type="hidden" name="id" id="service_id">
                
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="name" id="service_name" required>
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" id="service_description"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Цена *</label>
                    <input type="number" step="0.01" name="price" id="service_price" required>
                </div>
                
                <div class="form-group">
                    <label>Длительность (минут)</label>
                    <input type="number" name="duration" id="service_duration" value="60">
                </div>
                
                <div class="form-group">
                    <label>URL изображения</label>
                    <input type="url" name="image_url" id="service_image_url" placeholder="https://...">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_active" id="service_is_active" checked>
                    <label for="service_is_active">Активна</label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно акции -->
    <div id="promotionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="promotionModalTitle">Добавить акцию</h3>
                <button class="modal-close" onclick="closeModal('promotionModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="promotion_action" value="add_promotion">
                <input type="hidden" name="id" id="promotion_id">
                
                <div class="form-group">
                    <label>Название *</label>
                    <input type="text" name="title" id="promotion_title" required>
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" id="promotion_description"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Скидка (%) *</label>
                    <input type="number" name="discount" id="promotion_discount" required min="1" max="100">
                </div>
                
                <div class="form-group">
                    <label>Дата начала *</label>
                    <input type="date" name="start_date" id="promotion_start_date" required>
                </div>
                
                <div class="form-group">
                    <label>Дата окончания *</label>
                    <input type="date" name="end_date" id="promotion_end_date" required>
                </div>
                
                <div class="form-group">
                    <label>URL изображения</label>
                    <input type="url" name="image_url" id="promotion_image_url" placeholder="https://...">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_active" id="promotion_is_active" checked>
                    <label for="promotion_is_active">Активна</label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно пользователя -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="userModalTitle">Редактировать пользователя</h3>
                <button class="modal-close" onclick="closeModal('userModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="id" id="user_id">
                
                <div class="form-group">
                    <label>ФИО</label>
                    <input type="text" name="fullname" id="user_fullname">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="user_email">
                </div>
                
                <div class="form-group">
                    <label>Никнейм</label>
                    <input type="text" name="nickname" id="user_nickname">
                </div>
                
                <div class="form-group">
                    <label>Роль</label>
                    <select name="role" id="user_role">
                        <option value="user">Пользователь</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_active" id="user_is_active">
                    <label for="user_is_active">Активен</label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            // Очистка формы
            const form = document.querySelector(`#${modalId} form`);
            if (form) form.reset();
        }
        
        function editProduct(product) {
            document.getElementById('productModalTitle').textContent = 'Редактировать товар';
            document.getElementById('product_action').value = 'edit_product';
            document.getElementById('product_id').value = product.id;
            document.getElementById('product_name').value = product.name;
            document.getElementById('product_description').value = product.description || '';
            document.getElementById('product_price').value = product.price;
            document.getElementById('product_category_id').value = product.category_id;
            document.getElementById('product_platform').value = product.platform || '';
            document.getElementById('product_stock').value = product.stock;
            document.getElementById('product_image_url').value = product.image_url || '';
            document.getElementById('product_is_active').checked = product.is_active == 1;
            openModal('productModal');
        }
        
        function editNews(news) {
            document.getElementById('newsModalTitle').textContent = 'Редактировать новость';
            document.getElementById('news_action').value = 'edit_news';
            document.getElementById('news_id').value = news.id;
            document.getElementById('news_title').value = news.title;
            document.getElementById('news_content').value = news.content;
            document.getElementById('news_rating').value = news.rating;
            document.getElementById('news_image_url').value = news.image_url || '';
            document.getElementById('news_is_active').checked = news.is_active == 1;
            openModal('newsModal');
        }
        
        function editService(service) {
            document.getElementById('serviceModalTitle').textContent = 'Редактировать услугу';
            document.getElementById('service_action').value = 'edit_service';
            document.getElementById('service_id').value = service.id;
            document.getElementById('service_name').value = service.name;
            document.getElementById('service_description').value = service.description || '';
            document.getElementById('service_price').value = service.price;
            document.getElementById('service_duration').value = service.duration;
            document.getElementById('service_image_url').value = service.image_url || '';
            document.getElementById('service_is_active').checked = service.is_active == 1;
            openModal('serviceModal');
        }
        
        function editPromotion(promo) {
            document.getElementById('promotionModalTitle').textContent = 'Редактировать акцию';
            document.getElementById('promotion_action').value = 'edit_promotion';
            document.getElementById('promotion_id').value = promo.id;
            document.getElementById('promotion_title').value = promo.title;
            document.getElementById('promotion_description').value = promo.description || '';
            document.getElementById('promotion_discount').value = promo.discount;
            document.getElementById('promotion_start_date').value = promo.start_date;
            document.getElementById('promotion_end_date').value = promo.end_date;
            document.getElementById('promotion_image_url').value = promo.image_url || '';
            document.getElementById('promotion_is_active').checked = promo.is_active == 1;
            openModal('promotionModal');
        }
        
        function editUser(user) {
            document.getElementById('userModalTitle').textContent = 'Редактировать пользователя';
            document.getElementById('user_id').value = user.id;
            document.getElementById('user_fullname').value = user.fullname;
            document.getElementById('user_email').value = user.email;
            document.getElementById('user_nickname').value = user.nickname;
            document.getElementById('user_role').value = user.role;
            document.getElementById('user_is_active').checked = user.is_active == 1;
            openModal('userModal');
        }
        
        // Закрытие модального окна по клику вне его
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
    
    <!-- Closing tags for the new structure -->
    </main>
</body>
</html>
