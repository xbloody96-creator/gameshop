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
    }
}

// Получение списка товаров
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

// Получение категорий
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

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

            <div class="admin-form">
                <h2>➕ Добавить товар</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_product">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Название *</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Цена (₽) *</label>
                            <input type="number" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Категория *</label>
                            <select name="category_id" required>
                                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Платформа</label>
                            <select name="platform">
                                <option value="">Любая</option>
                                <option value="PC">PC</option>
                                <option value="PlayStation">PlayStation</option>
                                <option value="Xbox">Xbox</option>
                                <option value="Nintendo">Nintendo</option>
                                <option value="Mobile">Mobile</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Остаток на складе</label>
                            <input type="number" name="stock" value="100" min="0">
                        </div>
                        <div class="form-group">
                            <label>URL изображения</label>
                            <input type="url" name="image_url" placeholder="https://...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить товар</button>
                </form>
            </div>

            <div class="admin-form">
                <h2>📦 Список товаров</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                            <td>#<?= $product['id'] ?></td>
                            <td>
                <?php if ($product['image_url']): ?>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="" class="product-thumb">
                <?php else: ?>
                                    <div class="product-thumb" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;">No img</div>
                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category_name'] ?? 'Без категории') ?></td>
                            <td><?= htmlspecialchars($product['platform']) ?></td>
                            <td><?= number_format($product['price'], 2) ?> ₽</td>
                            <td><?= $product['stock'] ?></td>
                            <td>
                                <span class="status status-<?= $product['is_active'] ? 'completed' : 'cancelled' ?>">
                                    <?= $product['is_active'] ? 'Активен' : 'Неактивен' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn btn-sm btn-warning" onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)">✏️</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить товар?')">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                                </form>
                            </td>
                        </tr>
        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
</main>
    </div>

    <!-- Modal для редактирования -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Редактировать товар</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Цена (₽) *</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Категория *</label>
                        <select name="category_id" id="edit_category_id" required>
            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Платформа</label>
                        <select name="platform" id="edit_platform">
                            <option value="">Любая</option>
                            <option value="PC">PC</option>
                            <option value="PlayStation">PlayStation</option>
                            <option value="Xbox">Xbox</option>
                            <option value="Nintendo">Nintendo</option>
                            <option value="Mobile">Mobile</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Остаток на складе</label>
                        <input type="number" name="stock" id="edit_stock" min="0">
                    </div>
                    <div class="form-group">
                        <label>URL изображения</label>
                        <input type="url" name="image_url" id="edit_image_url">
                    </div>
                </div>
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" id="edit_description" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" id="edit_is_active"> Активен
                    </label>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-danger" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editProduct(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_platform').value = product.platform;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_image_url').value = product.image_url || '';
            document.getElementById('edit_description').value = product.description || '';
            document.getElementById('edit_is_active').checked = product.is_active == 1;
            
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        // Закрытие по клику вне модального окна
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
