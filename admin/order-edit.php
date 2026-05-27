<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';
$order = null;
$is_edit = false;

// Получение ID заказа
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT o.*, u.email, u.full_name, u.nickname, u.login FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: orders.php?error=not_found');
        exit;
    }
    
    // Получаем товары заказа
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$id]);
    $order_items = $stmt->fetchAll();
    if (!is_array($order_items)) $order_items = [];
    
    $is_edit = true;
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_order') {
        $status = $_POST['status'];
        $phone = trim($_POST['phone']);
        $delivery_address = trim($_POST['delivery_address']);
        $payment_method = trim($_POST['payment_method']);
        
        $stmt = $pdo->prepare("UPDATE orders SET status=?, phone=?, delivery_address=?, payment_method=? WHERE id=?");
        $stmt->execute([$status, $phone, $delivery_address, $payment_method, $id]);
        
        header('Location: order-edit.php?id=' . $id . '&success=updated');
        exit;
    }
}

// Проверка параметров URL
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'updated') $success = 'Заказ успешно обновлен';
}

$status_names = [
    'pending' => '⏳ Ожидает',
    'processing' => '⚙️ В обработке',
    'completed' => '✓ Завершен',
    'cancelled' => '✗ Отменен'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Редактировать заказ #' . $id : 'Заказ не найден' ?> - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">

<?php include 'includes/sidebar.php'; ?>

<main class="admin-main">
    <header class="admin-header">
        <h1>
            <svg class="svg-icon svg-md"><use href="../assets/icons.svg#icon-cart"></use></svg>
            <?= $is_edit ? 'Заказ #' . $id : 'Заказ не найден' ?>
        </h1>
        <?php include 'includes/theme-toggle.php'; ?>
    </header>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($is_edit): ?>
    <div class="admin-form" style="max-width: 900px;">
        <form method="POST">
            <input type="hidden" name="action" value="update_order">
            
            <div class="form-section">
                <h3>
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-user"></use></svg>
                    Информация о клиенте
                </h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Клиент:</span>
                        <span class="info-value"><?= htmlspecialchars($order['full_name'] ?? $order['nickname'] ?? $order['login'] ?? 'Клиент') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= htmlspecialchars($order['email'] ?? '-') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Дата заказа:</span>
                        <span class="info-value"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Текущий статус:</span>
                        <span class="status-badge status-badge-<?= $order['status'] == 'completed' ? 'active' : ($order['status'] == 'cancelled' ? 'inactive' : 'pending') ?>">
                            <?= $status_names[$order['status']] ?? $order['status'] ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-settings"></use></svg>
                    Детали заказа
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Статус *</label>
                        <select name="status" required>
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>⏳ Ожидает</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>⚙️ В обработке</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>✓ Завершен</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>✗ Отменен</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($order['phone'] ?? '') ?>" placeholder="+7 (___) ___-__-__">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Адрес доставки</label>
                        <input type="text" name="delivery_address" value="<?= htmlspecialchars($order['delivery_address'] ?? '') ?>" placeholder="Город, улица, дом...">
                    </div>
                    <div class="form-group">
                        <label>Способ оплаты</label>
                        <select name="payment_method">
                            <option value="">Выберите способ</option>
                            <option value="card" <?= ($order['payment_method'] ?? '') === 'card' ? 'selected' : '' ?>>Банковская карта</option>
                            <option value="cash" <?= ($order['payment_method'] ?? '') === 'cash' ? 'selected' : '' ?>>Наличные</option>
                            <option value="online" <?= ($order['payment_method'] ?? '') === 'online' ? 'selected' : '' ?>>Онлайн оплата</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-bag"></use></svg>
                    Товары в заказе
                </h3>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Цена</th>
                            <th>Количество</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        foreach ($order_items as $item): 
                            $item_total = ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                            $total += $item_total;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name'] ?? 'Товар удален') ?></td>
                            <td><?= number_format($item['price'] ?? 0, 2) ?> ₽</td>
                            <td><?= $item['quantity'] ?? 0 ?></td>
                            <td><strong><?= number_format($item_total, 2) ?> ₽</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Итого:</strong></td>
                            <td><strong style="font-size: 1.2rem; color: var(--primary);"><?= number_format($total, 2) ?> ₽</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="form-actions">
                <a href="orders.php" class="btn btn-secondary">
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-arrow-left"></use></svg>
                    Назад к списку
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="svg-icon svg-sm"><use href="../assets/icons.svg#icon-check"></use></svg>
                    Сохранить изменения
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="alert alert-error">
        Заказ не найден. <a href="orders.php" class="btn-link">Вернуться к списку заказов</a>
    </div>
    <?php endif; ?>
</main>

<style>
.form-section {
    background: var(--bg-surface);
    padding: 25px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 25px;
    border: 1px solid var(--border);
}

.form-section h3 {
    margin: 0 0 20px;
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary-light);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    font-size: 0.85rem;
    color: var(--text-muted);
    font-weight: 500;
}

.info-value {
    font-size: 1rem;
    color: var(--text-primary);
    font-weight: 600;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-top: 30px;
}

.svg-icon {
    width: 1em;
    height: 1em;
    fill: currentColor;
}

.svg-md {
    font-size: 1.5rem;
}

.svg-sm {
    font-size: 1rem;
}

.btn-link {
    color: var(--primary);
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}
</style>
</body>
</html>
