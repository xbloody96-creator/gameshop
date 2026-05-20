<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Обработка действий с отзывами
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'approve_review':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("UPDATE reviews SET approved = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Отзыв одобрен';
            break;
            
        case 'reject_review':
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Отзыв удален';
            break;
    }
}

// Получение всех отзывов
$filter = $_GET['filter'] ?? 'all'; // all, pending, approved

$sql = "SELECT r.*, u.login, u.email, p.name as product_name FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        LEFT JOIN products p ON r.product_id = p.id 
        WHERE 1";

if ($filter == 'pending') {
    $sql .= " AND r.approved = 0";
} elseif ($filter == 'approved') {
    $sql .= " AND r.approved = 1";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->query($sql);
$reviews = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Модерация отзывов - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>💬 Модерация отзывов</h1>
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

            <!-- Фильтры -->
            <div class="admin-form">
                <form method="GET" style="display: flex; gap: 15px;">
                    <select name="filter" onchange="this.form.submit()">
                        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Все отзывы</option>
                        <option value="pending" <?= $filter == 'pending' ? 'selected' : '' ?>>На модерации</option>
                        <option value="approved" <?= $filter == 'approved' ? 'selected' : '' ?>>Одобренные</option>
                    </select>
                </form>
            </div>

            <!-- Список отзывов -->
            <div class="admin-form">
                <h2>📋 Отзывы (<?= count($reviews) ?>)</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Товар</th>
                            <th>Оценка</th>
                            <th>Отзыв</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>#<?= $review['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($review['login']) ?><br>
                                <small style="color:#6b7280"><?= htmlspecialchars($review['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($review['product_name'] ?? 'Удален') ?></td>
                            <td>⭐ <?= $review['rating'] ?>/5</td>
                            <td><?= htmlspecialchars(mb_substr($review['comment'], 0, 100)) ?><?= mb_strlen($review['comment']) > 100 ? '...' : '' ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                            <td>
                                <span class="status status-<?= $review['approved'] ? 'completed' : 'pending' ?>">
                                    <?= $review['approved'] ? 'Одобрен' : 'На модерации' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <?php if (!$review['approved']): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="approve_review">
                                        <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">✅</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить отзыв?')">
                                    <input type="hidden" name="action" value="reject_review">
                                    <input type="hidden" name="id" value="<?= $review['id'] ?>">
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
</body>
</html>
