<?php
session_start();
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
    }
}

// Получение заказов
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT o.*, u.login, u.email FROM orders o 
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
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td>
                                <?= htmlspecialchars($order['login']) ?><br>
                                <small style="color:#6b7280"><?= htmlspecialchars($order['email']) ?></small>
                            </td>
                            <td><strong><?= number_format($order['total'], 2) ?> ₽</strong></td>
                            <td>
                                <span class="status status-<?= $order['status'] ?>">
                                    <?php
                                    $status_names = [
                                        'pending' => 'Ожидает',
                                        'processing' => 'В обработке',
                                        'completed' => 'Завершен',
                                        'cancelled' => 'Отменен'
                                    ];
                                    echo $status_names[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                            <td class="actions">
                                <button class="btn btn-sm btn-warning" onclick="viewOrder(<?= $order['id'] ?>)">👁️</button>
                                <button class="btn btn-sm btn-primary" onclick="changeStatus(<?= $order['id'] ?>)">✏️</button>
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
    </script>
</body>
</html>
