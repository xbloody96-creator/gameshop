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
        case 'add_news':
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $image_url = trim($_POST['image_url']);
            $rating = floatval($_POST['rating']);
            
            if ($title && $content) {
                $stmt = $pdo->prepare("INSERT INTO news (title, content, image_url, rating, published_at, is_active) VALUES (?, ?, ?, ?, NOW(), 1)");
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
    }
}

$stmt = $pdo->query("SELECT * FROM news ORDER BY published_at DESC");
$news_items = $stmt->fetchAll();
if (!is_array($news_items)) $news_items = [];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление новостями - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>📰 Управление новостями</h1>
                <?php include 'includes/theme-toggle.php'; ?>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="admin-form">
                <h2>➕ Добавить новость</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_news">
                    <div class="form-group">
                        <label>Заголовок *</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>URL изображения</label>
                            <input type="url" name="image_url" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>Рейтинг (0-5)</label>
                            <input type="number" name="rating" step="0.1" min="0" max="5" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Содержание *</label>
                        <textarea name="content" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить новость</button>
                </form>
            </div>

            <div class="admin-form">
                <h2>📋 Список новостей</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заголовок</th>
                            <th>Рейтинг</th>
                            <th>Дата публикации</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news_items as $item): ?>
                        <tr>
                            <td>#<?= $item['id'] ?? '?' ?></td>
                            <td><?= htmlspecialchars($item['title'] ?? 'Без названия') ?></td>
                            <td>⭐ <?= number_format($item['rating'] ?? 0, 1) ?></td>
                            <td><?= isset($item['published_at']) ? date('d.m.Y H:i', strtotime($item['published_at'])) : '-' ?></td>
                            <td>
                                <span class="status status-<?= !empty($item['is_active']) ? 'completed' : 'cancelled' ?>">
                                    <?= !empty($item['is_active']) ? 'Активна' : 'Неактивна' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn btn-sm btn-warning" onclick='editNews({
                                    id: <?= $item['id'] ?? 0 ?>,
                                    title: "<?= addslashes($item['title'] ?? '') ?>",
                                    content: "<?= addslashes($item['content'] ?? '') ?>",
                                    image_url: "<?= addslashes($item['image_url'] ?? '') ?>",
                                    rating: <?= $item['rating'] ?? 0 ?>,
                                    is_active: <?= $item['is_active'] ?? 0 ?>
                                })'>✏️</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить новость?')">
                                    <input type="hidden" name="action" value="delete_news">
                                    <input type="hidden" name="id" value="<?= $item['id'] ?? '' ?>">
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
                <h3>Редактировать новость</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_news">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Заголовок *</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>URL изображения</label>
                        <input type="url" name="image_url" id="edit_image_url">
                    </div>
                    <div class="form-group">
                        <label>Рейтинг (0-5)</label>
                        <input type="number" name="rating" id="edit_rating" step="0.1" min="0" max="5">
                    </div>
                </div>
                <div class="form-group">
                    <label>Содержание *</label>
                    <textarea name="content" id="edit_content" rows="6" required></textarea>
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
        function editNews(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('edit_title').value = item.title || '';
            document.getElementById('edit_image_url').value = item.image_url || '';
            document.getElementById('edit_rating').value = item.rating || 0;
            document.getElementById('edit_content').value = item.content || '';
            document.getElementById('edit_is_active').checked = item.is_active == 1;
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
