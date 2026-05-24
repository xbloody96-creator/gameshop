<?php
session_start();
require_once '../config.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Проверка параметров URL
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'added') $success = 'Товар успешно добавлен';
    if ($_GET['success'] === 'updated') $success = 'Товар успешно обновлен';
    if ($_GET['success'] === 'deleted') $success = 'Товар удален';
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'not_found') $error = 'Товар не найден';
}

// Обработка удаления
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $id = intval($_POST['id']);
    
    // Получаем информацию о товаре для удаления изображения
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    // Удаляем локальное изображение если оно есть
    if ($product && !empty($product['image_url']) && strpos($product['image_url'], '/uploads/') === 0) {
        $old_path = '..' . $product['image_url'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$id]);
    header('Location: products.php?success=deleted');
    exit;
}

// Получение списка товаров
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();
if (!is_array($products)) $products = [];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами - Админ-панель</title>
    
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">

<?php include 'includes/sidebar.php'; ?>
        
<main class="admin-main">
<header class="admin-header">
<h1>🎮 Управление товарами</h1>
        <?php include 'includes/theme-toggle.php'; ?>
</header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Панель быстрых действий -->
            <div class="quick-actions" style="margin-bottom: 25px;">
                <span class="quick-actions-title">Быстрые действия</span>
                <a href="product-edit.php" class="quick-action-btn">
                    <span class="icon">➕</span>
                    Добавить товар
                </a>
                <a href="#" class="quick-action-btn" onclick="alert('Функция импорта в разработке')">
                    <span class="icon">📥</span>
                    Импорт CSV
                </a>
                <a href="#" class="quick-action-btn" onclick="alert('Функция экспорта в разработке')">
                    <span class="icon">📤</span>
                    Экспорт данных
                </a>
            </div>

            <!-- Поиск и фильтры -->
            <div class="search-filter-bar">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" placeholder="Поиск товаров..." onkeyup="filterProducts()">
                </div>
                <select class="filter-select" id="categoryFilter" onchange="filterProducts()">
                    <option value="">Все категории</option>
                    <?php 
                    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                    $cats = $stmt->fetchAll();
                    if (is_array($cats)) {
                        foreach ($cats as $cat): 
                    ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name'] ?? 'Без названия') ?></option>
                    <?php 
                        endforeach;
                    }
                    ?>
                </select>
                <select class="filter-select" id="statusFilter" onchange="filterProducts()">
                    <option value="">Все статусы</option>
                    <option value="1">Активные</option>
                    <option value="0">Неактивные</option>
                </select>
            </div>

            <div class="admin-form" style="padding: 0; overflow: hidden;">
                <table class="admin-table admin-table-compact" id="productsTable">
                    <thead>
                        <tr>
                            <th>Фото</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Остаток</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
        <?php foreach ($products as $product): ?>
                        <tr data-category="<?= $product['category_id'] ?>" data-status="<?= $product['is_active'] ?>">
                            <td>
                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="" class="product-thumb product-thumb-small">
                <?php else: ?>
                                    <div class="no-image no-image-small">No img</div>
                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($product['name'] ?? 'Без названия') ?></strong></td>
                            <td><?= htmlspecialchars($product['category_name'] ?? 'Без категории') ?></td>
                            <td><?= number_format($product['price'] ?? 0, 2) ?> ₽</td>
                            <td><?= $product['stock'] ?? 0 ?></td>
                            <td>
                                <span class="status-badge status-badge-<?= $product['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $product['is_active'] ? '✓' : '✗' ?>
                                </span>
                            </td>
                            <td class="actions actions-compact">
                                <a href="product-edit.php?id=<?= $product['id'] ?>" class="btn-icon btn-edit" title="Редактировать">✏️</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить товар?')">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn-icon btn-delete" title="Удалить">🗑️</button>
                                </form>
                            </td>
                        </tr>
        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
</main>

<script>
function filterProducts() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('#productsTable tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const categoryId = row.dataset.category;
        const status = row.dataset.status;
        
        const matchesSearch = name.includes(searchInput);
        const matchesCategory = !categoryFilter || categoryId === categoryFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesCategory && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
