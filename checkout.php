<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$error = '';
$success = '';

// Получение содержимого корзины
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.title, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartItems = $stmt->fetchAll();
    
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    if (empty($cartItems)) {
        header('Location: cart.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Ошибка получения корзины';
    $cartItems = [];
    $total = 0;
}

// Обработка оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $deliveryMethod = $_POST['delivery_method'] ?? 'email';
    $paymentMethod = $_POST['payment_method'] ?? 'card';
    $notes = trim($_POST['notes'] ?? '');
    
    try {
        // Генерация номера заказа
        $orderNumber = 'ORD-' . strtoupper(uniqid());
        
        // Создание заказа
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, total_amount, status, payment_method, delivery_method, 
                                customer_name, customer_email, customer_phone, notes) 
            VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $orderNumber,
            $total,
            $paymentMethod,
            $deliveryMethod,
            $user['full_name'],
            $user['email'],
            '', // телефон можно добавить в профиль
            $notes
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Добавление товаров в заказ
        foreach ($cartItems as $item) {
            // Генерация ключа игры (для примера)
            $gameKey = 'GK-' . strtoupper(bin2hex(random_bytes(8))) . '-' . strtoupper(bin2hex(random_bytes(8)));
            
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_title, price, quantity, game_key) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['title'],
                $item['price'],
                $item['quantity'],
                $gameKey
            ]);
        }
        
        // Очистка корзины
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Отправка email подтверждения
        $subject = 'Подтверждение заказа #' . $orderNumber;
        $message = "
            <h2>Спасибо за заказ!</h2>
            <p>Ваш заказ #{$orderNumber} успешно оформлен.</p>
            <h3>Детали заказа:</h3>
            <ul>
        ";
        
        foreach ($cartItems as $item) {
            $message .= "<li>{$item['title']} - {$item['quantity']} шт. x " . formatPrice($item['price']) . "</li>";
        }
        
        $message .= "
            </ul>
            <p><strong>Общая сумма: " . formatPrice($total) . "</strong></p>
            <p>Статус заказа: Ожидает оплаты</p>
            <p>С уважением, команда " . SITE_NAME . "</p>
        ";
        
        sendEmail($user['email'], $subject, $message);
        
        $success = 'Заказ успешно оформлен! Подтверждение отправлено на ваш email.';
        
        // Редирект на страницу успеха
        header('Location: checkout.php?success=1');
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка оформления заказа';
    }
}

if (isset($_GET['success'])) {
    $success = 'Заказ успешно оформлен! Подтверждение отправлено на ваш email.';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="checkout-page">
        <div class="container">
            <h1>Оформление заказа</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= escape($success) ?></div>
                <a href="profile.php" class="btn btn-primary">Перейти в личный кабинет</a>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= escape($error) ?></div>
                <?php endif; ?>
                
                <div class="checkout-layout">
                    <div class="checkout-form-wrapper">
                        <form method="POST" action="" class="checkout-form">
                            <section class="checkout-section">
                                <h2>Данные покупателя</h2>
                                <div class="form-group">
                                    <label>ФИО</label>
                                    <input type="text" value="<?= escape($user['full_name']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" value="<?= escape($user['email']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Никнейм</label>
                                    <input type="text" value="<?= escape($user['nickname']) ?>" readonly>
                                </div>
                            </section>
                            
                            <section class="checkout-section">
                                <h2>Способ получения</h2>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="delivery_method" value="email" checked>
                                        <span>Email (цифровой ключ)</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="delivery_method" value="account">
                                        <span>Личный кабинет</span>
                                    </label>
                                </div>
                            </section>
                            
                            <section class="checkout-section">
                                <h2>Способ оплаты</h2>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="card" checked>
                                        <span>Банковская карта</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="qiwi">
                                        <span>Qiwi</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="yandex">
                                        <span>Яндекс.Деньги</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="payment_method" value="crypto">
                                        <span>Криптовалюта</span>
                                    </label>
                                </div>
                            </section>
                            
                            <section class="checkout-section">
                                <h2>Комментарий к заказу</h2>
                                <textarea name="notes" rows="3" placeholder="Дополнительная информация..."></textarea>
                            </section>
                            
                            <button type="submit" class="btn btn-primary btn-block btn-lg">Подтвердить заказ</button>
                        </form>
                    </div>
                    
                    <div class="checkout-summary">
                        <h2>Ваш заказ</h2>
                        <div class="order-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item-summary">
                                    <img src="images/uploads/<?= escape($item['image']) ?>" alt="<?= escape($item['title']) ?>" onerror="this.src='https://via.placeholder.com/60x60?text=<?= urlencode($item['title']) ?>'">
                                    <div class="order-item-info">
                                        <h4><?= escape($item['title']) ?></h4>
                                        <p><?= $item['quantity'] ?> шт. x <?= formatPrice($item['price']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="order-total">
                            <span>Итого:</span>
                            <span class="total-price"><?= formatPrice($total) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="main.js"></script>
</body>
</html>
