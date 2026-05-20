<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
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
    }
}

$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll();
if (!is_array($services)) $services = [];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление услугами - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>🛠️ Управление услугами</h1>
                <div class="admin-user-info">
                    <span><?= htmlspecialchars($_SESSION['login']) ?></span>
                    <a href="../logout.php" class="btn btn-danger">Выход</a>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="admin-form">
                <h2>➕ Добавить услугу</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_service">
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
                            <label>Длительность (мин)</label>
                            <input type="number" name="duration" value="60" min="0">
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
                    <button type="submit" class="btn btn-primary">Добавить услугу</button>
                </form>
            </div>

            <div class="admin-form">
                <h2>📋 Список услуг</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Цена</th>
                            <th>Длительность</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                        <tr>
                            <td>#<?= $service['id'] ?></td>
                            <td><?= htmlspecialchars($service['name'] ?? 'Без названия') ?></td>
                            <td><?= number_format($service['price'], 2) ?> ₽</td>
                            <td><?= htmlspecialchars($service['duration'] ?? 0) ?> мин</td>
                            <td>
                                <span class="status status-<?= $service['is_active'] ? 'completed' : 'cancelled' ?>">
                                    <?= $service['is_active'] ? 'Активна' : 'Неактивна' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn btn-sm btn-warning" onclick="editService(<?= htmlspecialchars(json_encode($service)) ?>)">✏️</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить услугу?')">
                                    <input type="hidden" name="action" value="delete_service">
                                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
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

    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Редактировать услугу</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_service">
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
                        <label>Длительность (мин)</label>
                        <input type="number" name="duration" id="edit_duration" min="0">
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
                        <input type="checkbox" name="is_active" id="edit_is_active"> Активна
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
        function editService(service) {
            document.getElementById('edit_id').value = service.id;
            document.getElementById('edit_name').value = service.name;
            document.getElementById('edit_price').value = service.price;
            document.getElementById('edit_duration').value = service.duration;
            document.getElementById('edit_image_url').value = service.image_url || '';
            document.getElementById('edit_description').value = service.description || '';
            document.getElementById('edit_is_active').checked = service.is_active == 1;
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
