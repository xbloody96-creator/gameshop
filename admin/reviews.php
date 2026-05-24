<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
            try {
                $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Отзыв одобрен';
            } catch (PDOException $e) {
                $error = 'Ошибка при одобрении отзыва: ' . $e->getMessage();
            }
            header('Location: reviews.php?success=' . urlencode($success) . '&error=' . urlencode($error));
            exit;
            
        case 'reject_review':
            $id = intval($_POST['id']);
            try {
                $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                $stmt->execute([$id]);
                $success = 'Отзыв удален';
            } catch (PDOException $e) {
                $error = 'Ошибка при удалении отзыва: ' . $e->getMessage();
            }
            header('Location: reviews.php?success=' . urlencode($success) . '&error=' . urlencode($error));
            exit;
    }
}

// Получение сообщений об успехе/ошибке из GET параметров
if (isset($_GET['success']) && $_GET['success']) {
    $success = $_GET['success'];
}
if (isset($_GET['error']) && $_GET['error']) {
    $error = $_GET['error'];
}

// Получение всех отзывов
$filter = $_GET['filter'] ?? 'all'; // all, pending, approved

$sql = "SELECT r.*, u.email, u.full_name, u.nickname, COALESCE(p.name, 'Товар удален') as product_name FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        LEFT JOIN products p ON r.product_id = p.id 
        WHERE 1";

if ($filter == 'pending') {
    $sql .= " AND r.is_approved = 0";
} elseif ($filter == 'approved') {
    $sql .= " AND r.is_approved = 1";
}

$sql .= " ORDER BY r.created_at DESC";

$stmt = $pdo->query($sql);
$reviews = $stmt->fetchAll();
if (!is_array($reviews)) $reviews = [];

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
                <?php include 'includes/theme-toggle.php'; ?>
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
                <table class="admin-table admin-table-compact">
                    <thead>
                        <tr>
                            <th>Пользователь</th>
                            <th>Товар</th>
                            <th>Оценка</th>
                            <th>Отзыв</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($review['full_name'] ?? $review['nickname'] ?? $review['email'] ?? 'Клиент') ?><br>
                                <small style="color:var(--text-muted);font-size:0.75rem;"><?= htmlspecialchars($review['email'] ?? '-') ?></small>
                            </td>
                            <td><?= htmlspecialchars($review['product_name'] ?? 'Удален') ?></td>
                            <td>⭐ <?= $review['rating'] ?>/5</td>
                            <td><?= htmlspecialchars(mb_substr($review['comment'], 0, 80)) ?><?= mb_strlen($review['comment']) > 80 ? '...' : '' ?></td>
                            <td>
                                <?php $isApproved = isset($review['is_approved']) && $review['is_approved'] == 1; ?>
                                <span class="status-badge status-badge-<?= $isApproved ? 'active' : 'inactive' ?>">
                                    <?= $isApproved ? '✓' : '⏳' ?>
                                </span>
                            </td>
                            <td class="actions actions-compact">
                                <?php if (!$isApproved): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="approve_review">
                                        <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                        <button type="submit" class="btn-icon btn-edit" title="Одобрить">✅</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить отзыв?')">
                                    <input type="hidden" name="action" value="reject_review">
                                    <input type="hidden" name="id" value="<?= $review['id'] ?>">
                                    <button type="submit" class="btn-icon btn-delete" title="Удалить">🗑️</button>
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
