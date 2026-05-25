<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = '';
$error = '';

// Обработка действий с заказами
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            $id = intval($_POST['id']);
            $status = $_POST['status'];
            
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            // Отправка уведомления (опционально)
            $success = 'Статус заказа обновлен';
            break;
            
        case 'delete_order':
            $id = intval($_POST['id']);
            
            // Сначала удаляем позиции заказа
            $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$id]);
            
            // Затем удаляем сам заказ
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            
            $success = 'Заказ удален';
            break;
    }
}

// Получение заказов
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT o.*, u.email, u.full_name, u.nickname FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE 1";

if ($filter != 'all') {
    $sql .= " AND o.status = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query($sql);
}

$orders = $stmt->fetchAll();
if (!is_array($orders)) $orders = [];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1>🛒 Управление заказами</h1>
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
                        <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Все заказы</option>
                        <option value="pending" <?= $filter == 'pending' ? 'selected' : '' ?>>Ожидают</option>
                        <option value="processing" <?= $filter == 'processing' ? 'selected' : '' ?>>В обработке</option>
                        <option value="completed" <?= $filter == 'completed' ? 'selected' : '' ?>>Завершены</option>
                        <option value="cancelled" <?= $filter == 'cancelled' ? 'selected' : '' ?>>Отменены</option>
                    </select>
                </form>
            </div>

            <!-- Список заказов -->
            <div class="admin-form">
                <h2>📋 Заказы (<?= count($orders) ?>)</h2>
                <table class="admin-table admin-table-compact">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($order['full_name'] ?? $order['nickname'] ?? $order['email'] ?? 'Клиент') ?><br>
                                <small style="color:var(--text-muted);font-size:0.75rem;"><?= htmlspecialchars($order['email'] ?? '-') ?></small>
                            </td>
                            <td><strong><?= number_format($order['total'], 2) ?> ₽</strong></td>
                            <td>
                                <span class="status-badge status-badge-<?= $order['status'] == 'completed' ? 'active' : ($order['status'] == 'cancelled' ? 'inactive' : 'pending') ?>">
                                    <?php
                                    $status_names = [
                                        'pending' => '⏳ Ожидает',
                                        'processing' => '⚙️ В обработке',
                                        'completed' => '✓ Завершен',
                                        'cancelled' => '✗ Отменен'
                                    ];
                                    echo $status_names[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                            <td class="actions actions-compact">
                                <button class="btn-icon btn-info" style="color:var(--info);background:var(--info-bg);" onclick="viewOrder(<?= $order['id'] ?>)" title="Просмотр">👁️</button>
                                <button class="btn-icon btn-edit" onclick="changeStatus(<?= $order['id'] ?>)" title="Изменить статус">✏️</button>
                                <button class="btn-icon btn-danger" style="color:var(--danger);background:var(--danger-bg);" onclick="deleteOrder(<?= $order['id'] ?>)" title="Удалить">🗑️</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal просмотра заказа -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Информация о заказе</h3>
                <button class="modal-close" onclick="document.getElementById('viewModal').classList.remove('active')">&times;</button>
            </div>
            <div id="viewContent">Загрузка...</div>
        </div>
    </div>

    <!-- Modal изменения статуса -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Изменить статус заказа</h3>
                <button class="modal-close" onclick="document.getElementById('statusModal').classList.remove('active')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" id="status_order_id">
                <div class="form-group">
                    <label>Новый статус</label>
                    <select name="status" id="status_select" required>
                        <option value="pending">Ожидает</option>
                        <option value="processing">В обработке</option>
                        <option value="completed">Завершен</option>
                        <option value="cancelled">Отменен</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('statusModal').classList.remove('active')">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function viewOrder(orderId) {
            fetch('ajax/order_details.php?id=' + orderId)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('viewContent').innerHTML = '<p style="color:red">' + data.error + '</p>';
                    } else {
                        let itemsHtml = data.items.map(item => 
                            `<li>${item.product_name} × ${item.quantity} = ${item.price * item.quantity} ₽</li>`
                        ).join('');
                        
                        let html = `
                            <p><strong>ID:</strong> #${data.id}</p>
                            <p><strong>Клиент:</strong> ${data.login} (${data.email})</p>
                            <p><strong>Телефон:</strong> ${data.phone || '-'}</p>
                            <p><strong>Адрес доставки:</strong> ${data.delivery_address || '-'}</p>
                            <p><strong>Способ оплаты:</strong> ${data.payment_method || '-'}</p>
                            <p><strong>Статус:</strong> ${data.status}</p>
                            <p><strong>Сумма:</strong> <strong>${data.total} ₽</strong></p>
                            <p><strong>Дата:</strong> ${data.created_at}</p>
                            <hr>
                            <p><strong>Товары:</strong></p>
                            <ul>${itemsHtml}</ul>
                        `;
                        document.getElementById('viewContent').innerHTML = html;
                    }
                    document.getElementById('viewModal').classList.add('active');
                });
        }
        
        function changeStatus(orderId) {
            document.getElementById('status_order_id').value = orderId;
            document.getElementById('statusModal').classList.add('active');
        }
        
        function deleteOrder(orderId) {
            if (!confirm('Вы уверены, что хотите удалить этот заказ? Это действие нельзя отменить.')) return;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_order">
                <input type="hidden" name="id" value="${orderId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
