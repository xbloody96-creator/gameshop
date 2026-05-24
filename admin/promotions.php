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
        case 'add_promotion':
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $discount = floatval($_POST['discount']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $product_id = intval($_POST['product_id']) ?: null;
            
            if ($title && $discount > 0 && $start_date && $end_date) {
                $stmt = $pdo->prepare("INSERT INTO promotions (title, description, discount_percent, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$title, $description, $discount, $start_date, $end_date]);
                $success = 'Акция добавлена';
            } else {
                $error = 'Заполните обязательные поля';
            }
            break;
            
        case 'edit_promotion':
            $id = intval($_POST['id']);
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $discount = floatval($_POST['discount']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE promotions SET title=?, description=?, discount_percent=?, start_date=?, end_date=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $description, $discount, $start_date, $end_date, $is_active, $id]);
            $success = 'Акция обновлена';
            break;
            
        case 'delete_promotion':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM promotions WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Акция удалена';
            break;
    }
}

$stmt = $pdo->query("SELECT * FROM promotions ORDER BY created_at DESC");
$promotions = $stmt->fetchAll();
if (!is_array($promotions)) $promotions = [];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление акциями - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>🏷️ Управление акциями</h1>
                <?php include 'includes/theme-toggle.php'; ?>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="admin-form">
                <h2>➕ Добавить акцию</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_promotion">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Название *</label>
                            <input type="text" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Скидка (%) *</label>
                            <input type="number" name="discount" step="0.1" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Дата начала *</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>Дата окончания *</label>
                            <input type="date" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить акцию</button>
                </form>
            </div>

            <div class="admin-form">
                <h2>📋 Список акций</h2>
                <table class="admin-table admin-table-compact">
                    <thead>
                        <tr>
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
                            <td><?= htmlspecialchars($promo['title']) ?></td>
                            <td><strong>-<?= $promo['discount'] ?>%</strong></td>
                            <td>
                                <?= date('d.m.Y', strtotime($promo['start_date'])) ?> — 
                                <?= date('d.m.Y', strtotime($promo['end_date'])) ?>
                            </td>
                            <td>
                                <span class="status-badge status-badge-<?= $promo['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $promo['is_active'] ? '✓' : '✗' ?>
                                </span>
                            </td>
                            <td class="actions actions-compact">
                                <button class="btn-icon btn-edit" onclick="editPromotion(<?= htmlspecialchars(json_encode($promo)) ?>)">✏️</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить акцию?')">
                                    <input type="hidden" name="action" value="delete_promotion">
                                    <input type="hidden" name="id" value="<?= $promo['id'] ?>">
                                    <button type="submit" class="btn-icon btn-delete">🗑️</button>
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
                <h3>Редактировать акцию</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_promotion">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group">
                        <label>Название *</label>
                        <input type="text" name="title" id="edit_title" required>
                    </div>
                    <div class="form-group">
                        <label>Скидка (%) *</label>
                        <input type="number" name="discount" id="edit_discount" step="0.1" min="0" max="100" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Дата начала *</label>
                        <input type="date" name="start_date" id="edit_start_date" required>
                    </div>
                    <div class="form-group">
                        <label>Дата окончания *</label>
                        <input type="date" name="end_date" id="edit_end_date" required>
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
        function editPromotion(promo) {
            document.getElementById('edit_id').value = promo.id;
            document.getElementById('edit_title').value = promo.title;
            document.getElementById('edit_discount').value = promo.discount;
            document.getElementById('edit_start_date').value = promo.start_date;
            document.getElementById('edit_end_date').value = promo.end_date;
            document.getElementById('edit_description').value = promo.description || '';
            document.getElementById('edit_is_active').checked = promo.is_active == 1;
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
